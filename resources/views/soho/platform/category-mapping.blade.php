@extends('soho::layouts.app')

@section('title', '分类映射')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">本地分类</div><div class="fs-4 fw-semibold">{{ $stats['categories'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已映射 SPU</div><div class="fs-4 fw-semibold text-success">{{ $stats['mapped'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">涉及平台</div><div class="fs-4 fw-semibold">{{ $stats['platforms'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">未映射平台商品</div><div class="fs-4 fw-semibold text-warning">{{ $stats['unmapped'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">本地分类与商品数</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>分类名称</th><th class="text-center">上级</th><th class="text-center">状态</th><th class="text-center">关联商品</th></tr></thead>
                <tbody>
                    @forelse($localCategories as $c)
                        <tr>
                            <td>{{ $c->name }}</td>
                            <td class="text-center small text-muted">
                                {{ $c->parent_id ? ($localCategories->firstWhere('id', $c->parent_id)->name ?? '#'.$c->parent_id) : '—' }}
                            </td>
                            <td class="text-center"><span class="badge bg-{{ $c->is_active ? 'success' : 'secondary' }}">{{ $c->is_active ? '启用' : '停用' }}</span></td>
                            <td class="text-center">{{ $productCategory[$c->id] ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">暂无分类。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">平台类目映射（来自平台商品的平台类目 ID）</h5>
        <a href="{{ route('soho.platform.listing') }}" class="btn btn-outline-secondary btn-sm">Listing 管理</a>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            映射数据取自平台商品的 `platform_category_id`；同一平台类目下统计商品条数与去重 SPU 数。
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center">平台</th>
                        <th class="font-monospace">平台类目 ID</th>
                        <th class="text-center">商品条数</th>
                        <th class="text-center">SPU 数</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mapping as $row)
                        <tr>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($row->platform) }}</span></td>
                            <td class="small">{{ $row->platform_category_id }}</td>
                            <td class="text-center">{{ $row->product_count }}</td>
                            <td class="text-center">{{ $row->spu_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">尚无平台类目映射数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
