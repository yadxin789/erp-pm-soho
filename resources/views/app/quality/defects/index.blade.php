@extends('layouts.business')

@section('title', '缺陷管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-triangle-exclamation"></i>缺陷管理</h2>
        <div class="d-flex gap-2">
            <span class="text-muted small align-self-center">缺陷代码字典 + 不合格品台账</span>
            <a href="{{ erp_route('app.quality.defects.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i>登记缺陷
            </a>
        </div>
    </div>

    @php
        $dispMap = ['pending' => ['待处置', 'warning'], 'rework' => ['返工', 'info'], 'reject' => ['退货', 'danger'], 'concession' => ['让步接收', 'secondary'], 'scrap' => ['报废', 'dark']];
        $typeMap = ['iqc' => 'IQC来料', 'ipqc' => 'IPQC过程', 'fqc' => 'FQC成品', 'oqc' => 'OQC出货', 'customer' => '客诉'];
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-primary-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">缺陷记录总数</div>
                    <div class="fs-3 fw-bold text-primary">{{ $records->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-warning-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">待处置</div>
                    <div class="fs-3 fw-bold text-warning-emphasis">{{ $records->where('disposition', 'pending')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-success-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">缺陷代码（启用）</div>
                    <div class="fs-3 fw-bold text-success">{{ $codes->where('active', true)->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-clipboard-list me-2"></i>不合格品记录</span>
            <span class="text-muted small">共 {{ $records->total() }} 条</span>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">缺陷类型</label>
                    <select name="defect_type" class="form-select form-select-sm">
                        <option value="">全部</option>
                        @foreach($typeMap as $k => $v)
                            <option value="{{ $k }}" @selected(request('defect_type') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">处置状态</label>
                    <select name="disposition" class="form-select form-select-sm">
                        <option value="">全部</option>
                        @foreach($dispMap as $k => $v)
                            <option value="{{ $k }}" @selected(request('disposition') === $k)>{{ $v[0] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">关键字</label>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="记录号 / 批次 / 描述">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-magnifying-glass me-1"></i>查询</button>
                    <a href="{{ erp_route('app.quality.defects.index') }}" class="btn btn-outline-secondary btn-sm">重置</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>记录号</th>
                            <th>缺陷类型</th>
                            <th>缺陷代码</th>
                            <th>物料</th>
                            <th>批次</th>
                            <th class="text-end">数量</th>
                            <th class="text-center">处置</th>
                            <th class="text-center">登记</th>
                            <th class="text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            @php [$dl, $dc] = $dispMap[$record->disposition] ?? [$record->disposition, 'secondary']; @endphp
                            <tr>
                                <td class="font-monospace small">{{ $record->record_no ?? '—' }}</td>
                                <td>{{ $typeMap[$record->defect_type] ?? ($record->defect_type ?? '—') }}</td>
                                <td>{{ $record->defectCode?->name ?? '—' }}</td>
                                <td>{{ $record->item?->name ?? '—' }}</td>
                                <td class="small text-muted">{{ $record->batch_number ?? '—' }}</td>
                                <td class="text-end">{{ $record->quantity }}</td>
                                <td class="text-center"><span class="badge bg-{{ $dc }}-subtle text-{{ $dc }}-emphasis border border-{{ $dc }}-subtle">{{ $dl }}</span></td>
                                <td class="text-center small text-muted">{{ $record->created_at?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ erp_route('app.quality.defects.show', $record) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i>详情
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="erp-empty">暂无缺陷记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">{{ $records->links() }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-book me-2"></i>缺陷代码字典</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>代码</th>
                            <th>名称</th>
                            <th>分类</th>
                            <th>描述</th>
                            <th class="text-center">状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($codes as $code)
                            <tr>
                                <td class="font-monospace small">{{ $code->code }}</td>
                                <td>{{ $code->name }}</td>
                                <td><span class="badge bg-light text-secondary border">{{ $code->category }}</span></td>
                                <td class="text-muted">{{ $code->description ?? '—' }}</td>
                                <td class="text-center">
                                    @if($code->active)
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">启用</span>
                                    @else
                                        <span class="badge bg-light text-secondary border">停用</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="erp-empty">暂无缺陷代码数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
