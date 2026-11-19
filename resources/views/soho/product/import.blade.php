@extends('soho::layouts.app')

@section('title', '商品导入')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">商品总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">草稿</div><div class="fs-4 fw-semibold text-secondary">{{ $stats['draft'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待审核</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">在售</div><div class="fs-4 fw-semibold text-success">{{ $stats['active'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">批量导入</h5>
        <a href="{{ route('soho.product.import.template') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-download me-1"></i> 下载模板
        </a>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted mb-3">
            模板列：product_code / name / subtitle / category_id / cost_price / retail_price / weight / description。
            导入后商品状态为「草稿」，需走审核流程上架；product_code 重复或名称为空的行会被跳过。
        </div>
        <form method="POST" action="{{ route('soho.product.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">CSV 文件（UTF-8，≤ 2MB）</label>
                    <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-upload me-1"></i> 开始导入</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">最近导入 / 新建商品</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>商品编码</th>
                        <th>商品名称</th>
                        <th class="text-end">成本价</th>
                        <th class="text-end">零售价</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent as $row)
                        @php $st = ['draft' => ['草稿', 'secondary'], 'pending' => ['待审核', 'warning'], 'active' => ['在售', 'success']]; @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->product_code }}</td>
                            <td>{{ $row->name }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->cost_price, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->retail_price, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="text-center small text-muted">{{ $row->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">暂无商品，先导入一批。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
