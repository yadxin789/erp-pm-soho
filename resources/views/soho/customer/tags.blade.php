@extends('soho::layouts.app')

@section('title', '客户标签')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small mb-1">标签总数</div>
            <div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small mb-1">手工标签</div>
            <div class="fs-4 fw-semibold">{{ $stats['manual'] }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small mb-1">规则标签</div>
            <div class="fs-4 fw-semibold">{{ $stats['auto'] }}</div>
        </div></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">新建标签</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.customer.tags.store') }}">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">标签名称</label>
                    <input type="text" name="name" class="form-control" placeholder="如：高意向客户" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">类型</label>
                    <select name="type" class="form-select" required>
                        <option value="manual">手工标签</option>
                        <option value="auto">规则标签</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">颜色</label>
                    <input type="color" name="color" class="form-control form-control-color" value="#4ECDC4">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">规则配置（规则标签填写）</label>
                    <input type="text" name="condition_config" class="form-control" placeholder="如：order_count>=3">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa fa-plus me-1"></i> 创建</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">标签列表</h5>
        <a href="{{ route('soho.customer.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 返回客户
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索标签名称" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="manual" @selected(request('type') === 'manual')>手工标签</option>
                        <option value="auto" @selected(request('type') === 'auto')>规则标签</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.customer.tags') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        @forelse($tags as $tag)
            <div class="border rounded-3 mb-3">
                <div class="px-3 py-2 bg-light rounded-top d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-circle" style="width:14px;height:14px;background:{{ $tag->color ?: '#6c757d' }}"></span>
                        <span class="fw-semibold">{{ $tag->name }}</span>
                        <span class="badge bg-{{ $tag->type === 'auto' ? 'info' : 'secondary' }}">{{ $tag->type === 'auto' ? '规则标签' : '手工标签' }}</span>
                    </div>
                    <div class="small text-muted">创建于 {{ $tag->created_at }}</div>
                </div>
                <div class="p-3">
                    <form method="POST" action="{{ route('soho.customer.tags.update', $tag->id) }}" class="row g-2 align-items-end">
                        @csrf
                        @method('PUT')
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">名称</label>
                            <input type="text" name="name" class="form-control" value="{{ $tag->name }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">类型</label>
                            <select name="type" class="form-select">
                                <option value="manual" @selected($tag->type === 'manual')>手工标签</option>
                                <option value="auto" @selected($tag->type === 'auto')>规则标签</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">颜色</label>
                            <input type="color" name="color" class="form-control form-control-color" value="{{ $tag->color ?: '#6c757d' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">规则配置</label>
                            <input type="text" name="condition_config" class="form-control" value="{{ $tag->condition_config }}">
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-primary flex-fill">保存</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('soho.customer.tags.destroy', $tag->id) }}" class="mt-2 text-end" onsubmit="return confirm('确认删除标签「{{ $tag->name }}」？')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash me-1"></i> 删除标签</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fa fa-tags fs-3 d-block mb-2"></i>
                还没有客户标签，可在上方创建。
            </div>
        @endforelse

        {{ $tags->links() }}
    </div>
</div>
@endsection
