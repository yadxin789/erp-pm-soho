<?php

namespace App\Soho\Http\Controllers\Customer;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 客户隐私（数据主体权利请求）
 *
 * 数据源：
 * - soho_customer_privacy_requests：隐私请求全流程（新建表，本模块私有）
 * - soho_customers：客户基础信息（提交请求时回填姓名/联系方式）
 * - soho_sensitive_access_logs：敏感数据访问日志（同页展示，便于溯源）
 */
class CustomerPrivacyController extends Controller
{
    /** 隐私请求清单 */
    public function index(Request $request)
    {
        $base = DB::table('soho_customer_privacy_requests')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t));

        $rows = (clone $base)
            ->when($request->input('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($request->input('request_type'), fn ($q, $ty) => $q->where('request_type', $ty))
            ->when($request->input('search'), fn ($q, $kw) => $q->where(function ($qq) use ($kw) {
                $qq->where('request_no', 'like', "%{$kw}%")
                    ->orWhere('customer_name', 'like', "%{$kw}%")
                    ->orWhere('customer_contact', 'like', "%{$kw}%");
            }))
            ->orderByRaw("FIELD(status,'pending','processing','completed','rejected')")
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'processing' => (clone $base)->where('status', 'processing')->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'overdue' => (clone $base)->whereIn('status', ['pending', 'processing'])
                ->whereNotNull('deadline_at')->where('deadline_at', '<', now())->count(),
        ];

        // 敏感数据访问日志（近 20 条，用于溯源）
        $accessLogs = DB::table('soho_sensitive_access_logs')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->input('data_type'), fn ($q, $ty) => $q->where('data_type', $ty))
            ->orderByDesc('accessed_at')
            ->limit(20)
            ->get(['id', 'user_id', 'data_type', 'action', 'record_identifier', 'ip_address', 'accessed_at', 'remark']);

        $customers = DB::table('soho_customers')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('status', 1)
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'name', 'email', 'phone']);

        $dataTypes = DB::table('soho_sensitive_access_logs')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->select('data_type')->distinct()->pluck('data_type');

        return view('soho.customer.privacy', compact('rows', 'stats', 'accessLogs', 'customers', 'dataTypes'));
    }

    /** 提交隐私请求 */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'request_type' => ['required', 'in:access,export,delete,withdraw'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [], ['customer_id' => '客户', 'request_type' => '请求类型']);

        $customer = DB::table('soho_customers')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('id', $data['customer_id'])
            ->first(['id', 'name', 'email', 'phone']);
        if (! $customer) {
            return back()->with('warning', '客户不存在或不属于当前租户。');
        }

        DB::table('soho_customer_privacy_requests')->insert([
            'tenant_id' => $this->tenantId(),
            'request_no' => 'PR'.now()->format('YmdHis').rand(100, 999),
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_contact' => $customer->email ?: $customer->phone,
            'request_type' => $data['request_type'],
            'status' => 'pending',
            // 默认 15 个自然日内处理
            'deadline_at' => now()->addDays(15),
            'reason' => $data['reason'] ?? null,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('soho.customer.privacy')
            ->with('success', "已受理客户「{$customer->name}」的隐私请求，请于 15 日内处理。");
    }

    /** 处理隐私请求（受理 / 完成 / 驳回） */
    public function handle(Request $request, int $id)
    {
        $data = $request->validate([
            'action' => ['required', 'in:process,complete,reject'],
            'handle_remark' => ['nullable', 'string', 'max:1000'],
        ], [], ['action' => '处理动作']);

        $row = DB::table('soho_customer_privacy_requests')
            ->when($this->tenantId(), fn ($q, $t) => $q->where('tenant_id', $t))
            ->where('id', $id)
            ->first();
        if (! $row) {
            return back()->with('warning', '隐私请求不存在。');
        }
        if (in_array($row->status, ['completed', 'rejected'], true)) {
            return back()->with('warning', '该请求已处理完毕，不能重复操作。');
        }

        $map = ['process' => 'processing', 'complete' => 'completed', 'reject' => 'rejected'];
        $updates = [
            'status' => $map[$data['action']],
            'handle_remark' => $data['handle_remark'] ?? $row->handle_remark,
            'updated_at' => now(),
        ];
        if (in_array($data['action'], ['complete', 'reject'], true)) {
            $updates['handled_at'] = now();
            $updates['handled_by'] = Auth::id();
            $updates['handled_by_name'] = Auth::user()->name ?? null;
        }

        DB::table('soho_customer_privacy_requests')->where('id', $id)->update($updates);

        $label = ['process' => '已受理', 'complete' => '已完成', 'reject' => '已驳回'][$data['action']];

        return redirect()->route('soho.customer.privacy')
            ->with('success', "请求 {$row->request_no} {$label}。");
    }

    private function tenantId(): ?int
    {
        return Auth::user()->tenant_id;
    }
}
