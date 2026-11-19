<?php

namespace App\Erp\Http\Controllers\App\Quality;

use App\Erp\Enums\QualityInspectionStatus;
use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Quality\QualityInspection;
use App\Erp\Models\Quality\QualityInspectionLine;
use App\Erp\Models\Quality\DefectRecord;
use App\Erp\Models\MasterData\Item;
use App\Erp\Models\MasterData\Warehouse;
use App\Erp\Models\Quality\DefectCode;
use App\Erp\Services\NotificationService;
use App\Erp\Events\QualityCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QualityController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        Gate::authorize('view quality inspections');
        $inspections = QualityInspection::with('item')->paginate(20);
        return view('app.quality.inspections.index', compact('inspections'));
    }

    public function create()
    {
        Gate::authorize('create quality inspections');
        $items = Item::all();
        $warehouses = Warehouse::all();
        $defectCodes = DefectCode::all();
        $types = ['iqc' => 'IQC来料检验', 'ipqc' => 'IPQC过程检验', 'fqc' => 'FQC成品检验', 'oqc' => 'OQC出货检验'];
        return view('app.quality.inspections.create', compact('items', 'warehouses', 'defectCodes', 'types'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create quality inspections');
        $validated = $request->validate([
            'inspection_type' => 'required|in:iqc,ipqc,fqc,oqc',
            'inspection_date' => 'required|date',
            'item_id' => 'required|exists:items,id',
            'batch_number' => 'nullable|string',
            'inspected_size' => 'required|integer|min:1',
            'qualified_size' => 'required|integer|min:0',
            'rejected_size' => 'required|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            // result 已 cast 为 QualityResult 枚举，取值必须与枚举一致（原 qualified/unqualified 会导致读取时枚举转换异常）
            'result' => 'required|in:pass,fail,conditional,waived',
        ]);

        // inspection_no 为唯一且非空字段，需在此生成（原代码未赋值会导致创建失败）
        $nextId = (int) (QualityInspection::max('id') ?? 0) + 1;
        $inspection = QualityInspection::create([
            'inspection_no' => 'QI' . date('Ymd') . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'inspection_type' => $validated['inspection_type'],
            'inspection_date' => $validated['inspection_date'],
            'item_id' => $validated['item_id'],
            'batch_number' => $validated['batch_number'] ?? null,
            'inspected_size' => $validated['inspected_size'],
            'qualified_size' => $validated['qualified_size'],
            'rejected_size' => $validated['rejected_size'],
            'warehouse_id' => $validated['warehouse_id'] ?? null,
            'result' => $validated['result'],
            'status' => QualityInspectionStatus::Completed,
        ]);

        $this->auditLog('create', 'QualityInspection', $inspection);

        event(new QualityCompleted($inspection));

        // 判定不合格时自动生成缺陷记录（不合格品台账入口），待质检处置
        if ($inspection->result === \App\Erp\Enums\QualityResult::Fail) {
            DefectRecord::create([
                'record_no' => DefectRecordController::nextRecordNo(),
                'defect_type' => $inspection->inspection_type?->value ?? 'iqc',
                'item_id' => $inspection->item_id,
                'batch_number' => $inspection->batch_number,
                'quantity' => max((float) $inspection->rejected_size, 0.0001),
                'disposition' => 'pending',
                'description' => '检验单 '.$inspection->inspection_no.' 判定不合格，待处置',
                'created_by' => auth()->id(),
            ]);

            $this->notificationService->sendToRole(
                'warehouse_manager',
                '质检不合格通知',
                "物料 #{$inspection->item_id} 检验不合格（{$inspection->rejected_size} 件），请处理。",
                'warning',
                route('app.quality.inspections.show', $inspection)
            );
        }

        return redirect()->route('app.quality.inspections.show', $inspection)->with('success', '检验单创建成功');
    }

    /** 追加检验项明细 */
    public function addLine(Request $request, QualityInspection $inspection)
    {
        Gate::authorize('create quality inspections');

        $data = $request->validate([
            'inspection_item' => 'required|string|max:200',
            'specification' => 'nullable|string|max:200',
            'result' => 'nullable|string|max:200',
            'judgment' => 'required|in:pass,fail,rework,pending',
            'remark' => 'nullable|string|max:2000',
        ], [], ['inspection_item' => '检验项目', 'judgment' => '判定']);

        $next = (int) ($inspection->lines()->max('line_number') ?? 0) + 1;

        QualityInspectionLine::create([
            'quality_inspection_id' => $inspection->id,
            'line_number' => $next,
            'inspection_item' => $data['inspection_item'],
            'specification' => $data['specification'] ?? null,
            'result' => $data['result'] ?? null,
            'judgment' => $data['judgment'],
            'remark' => $data['remark'] ?? null,
        ]);

        return back()->with('success', '检验项已追加。');
    }

    /** 检验判定：写结论、审批人，并对不合格项自动登记缺陷记录 */
    public function judge(Request $request, QualityInspection $inspection)
    {
        Gate::authorize('complete quality inspections');

        $data = $request->validate([
            'result' => 'required|in:pass,fail,conditional,waived',
            'qualified_size' => 'nullable|integer|min:0',
            'rejected_size' => 'nullable|integer|min:0',
            'remark' => 'nullable|string|max:2000',
        ], [], ['result' => '检验结论']);

        // 有 fail 明细行时必须判不合格或让步，禁止直接判合格
        $hasFailLine = $inspection->lines()->where('judgment', 'fail')->exists();
        if ($hasFailLine && in_array($data['result'], ['pass', 'waived'], true)) {
            return back()->with('warning', '存在判「不合格」的检验项，不能选择合格/豁免接收。');
        }

        $inspection->update([
            'result' => $data['result'],
            'qualified_size' => $data['qualified_size'] ?? $inspection->qualified_size,
            'rejected_size' => $data['rejected_size'] ?? $inspection->rejected_size,
            'remark' => $data['remark'] ?? $inspection->remark,
            'status' => QualityInspectionStatus::Completed,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->auditLog('judge', 'QualityInspection', $inspection, '检验判定 '.$inspection->inspection_no.' → '.$data['result']);

        if ($data['result'] === 'fail') {
            $exists = DefectRecord::where('description', 'like', '%'.$inspection->inspection_no.'%')->exists();
            if (! $exists) {
                DefectRecord::create([
                    'record_no' => DefectRecordController::nextRecordNo(),
                    'defect_type' => $inspection->inspection_type?->value ?? 'iqc',
                    'item_id' => $inspection->item_id,
                    'batch_number' => $inspection->batch_number,
                    'quantity' => max((float) ($data['rejected_size'] ?? $inspection->rejected_size), 0.0001),
                    'disposition' => 'pending',
                    'description' => '检验单 '.$inspection->inspection_no.' 判定不合格（'.$data['remark'].'）',
                    'created_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('app.quality.inspections.show', $inspection)->with('success', '检验判定已保存。');
    }

    public function show(QualityInspection $inspection)
    {
        Gate::authorize('view quality inspections');
        return view('app.quality.inspections.show', compact('inspection'));
    }

    public function reports()
    {
        Gate::authorize('view quality reports');
        $totalInspections = QualityInspection::count();
        $passCount = QualityInspection::where('result', 'pass')->count();
        $failCount = QualityInspection::where('result', 'fail')->count();
        $inspections = QualityInspection::with('item')->latest()->paginate(20);
        return view('app.quality.reports', compact('inspections', 'totalInspections', 'passCount', 'failCount'));
    }

    protected function auditLog(string $action, string $module, $record): void
    {
        \App\Erp\Models\System\AuditLog::create([
            'action' => $action,
            'module' => $module,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'record_id' => $record->id,
            'record_type' => get_class($record),
            'remark' => "{$module} #{$record->id} {$action}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
