@extends('soho::layouts.app')

@section('title', '商品发布任务')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">任务总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待执行 / 执行中</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">成功</div><div class="fs-4 fw-semibold text-success">{{ $stats['success'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">失败</div><div class="fs-4 fw-semibold text-danger">{{ $stats['failed'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">上架任务</h5>
        <a href="{{ route('soho.platform.product.publish') }}" class="btn btn-outline-secondary btn-sm">批量发布</a>
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
                        @foreach(['pending' => '待执行', 'publishing' => '执行中', 'success' => '成功', 'failed' => '失败'] as $k => $v)
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
                    <input type="text" name="search" class="form-control" placeholder="平台商品 ID" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.platform.publish.task') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>商品</th>
                        <th class="text-center">平台</th>
                        <th>店铺</th>
                        <th class="font-monospace">平台商品 ID</th>
                        <th class="text-center">状态</th>
                        <th>失败原因</th>
                        <th class="text-center">发布时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $st = ['pending' => ['待执行', 'warning'], 'publishing' => ['执行中', 'info'], 'success' => ['成功', 'success'], 'failed' => ['失败', 'danger']];
                            $product = $products[$row->system_goods_id] ?? null;
                        @endphp
                        <tr>
                            <td>
                                <div>{{ $product->name ?? '商品#'.$row->system_goods_id }}</div>
                                <div class="small text-muted font-monospace">{{ $product->product_code ?? '' }}</div>
                            </td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($row->platform) }}</span></td>
                            <td>{{ $shops[$row->shop_id]->shop_name ?? '店铺#'.$row->shop_id }}</td>
                            <td class="small text-muted">{{ $row->platform_goods_id ?: '—' }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->publish_status][1] ?? 'secondary' }}">{{ $st[$row->publish_status][0] ?? $row->publish_status }}</span></td>
                            <td class="small text-danger">{{ $row->error_message ?: '—' }}</td>
                            <td class="text-center small text-muted">{{ $row->published_at ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无上架任务。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
