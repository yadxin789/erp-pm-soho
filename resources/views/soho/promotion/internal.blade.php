@extends('soho::layouts.app')

@section('title', '内部促销')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">活动总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">生效中</div><div class="fs-4 fw-semibold text-success">{{ $stats['active'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">进行中（在时间窗内）</div><div class="fs-4 fw-semibold text-primary">{{ $stats['running'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">草稿 / 已过期</div><div class="fs-4 fw-semibold"><span class="text-warning">{{ $stats['draft'] }}</span> / <span class="text-secondary">{{ $stats['expired'] }}</span></div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">站内促销活动</h5>
        <a href="{{ route('soho.promotion.analysis') }}" class="btn btn-outline-secondary btn-sm">促销分析</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        @foreach($types as $ty)
                            <option value="{{ $ty }}" @selected(request('type') === $ty)>
                                {{ ['fixed_discount' => '满减', 'percentage' => '折扣', 'buy_get' => '买赠'][$ty] ?? $ty }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['active' => '生效中', 'draft' => '草稿', 'inactive' => '已停用', 'expired' => '已过期'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="活动名称" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.promotion.internal') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>活动名称</th>
                        <th class="text-center">类型</th>
                        <th class="text-end">优惠力度</th>
                        <th class="text-end">门槛金额</th>
                        <th class="text-center">开始</th>
                        <th class="text-center">结束</th>
                        <th class="text-center">可叠加</th>
                        <th class="text-center">状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $typeMap = ['fixed_discount' => ['满减', 'primary'], 'percentage' => ['折扣', 'info'], 'buy_get' => ['买赠', 'warning']];
                            $st = ['active' => ['生效中', 'success'], 'draft' => ['草稿', 'secondary'], 'inactive' => ['已停用', 'dark'], 'expired' => ['已过期', 'secondary']];
                            $isRunning = $row->status === 'active' && $row->start_at <= now() && $row->end_at >= now();
                        @endphp
                        <tr>
                            <td>
                                <div>{{ $row->name }}</div>
                                @if($isRunning)<span class="badge bg-primary-subtle text-primary">进行中</span>@endif
                            </td>
                            <td class="text-center"><span class="badge bg-{{ $typeMap[$row->type][1] ?? 'secondary' }}">{{ $typeMap[$row->type][0] ?? $row->type }}</span></td>
                            <td class="text-end">
                                @if($row->type === 'percentage')
                                    {{ rtrim(rtrim(number_format((float) $row->value, 2), '0'), '.') }} 折
                                @elseif($row->type === 'fixed_discount')
                                    减 ¥{{ number_format((float) $row->value, 2) }}
                                @else
                                    赠品
                                @endif
                            </td>
                            <td class="text-end">¥{{ number_format((float) $row->min_amount, 2) }}</td>
                            <td class="text-center small text-muted">{{ $row->start_at }}</td>
                            <td class="text-center small text-muted">{{ $row->end_at }}</td>
                            <td class="text-center"><span class="badge bg-{{ $row->stackable ? 'success' : 'secondary' }}">{{ $row->stackable ? '可叠加' : '不可叠加' }}</span></td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">暂无站内促销活动。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
