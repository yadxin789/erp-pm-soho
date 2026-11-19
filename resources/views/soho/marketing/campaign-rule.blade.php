@extends('soho::layouts.app')

@section('title', '活动规则')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">活动总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">可叠加</div><div class="fs-4 fw-semibold text-success">{{ $stats['stackable'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">设门槛金额</div><div class="fs-4 fw-semibold">{{ $stats['with_threshold'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">设适用条件</div><div class="fs-4 fw-semibold">{{ $stats['with_conditions'] }}</div>
    </div></div></div>
</div>

@if($editing)
<div class="card mb-3 border-primary">
    <div class="card-header d-flex justify-content-between align-items-center bg-primary-subtle">
        <h5 class="mb-0">编辑规则：{{ $editing->name }}</h5>
        <a href="{{ route('soho.marketing.campaign.rule') }}" class="btn btn-sm btn-outline-secondary">取消</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.marketing.campaign.rule') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $editing->id }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">门槛金额（元）</label>
                    <input type="number" step="0.01" min="0" name="min_amount" class="form-control" value="{{ $editing->min_amount }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">是否可叠加</label>
                    <select name="stackable" class="form-select">
                        <option value="1" @selected($editing->stackable)>可叠加</option>
                        <option value="0" @selected(! $editing->stackable)>不可叠加</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">适用条件（JSON）</label>
                    <input type="text" name="conditions" class="form-control font-monospace" value="{{ $editing->conditions_json }}" placeholder='{"category": "all"}'>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk me-1"></i> 保存规则</button>
                </div>
            </div>
            <div class="form-text mt-2">条件需为合法 JSON，留空表示无额外限制（如 {"category":"all"}、{"brand":[1,2]}）。</div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">活动规则清单</h5>
        <a href="{{ route('soho.marketing.campaign.join') }}" class="btn btn-outline-secondary btn-sm">活动报名</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['active' => '生效中', 'draft' => '草稿', 'inactive' => '已停用', 'expired' => '已过期'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.marketing.campaign.rule') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>活动名称</th>
                        <th class="text-center">类型</th>
                        <th class="text-end">门槛金额</th>
                        <th class="text-center">可叠加</th>
                        <th>适用条件</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $st = ['active' => ['生效中', 'success'], 'draft' => ['草稿', 'secondary'], 'inactive' => ['已停用', 'dark'], 'expired' => ['已过期', 'secondary']];
                        @endphp
                        <tr>
                            <td>{{ $row->name }}</td>
                            <td class="text-center">{{ ['fixed_discount' => '满减', 'percentage' => '折扣', 'buy_get' => '买赠'][$row->type] ?? $row->type }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->min_amount, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $row->stackable ? 'success' : 'secondary' }}">{{ $row->stackable ? '可叠加' : '不可叠加' }}</span></td>
                            <td class="small text-muted font-monospace">{{ $row->conditions_json ?: '（无）' }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('soho.marketing.campaign.rule', ['edit' => $row->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-pen"></i> 编辑
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无促销活动规则。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
