@extends('layouts.business')

@section('title', '缺陷记录 '.$defect->record_no)

@section('content')
    @php
        $dispMap = ['pending' => ['待处置', 'warning'], 'rework' => ['返工', 'info'], 'reject' => ['退货', 'danger'], 'concession' => ['让步接收', 'secondary'], 'scrap' => ['报废', 'dark']];
        $typeMap = ['iqc' => 'IQC来料', 'ipqc' => 'IPQC过程', 'fqc' => 'FQC成品', 'oqc' => 'OQC出货', 'customer' => '客诉'];
        [$dl, $dc] = $dispMap[$defect->disposition] ?? [$defect->disposition, 'secondary'];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title">
            <i class="fa-solid fa-triangle-exclamation"></i>缺陷记录: {{ $defect->record_no }}
            <span class="badge bg-{{ $dc }}-subtle text-{{ $dc }}-emphasis border border-{{ $dc }}-subtle ms-2">{{ $dl }}</span>
        </h2>
        <a href="{{ erp_route('app.quality.defects.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>缺陷信息</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-4"><div class="text-muted small">记录号</div><div class="fw-semibold font-monospace">{{ $defect->record_no }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">缺陷类型</div><div class="fw-semibold">{{ $typeMap[$defect->defect_type] ?? $defect->defect_type }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">缺陷代码</div><div class="fw-semibold">{{ $defect->defectCode?->name ?? '—' }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">物料</div><div class="fw-semibold">{{ $defect->item?->name ?? '—' }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">生产订单</div><div class="fw-semibold">{{ $defect->productionOrder?->order_no ?? '—' }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">批次号</div><div class="fw-semibold">{{ $defect->batch_number ?? '—' }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">缺陷数量</div><div class="fw-semibold text-danger">{{ $defect->quantity }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">登记时间</div><div class="fw-semibold">{{ $defect->created_at?->format('Y-m-d H:i') ?? '—' }}</div></div>
                        <div class="col-12"><div class="text-muted small">缺陷描述</div><div>{{ $defect->description ?? '—' }}</div></div>
                        <div class="col-12"><div class="text-muted small">原因分析</div><div>{{ $defect->root_cause ?? '—' }}</div></div>
                        <div class="col-12"><div class="text-muted small">纠正措施</div><div>{{ $defect->corrective_action ?? '—' }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header"><i class="fa-solid fa-screwdriver-wrench me-2"></i>处置</div>
                <div class="card-body">
                    <form method="POST" action="{{ erp_route('app.quality.defects.handle', $defect) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="disposition">处置方式 <span class="text-danger">*</span></label>
                            <select id="disposition" name="disposition" class="form-select" required>
                                @foreach(['pending' => '待处置', 'rework' => '返工', 'reject' => '退货', 'concession' => '让步接收', 'scrap' => '报废'] as $k => $v)
                                    <option value="{{ $k }}" @selected($defect->disposition === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">选择返工/退货/让步接收/报废时，必须填写纠正措施。</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="root_cause">原因分析</label>
                            <textarea id="root_cause" name="root_cause" class="form-control" rows="3" maxlength="2000">{{ old('root_cause', $defect->root_cause) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="corrective_action">纠正措施</label>
                            <textarea id="corrective_action" name="corrective_action" class="form-control" rows="3" maxlength="2000">{{ old('corrective_action', $defect->corrective_action) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i>保存处置</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
