<?php

namespace App\Soho\Http\Controllers\Customer;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * 客户标签管理（soho_customer_tags 的增删改查）
 *
 * 口径：
 * - 标签按租户隔离；
 * - type=manual 手工标签，type=auto 规则标签（condition_config 存 JSON 规则）；
 * - 标签名在同租户内唯一。
 */
class CustomerTagController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $tags = DB::table('soho_customer_tags')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->input('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => DB::table('soho_customer_tags')->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->count(),
            'manual' => DB::table('soho_customer_tags')->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('type', 'manual')->count(),
            'auto' => DB::table('soho_customer_tags')->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('type', 'auto')->count(),
        ];

        return view('soho.customer.tags', compact('tags', 'stats'));
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $data = $this->validateTag($request, $tenantId);

        DB::table('soho_customer_tags')->insert([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'color' => $data['color'] ?? '#6c757d',
            'type' => $data['type'],
            'condition_config' => $data['condition_config'] ?? null,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('soho.customer.tags')->with('success', '标签「'.$data['name'].'」已创建。');
    }

    public function update(Request $request, int $tag)
    {
        $tenantId = Auth::user()->tenant_id;

        $row = DB::table('soho_customer_tags')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('id', $tag)
            ->first();

        if (! $row) {
            return back()->with('error', '标签不存在。');
        }

        $data = $this->validateTag($request, $tenantId, $row->id);

        DB::table('soho_customer_tags')->where('id', $row->id)->update([
            'name' => $data['name'],
            'color' => $data['color'] ?? $row->color,
            'type' => $data['type'],
            'condition_config' => $data['condition_config'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('soho.customer.tags')->with('success', '标签已更新。');
    }

    public function destroy(int $tag)
    {
        $tenantId = Auth::user()->tenant_id;

        $row = DB::table('soho_customer_tags')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('id', $tag)
            ->first();

        if (! $row) {
            return back()->with('error', '标签不存在。');
        }

        DB::table('soho_customer_tags')->where('id', $row->id)->delete();

        return redirect()->route('soho.customer.tags')->with('success', '标签「'.$row->name.'」已删除。');
    }

    private function validateTag(Request $request, ?int $tenantId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:40',
                Rule::unique('soho_customer_tags', 'name')
                    ->where(fn ($q) => $q->where('tenant_id', $tenantId))
                    ->ignore($ignoreId),
            ],
            'color' => ['nullable', 'string', 'max:20'],
            'type' => ['required', 'in:manual,auto'],
            'condition_config' => ['nullable', 'string', 'max:500'],
        ], [], [
            'name' => '标签名称',
            'type' => '标签类型',
        ]);
    }
}
