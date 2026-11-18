@extends('soho::layouts.app')

@section('title', '权限变更日志')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-user-shield me-2"></i>权限变更日志</h4>
        <p class="text-muted mb-0 small">记录用户角色、权限的变更历史</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fa fa-history me-2 text-primary"></i>变更记录</h6>
    </div>
    <div class="card-body p-0">
        @if($logs->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">暂无权限变更记录</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>变更时间</th>
                            <th>变更类型</th>
                            <th>目标用户</th>
                            <th>操作人</th>
                            <th>关联角色/权限</th>
                            <th>描述</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td class="text-muted">{{ $log->id }}</td>
                                <td class="small">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <span class="badge bg-{{ $changeTypeColors[$log->change_type] ?? 'secondary' }}">
                                        {{ $changeTypeLabels[$log->change_type] ?? $log->change_type }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->targetUser)
                                        <div class="fw-semibold">{{ $log->targetUser->name }}</div>
                                        <div class="small text-muted">{{ $log->targetUser->email }}</div>
                                    @else
                                        <span class="text-muted">用户已删除</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($log->operator)
                                        {{ $log->operator->name }}
                                    @else
                                        <span class="text-muted">系统</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($log->role)
                                        <span class="badge bg-info">{{ $log->role->name }}</span>
                                    @elseif($log->permission)
                                        <span class="badge bg-light text-dark">{{ $log->permission->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $log->description ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="small text-muted">共 {{ $logs->count() }} 条记录</div>
            </div>
        @endif
    </div>
</div>
@endsection