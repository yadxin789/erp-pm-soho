@extends('soho::layouts.app')

@section('title', '税务主体')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">税务主体</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">税务主体列表</h5>
        <a href="{{ route('soho.tax_entities.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 创建税务主体
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.tax_entities.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索主体名称/税号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="company" @selected(request('type') == 'company')>企业</option>
                        <option value="individual" @selected(request('type') == 'individual')>个人</option>
                        <option value="partnership" @selected(request('type') == 'partnership')>合伙企业</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.tax_entities.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>主体名称</th>
                        <th>类型</th>
                        <th>税号</th>
                        <th>税率</th>
                        <th>状态</th>
                        <th>更新时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxEntities as $entity)
                    <tr>
                        <td>{{ $entity->id }}</td>
                        <td>{{ $entity->name }}</td>
                        <td><span class="badge bg-info">{{ $entity->type_label ?? $entity->type }}</span></td>
                        <td>{{ $entity->tax_no ?? '-' }}</td>
                        <td>{{ $entity->tax_rate ? $entity->tax_rate . '%' : '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $entity->status ? 'success' : 'secondary' }}">
                                {{ $entity->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td>{{ $entity->updated_at ? $entity->updated_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            <a href="{{ route('soho.tax_entities.edit', $entity) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $taxEntities->links() }}
    </div>
</div>
@endsection