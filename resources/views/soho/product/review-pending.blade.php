@extends('soho::layouts.app')

@section('title', '待审核商品')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待审版本</div><div class="fs-4 fw-semibold text-warning">{{ $stats['versions'] }}</div>
    </div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待审商品</div><div class="fs-4 fw-semibold">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已发布版本</div><div class="fs-4 fw-semibold text-success">{{ $stats['published'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待审核商品</h5>
        <a href="{{ route('soho.product.review.record') }}" class="btn btn-outline-secondary btn-sm">审核记录</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="商品名称 / 版本标题" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.product.review.pending') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>商品</th>
                        <th>版本标题</th>
                        <th class="text-center">版本号</th>
                        <th class="text-end">版本价</th>
                        <th class="text-center">商品状态</th>
                        <th class="text-center">提交时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $pst = ['draft' => ['草稿', 'secondary'], 'pending' => ['待审核', 'warning'], 'active' => ['在售', 'success']];
                        @endphp
                        <tr>
                            <td>
                                <div>{{ $row->product_name ?: '—' }}</div>
                                <div class="small text-muted font-monospace">{{ $row->product_code }}</div>
                            </td>
                            <td>{{ $row->title }}</td>
                            <td class="text-center">V{{ $row->version_no }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->price, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $pst[$row->product_status][1] ?? 'secondary' }}">{{ $pst[$row->product_status][0] ?? $row->product_status }}</span></td>
                            <td class="text-center small text-muted">{{ $row->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">
                            <i class="fa fa-circle-check text-success d-block fs-4 mb-2"></i>
                            没有待审核的商品版本。
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
