@extends('soho::layouts.app')

@section('title', '审批实例')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">审批实例</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">审批实例列表</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.approval_instances.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索审批标题/编号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="pending" @selected(request('status') == 'pending')>待审批</option>
                        <option value="approved" @selected(request('status') == 'approved')>已通过</option>
                        <option value="rejected" @selected(request('status') == 'rejected')>已驳回</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.approval_instances.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>审批标题</th>
                        <th>类型</th>
                        <th>申请人</th>
                        <th>当前节点</th>
                        <th>状态</th>
                        <th>申请时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvalInstances as $instance)
                    <tr>
                        <td>{{ $instance->id }}</td>
                        <td>{{ $instance->title }}</td>
                        <td><span class="badge bg-info">{{ $instance->type_label ?? $instance->type }}</span></td>
                        <td>{{ $instance->applicant->name ?? '-' }}</td>
                        <td>{{ $instance->current_node ?? '-' }}</td>
                        <td>
                            @if($instance->status == 'pending')
                                <span class="badge bg-warning text-dark">待审批</span>
                            @elseif($instance->status == 'approved')
                                <span class="badge bg-success">已通过</span>
                            @elseif($instance->status == 'rejected')
                                <span class="badge bg-danger">已驳回</span>
                            @else
                                <span class="badge bg-secondary">{{ $instance->status }}</span>
                            @endif
                        </td>
                        <td>{{ $instance->created_at ? $instance->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            @if($instance->status == 'pending')
                                <form method="POST" action="{{ route('soho.approval_instances.approve', $instance) }}" class="d-inline" onsubmit="return confirm('确认通过？')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="fa fa-check"></i> 通过
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('soho.approval_instances.reject', $instance) }}" class="d-inline" onsubmit="return confirm('确认驳回？')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-times"></i> 驳回
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $approvalInstances->links() }}
    </div>
</div>
@endsection