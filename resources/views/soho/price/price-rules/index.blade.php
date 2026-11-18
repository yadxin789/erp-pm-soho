@extends('soho::layouts.app')

@section('title', '价格规则')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">价格中心</a></li>
        <li class="breadcrumb-item active">价格规则</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-cogs me-2"></i>价格规则</h4>
    <a href="{{ route('price.price-rules.create') }}" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>新建规则
    </a>
</div>

{{-- 筛选表单 --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('price.price-rules.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">规则名称</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="输入规则名称">
            </div>
            <div class="col-md-2">
                <label class="form-label">规则类型</label>
                <select class="form-select" name="type">
                    <option value="">全部类型</option>
                    @foreach($typeLabels ?? [] as $value => $label)
                        <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">启用状态</label>
                <select class="form-select" name="status">
                    <option value="">全部</option>
                    <option value="enabled" {{ request('status') == 'enabled' ? 'selected' : '' }}>已启用</option>
                    <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>已禁用</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-1">
                    <i class="fa fa-search"></i> 筛选
                </button>
                <a href="{{ route('price.price-rules.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 规则列表 --}}
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-list me-2"></i>规则列表</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>规则名称</th>
                        <th>类型</th>
                        <th>描述</th>
                        <th>价格范围</th>
                        <th>优先级</th>
                        <th>状态</th>
                        <th>创建人</th>
                        <th>创建时间</th>
                        <th width="180">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $index => $rule)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $rule->name }}</div>
                                @if($rule->code)
                                    <code class="small text-muted">{{ $rule->code }}</code>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $typeLabels[$rule->type] ?? $rule->type }}</span>
                            </td>
                            <td class="small text-muted">{{ Str::limit($rule->description ?? '-', 50) }}</td>
                            <td class="small">
                                @if($rule->min_price || $rule->max_price)
                                    ¥{{ number_format($rule->min_price ?? 0, 2) }} - ¥{{ number_format($rule->max_price ?? 0, 2) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">优先级 {{ $rule->priority ?? 0 }}</span>
                            </td>
                            <td>
                                @if($rule->status === 'enabled')
                                    <span class="badge bg-success">已启用</span>
                                @else
                                    <span class="badge bg-secondary">已禁用</span>
                                @endif
                            </td>
                            <td class="small">{{ $rule->creator->name ?? '-' }}</td>
                            <td class="small">{{ $rule->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('price.price-rules.edit', $rule) }}" class="btn btn-sm btn-outline-primary" title="编辑">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('price.price-rules.toggle', $rule) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $rule->status === 'enabled' ? 'warning' : 'success' }}" title="{{ $rule->status === 'enabled' ? '禁用' : '启用' }}">
                                        <i class="fa {{ $rule->status === 'enabled' ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('price.price-rules.destroy', $rule) }}" class="d-inline" onsubmit="return confirm('确定删除此规则？')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="删除">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($rules, 'links'))
        <div class="card-footer">
            {{ $rules->links() }}
        </div>
    @endif
</div>
@endsection