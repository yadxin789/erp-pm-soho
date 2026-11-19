@extends('soho::layouts.app')

@section('title', '商品变更审核')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">变更记录</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">价格变更</div><div class="fs-4 fw-semibold text-warning">{{ $stats['price'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">今日变更</div><div class="fs-4 fw-semibold">{{ $stats['today'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">涉及操作人</div><div class="fs-4 fw-semibold">{{ $stats['operators'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">商品字段变更记录</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="field" class="form-select">
                        <option value="">全部字段</option>
                        @foreach($fields as $field)
                            <option value="{{ $field }}" @selected(request('field') === $field)>{{ ['price' => '价格', 'title' => '标题', 'stock' => '库存', 'status' => '状态'][$field] ?? $field }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="商品名称 / 操作人" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.product.review.change') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>商品</th>
                        <th class="text-center">字段</th>
                        <th class="text-center">操作</th>
                        <th>变更前</th>
                        <th>变更后</th>
                        <th class="text-center">操作人</th>
                        <th class="text-center">操作时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>
                            <td class="text-center small">{{ ['price' => '价格', 'title' => '标题', 'stock' => '库存', 'status' => '状态'][$row->changed_field] ?? $row->changed_field }}</td>
                            <td class="text-center"><span class="badge bg-{{ $row->operation_type === 'update' ? 'info' : 'secondary' }}">{{ ['update' => '修改', 'create' => '新建', 'delete' => '删除'][$row->operation_type] ?? $row->operation_type }}</span></td>
                            <td class="small text-muted text-decoration-line-through">{{ $row->old_value ?: '—' }}</td>
                            <td class="small fw-semibold">{{ $row->new_value ?: '—' }}</td>
                            <td class="text-center small">{{ $row->operator }}</td>
                            <td class="text-center small text-muted">{{ $row->operated_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无变更记录。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
