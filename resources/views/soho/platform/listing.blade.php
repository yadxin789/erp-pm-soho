@extends('soho::layouts.app')

@section('title', 'Listing 管理')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">平台商品数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已上架</div><div class="fs-4 fw-semibold text-success">{{ $stats['online'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">审核中</div><div class="fs-4 fw-semibold text-warning">{{ $stats['reviewing'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">被驳回</div><div class="fs-4 fw-semibold text-danger">{{ $stats['rejected'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">平台商品（Listing）</h5>
        <a href="{{ route('soho.platform.publish.task') }}" class="btn btn-outline-secondary btn-sm">上架任务</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        @foreach($platforms as $p)
                            <option value="{{ $p }}" @selected(request('platform') === $p)>{{ strtoupper($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['online' => '已上架', 'reviewing' => '审核中', 'offline' => '已下架', 'rejected' => '被驳回'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach($shops as $s)
                            <option value="{{ $s->id }}" @selected(request('shop_id') == $s->id)>{{ $s->shop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="标题 / 平台商品 ID / SKU" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.platform.listing') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>平台商品标题</th>
                        <th class="text-center">平台</th>
                        <th>店铺</th>
                        <th class="font-monospace">平台商品 ID</th>
                        <th class="font-monospace">平台 SKU</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">最近同步</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $st = ['online' => ['已上架', 'success'], 'reviewing' => ['审核中', 'warning'], 'offline' => ['已下架', 'secondary'], 'rejected' => ['被驳回', 'danger']]; @endphp
                        <tr>
                            <td>{{ $row->title }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($row->platform) }}</span></td>
                            <td>{{ $shops[$row->shop_id]->shop_name ?? '店铺#'.$row->shop_id }}</td>
                            <td class="small text-muted">{{ $row->platform_product_id ?: '—' }}</td>
                            <td class="small text-muted">{{ $row->platform_sku ?: '—' }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->platform_status][1] ?? 'secondary' }}">{{ $st[$row->platform_status][0] ?? $row->platform_status }}</span></td>
                            <td class="text-center small text-muted">{{ $row->last_synced_at ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无平台商品。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
