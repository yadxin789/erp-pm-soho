@extends('soho::layouts.app')

@section('title', '审批流')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">审批流</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">审批流列表</h5>
        <a href="{{ route('soho.approval_flows.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 创建审批流
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.approval_flows.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索审批流名称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="active" @selected(request('status') == 'active')>启用</option>
                        <option value="inactive" @selected(request('status') == 'inactive')>禁用</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.approval_flows.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>审批流名称</th>
                        <th>类型</th>
                        <th>触发条件</th>
                        <th>节点数</th>
                        <th>状态</th>
                        <th>更新时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvalFlows as $flow)
                    <tr>
                        <td>{{ $flow->id }}</td>
                        <td>{{ $flow->name }}</td>
                        <td><span class="badge bg-info">{{ $flow->type_label ?? $flow->type }}</span></td>
                        <td>{{ $flow->trigger_condition ?? '-' }}</td>
                        <td>{{ $flow->nodes_count ?? 0 }}</td>
                        <td>
                            <span class="badge bg-{{ $flow->status ? 'success' : 'secondary' }}">
                                {{ $flow->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td>{{ $flow->updated_at ? $flow->updated_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            <a href="{{ route('soho.approval_flows.edit', $flow) }}" class="btn btn-sm btn-outline-primary">
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
        {{ $approvalFlows->links() }}
    </div>
</div>
@endsection