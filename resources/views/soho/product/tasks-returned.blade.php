@extends('soho::layouts.app')

@section('title', '退回商品')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">退回（未发布）版本</div><div class="fs-4 fw-semibold text-warning">{{ $stats['returned'] }}</div>
    </div></div></div>
    <div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">超 30 天未处理</div><div class="fs-4 fw-semibold text-danger">{{ $stats['stale'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">退回商品处理</h5>
        <a href="{{ route('soho.product.tasks.pending-edit') }}" class="btn btn-outline-secondary btn-sm">待修改商品</a>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            退回原因需重新编辑后再次提交审核；超过 30 天未更新的版本视为停滞，优先处理。
        </div>

        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="商品名称 / 版本标题" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.product.tasks.returned') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>商品</th>
                        <th>退回版本</th>
                        <th class="text-end">版本价</th>
                        <th class="text-center">停留天数</th>
                        <th class="text-center">最后更新</th>
                        <th class="text-center">状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $days = $row->updated_at ? now()->diffInDays($row->updated_at) : 0; @endphp
                        <tr>
                            <td>
                                <div>{{ $row->product_name ?: '—' }}</div>
                                <div class="small text-muted font-monospace">{{ $row->product_code }}</div>
                            </td>
                            <td>{{ $row->title }} <span class="text-muted small">V{{ $row->version_no }}</span></td>
                            <td class="text-end">¥{{ number_format((float) $row->price, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $days > 30 ? 'danger' : 'secondary' }}">{{ $days }} 天</span></td>
                            <td class="text-center small text-muted">{{ $row->updated_at }}</td>
                            <td class="text-center"><span class="badge bg-warning text-dark">待重新提交</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">
                            <i class="fa fa-circle-check text-success d-block fs-4 mb-2"></i>
                            没有退回待处理的商品版本。
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
