@extends('layouts.business')

@section('title', '检验单详情')

@section('content')
    @php
        $result = $inspection->result;
        $resultBadge = match ($result) {
            \App\Erp\Enums\QualityResult::Pass => 'bg-success-subtle text-success-emphasis border border-success-subtle',
            \App\Erp\Enums\QualityResult::Fail => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
            \App\Erp\Enums\QualityResult::Conditional => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            \App\Erp\Enums\QualityResult::Waived => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
            default => 'bg-light text-secondary border',
        };
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-check"></i>检验单: {{ $inspection->inspection_no }}</h2>
        <a href="{{ erp_route('app.quality.inspections.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-muted small">检验类型</div>
                    <div class="fw-semibold">{{ $inspection->inspection_type?->label() ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">检验日期</div>
                    <div class="fw-semibold">{{ $inspection->inspection_date?->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">物料</div>
                    <div class="fw-semibold">{{ $inspection->item?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">批次号</div>
                    <div class="fw-semibold">{{ $inspection->batch_number ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">检验数量</div>
                    <div class="fw-semibold">{{ $inspection->inspected_size }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">合格数量</div>
                    <div class="fw-semibold text-success">{{ $inspection->qualified_size }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">不合格数量</div>
                    <div class="fw-semibold text-danger">{{ $inspection->rejected_size }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">结果</div>
                    <div><span class="badge {{ $resultBadge }}">{{ $result?->label() ?? '-' }}</span></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">状态</div>
                    <div class="fw-semibold">{{ $inspection->status?->label() ?? '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">备注</div>
                    <div class="fw-semibold">{{ $inspection->remark ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">审批人 / 审批时间</div>
                    <div class="fw-semibold">{{ $inspection->approved_by ? '#'.$inspection->approved_by.' '.$inspection->approved_at : '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    @php
        $judgeMap = ['pass' => ['合格', 'success'], 'fail' => ['不合格', 'danger'], 'rework' => ['返工', 'warning'], 'pending' => ['待判', 'secondary']];
    @endphp

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-list-check me-2"></i>检验项明细</span>
            <span class="text-muted small">共 {{ $inspection->lines()->count() }} 项</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width:60px">序号</th>
                            <th>检验项目</th>
                            <th>规格要求</th>
                            <th>实测值</th>
                            <th class="text-center">判定</th>
                            <th>备注</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inspection->lines()->orderBy('line_number')->get() as $line)
                            @php [$jl, $jc] = $judgeMap[$line->judgment] ?? [$line->judgment, 'secondary']; @endphp
                            <tr>
                                <td class="text-muted small">{{ $line->line_number }}</td>
                                <td>{{ $line->inspection_item }}</td>
                                <td class="text-muted">{{ $line->specification ?? '—' }}</td>
                                <td class="text-muted">{{ $line->result ?? '—' }}</td>
                                <td class="text-center"><span class="badge bg-{{ $jc }}-subtle text-{{ $jc }}-emphasis border border-{{ $jc }}-subtle">{{ $jl }}</span></td>
                                <td class="text-muted small">{{ $line->remark ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="erp-empty">暂无检验项明细</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ erp_route('app.quality.inspections.lines.store', $inspection) }}" class="row g-2 align-items-end mt-2">
                @csrf
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted mb-1">检验项目 <span class="text-danger">*</span></label>
                    <input type="text" name="inspection_item" class="form-control form-control-sm" required maxlength="200">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted mb-1">规格要求</label>
                    <input type="text" name="specification" class="form-control form-control-sm" maxlength="200">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">实测值</label>
                    <input type="text" name="result" class="form-control form-control-sm" maxlength="200">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">判定 <span class="text-danger">*</span></label>
                    <select name="judgment" class="form-select form-select-sm" required>
                        <option value="pass">合格</option>
                        <option value="fail">不合格</option>
                        <option value="rework">返工</option>
                        <option value="pending">待判</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-outline-primary btn-sm w-100"><i class="fa-solid fa-plus me-1"></i>追加</button>
                </div>
                <div class="col-12">
                    <input type="text" name="remark" class="form-control form-control-sm" placeholder="备注（可选）" maxlength="2000">
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header"><i class="fa-solid fa-gavel me-2"></i>检验判定</div>
        <div class="card-body">
            <form method="POST" action="{{ erp_route('app.quality.inspections.judge', $inspection) }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-12 col-md-3">
                    <label class="form-label" for="judge_result">检验结论 <span class="text-danger">*</span></label>
                    <select id="judge_result" name="result" class="form-select" required>
                        @foreach(\App\Erp\Enums\QualityResult::options() as $k => $v)
                            <option value="{{ $k }}" @selected($inspection->result?->value === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">存在「不合格」检验项时不能判合格/豁免。</div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" for="judge_qualified">合格数量</label>
                    <input type="number" min="0" id="judge_qualified" name="qualified_size" class="form-control" value="{{ $inspection->qualified_size }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" for="judge_rejected">不合格数量</label>
                    <input type="number" min="0" id="judge_rejected" name="rejected_size" class="form-control" value="{{ $inspection->rejected_size }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="judge_remark">判定说明</label>
                    <input type="text" id="judge_remark" name="remark" class="form-control" maxlength="2000" value="{{ old('remark') }}">
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-primary w-100"><i class="fa-solid fa-check me-1"></i>保存判定</button>
                </div>
            </form>
            <div class="form-text mt-2">判「不合格」时自动登记不合格品记录（待处置），进入缺陷台账。</div>
        </div>
    </div>
@endsection
