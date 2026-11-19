@extends('soho::layouts.app')

@section('title', '平台属性')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">属性数</div><div class="fs-4 fw-semibold">{{ $stats['attributes'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">选择型（select）</div><div class="fs-4 fw-semibold">{{ $stats['select'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">输入型（input）</div><div class="fs-4 fw-semibold">{{ $stats['input'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">可选值总数</div><div class="fs-4 fw-semibold">{{ $stats['values'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">平台属性字典</h5>
        <a href="{{ route('soho.platform.category.mapping') }}" class="btn btn-outline-secondary btn-sm">分类映射</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="select" @selected(request('type') === 'select')>选择型</option>
                        <option value="input" @selected(request('type') === 'input')>输入型</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.platform.attribute') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>属性名称</th>
                        <th class="font-monospace">编码</th>
                        <th class="text-center">类型</th>
                        <th>可选值</th>
                        <th class="text-center">排序</th>
                        <th class="text-center">状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attributes as $attr)
                        @php $opts = $values[$attr->id] ?? collect(); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $attr->name }}</td>
                            <td class="small text-muted">{{ $attr->code }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $attr->type === 'select' ? 'info' : 'secondary' }}">{{ $attr->type === 'select' ? '选择型' : '输入型' }}</span>
                            </td>
                            <td>
                                @forelse($opts as $opt)
                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $opt->value_name }}</span>
                                @empty
                                    <span class="text-muted small">—</span>
                                @endforelse
                            </td>
                            <td class="text-center small text-muted">{{ $attr->sort }}</td>
                            <td class="text-center"><span class="badge bg-{{ $attr->status ? 'success' : 'secondary' }}">{{ $attr->status ? '启用' : '停用' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">暂无平台属性。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
