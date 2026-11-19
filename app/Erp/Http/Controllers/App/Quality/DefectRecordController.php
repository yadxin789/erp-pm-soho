<?php

namespace App\Erp\Http\Controllers\App\Quality;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\MasterData\Item;
use App\Erp\Models\Quality\DefectCode;
use App\Erp\Models\Quality\DefectRecord;
use App\Erp\Models\Production\ProductionOrder;
use App\Erp\Models\System\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * 不合格品（缺陷记录）台账与处置闭环
 *
 * 缺陷记录来源：
 * 1) 检验单判定不合格时自动生成（defect_type 取检验类型）
 * 2) 质检人员手工登记（来料/过程/成品/客诉）
 *
 * 处置状态机：pending（待处置） → rework（返工）/ reject（退货）/ concession（让步接收）/ scrap（报废）
 * 处置完成后写入 root_cause / corrective_action，形成纠正措施留痕。
 */
class DefectRecordController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view quality inspections');

        $query = DefectRecord::with(['defectCode', 'item', 'productionOrder']);

        if ($request->filled('defect_type')) {
            $query->where('defect_type', $request->input('defect_type'));
        }
        if ($request->filled('disposition')) {
            $query->where('disposition', $request->input('disposition'));
        }
        if ($request->filled('keyword')) {
            $kw = $request->input('keyword');
            $query->where(function ($q) use ($kw) {
                $q->where('record_no', 'like', "%{$kw}%")
                    ->orWhere('batch_number', 'like', "%{$kw}%")
                    ->orWhere('description', 'like', "%{$kw}%");
            });
        }

        $records = $query->latest()->paginate(20)->withQueryString();
        $codes = DefectCode::orderBy('category')->get();

        return view('app.quality.defects.index', compact('records', 'codes'));
    }

    public function create()
    {
        Gate::authorize('create quality inspections');

        $items = Item::orderBy('name')->get();
        $codes = DefectCode::where('active', true)->orderBy('category')->get();
        $orders = ProductionOrder::latest()->take(50)->get();
        $types = ['iqc' => 'IQC来料', 'ipqc' => 'IPQC过程', 'fqc' => 'FQC成品', 'oqc' => 'OQC出货', 'customer' => '客诉'];

        return view('app.quality.defects.create', compact('items', 'codes', 'orders', 'types'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create quality inspections');

        $data = $request->validate([
            'defect_type' => 'required|in:iqc,ipqc,fqc,oqc,customer',
            'defect_code_id' => 'nullable|exists:erp_defect_codes,id',
            'production_order_id' => 'nullable|exists:erp_production_orders,id',
            'item_id' => 'nullable|exists:erp_items,id',
            'batch_number' => 'nullable|string|max:50',
            'quantity' => 'required|numeric|min:0.0001',
            'description' => 'required|string|max:2000',
            'root_cause' => 'nullable|string|max:2000',
            'corrective_action' => 'nullable|string|max:2000',
            'disposition' => 'required|in:pending,rework,reject,concession,scrap',
        ]);

        $record = DefectRecord::create([
            'record_no' => $this->nextRecordNo(),
            'defect_type' => $data['defect_type'],
            'defect_code_id' => $data['defect_code_id'] ?? null,
            'production_order_id' => $data['production_order_id'] ?? null,
            'item_id' => $data['item_id'] ?? null,
            'batch_number' => $data['batch_number'] ?? null,
            'quantity' => $data['quantity'],
            'disposition' => $data['disposition'],
            'description' => $data['description'],
            'root_cause' => $data['root_cause'] ?? null,
            'corrective_action' => $data['corrective_action'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $this->auditLog('create', 'DefectRecord', $record, '登记缺陷记录 '.$record->record_no);

        return redirect()->route('app.quality.defects.show', $record)->with('success', '缺陷记录已登记：'.$record->record_no);
    }

    public function show(DefectRecord $defect)
    {
        Gate::authorize('view quality inspections');
        $defect->load(['defectCode', 'item', 'productionOrder']);

        return view('app.quality.defects.show', compact('defect'));
    }

    /** 处置：更新处置结论、原因分析与纠正措施 */
    public function handle(Request $request, DefectRecord $defect)
    {
        Gate::authorize('complete quality inspections');

        $data = $request->validate([
            'disposition' => 'required|in:pending,rework,reject,concession,scrap',
            'root_cause' => 'nullable|string|max:2000',
            'corrective_action' => 'nullable|string|max:2000',
        ], [], ['disposition' => '处置方式']);

        if ($data['disposition'] !== 'pending' && empty($data['corrective_action'])) {
            return back()->with('warning', '处置为返工/退货/让步接收/报废时，必须填写纠正措施。')->withInput();
        }

        $defect->update([
            'disposition' => $data['disposition'],
            'root_cause' => $data['root_cause'] ?? $defect->root_cause,
            'corrective_action' => $data['corrective_action'] ?? $defect->corrective_action,
        ]);

        $this->auditLog('handle', 'DefectRecord', $defect, '处置缺陷记录 '.$defect->record_no.' → '.$data['disposition']);

        return redirect()->route('app.quality.defects.show', $defect)->with('success', '处置已保存。');
    }

    /** 生成唯一记录号：DR-YYYY-序号（按当前最大 id 顺延） */
    public static function nextRecordNo(): string
    {
        $max = (int) (DefectRecord::max('id') ?? 0);
        do {
            $max++;
            $no = 'DR-'.date('Y').'-'.str_pad((string) $max, 6, '0', STR_PAD_LEFT);
        } while (DefectRecord::where('record_no', $no)->exists());

        return $no;
    }

    protected function auditLog(string $action, string $module, $record, string $remark = ''): void
    {
        AuditLog::create([
            'action' => $action,
            'module' => $module,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'record_id' => $record->id,
            'record_type' => get_class($record),
            'remark' => $remark ?: "{$module} #{$record->id} {$action}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
