@extends('soho::layouts.app')

@section('title', '审计日志')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">审计日志</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">审计日志列表</h5>
        <div>
            <a href="{{ route('soho.log.audit.index') }}" class="btn btn-outline-primary">
                <i class="fa fa-refresh"></i> 刷新
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.log.audit.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索操作人/操作描述" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="action" class="form-select">
                        <option value="">全部操作</option>
                        <option value="login" @selected(request('action') == 'login')>登录</option>
                        <option value="logout" @selected(request('action') == 'logout')>登出</option>
                        <option value="create" @selected(request('action') == 'create')>创建</option>
                        <option value="update" @selected(request('action') == 'update')>更新</option>
                        <option value="delete" @selected(request('action') == 'delete')>删除</option>
                        <option value="export" @selected(request('action') == 'export')>导出</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="结束日期">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.log.audit.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>操作人</th>
                        <th>操作类型</th>
                        <th>操作对象</th>
                        <th>IP地址</th>
                        <th>User Agent</th>
                        <th>状态</th>
                        <th>操作时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->user->name ?? '-' }}</td>
                        <td>
                            @if($log->action == 'login')
                                <span class="badge bg-info">登录</span>
                            @elseif($log->action == 'logout')
                                <span class="badge bg-secondary">登出</span>
                            @elseif($log->action == 'create')
                                <span class="badge bg-success">创建</span>
                            @elseif($log->action == 'update')
                                <span class="badge bg-warning text-dark">更新</span>
                            @elseif($log->action == 'delete')
                                <span class="badge bg-danger">删除</span>
                            @else
                                <span class="badge bg-light text-dark">{{ $log->action }}</span>
                            @endif
                        </td>
                        <td>{{ $log->target_type ?? '-' }} #{{ $log->target_id ?? '-' }}</td>
                        <td>{{ $log->ip_address ?? '-' }}</td>
                        <td>
                            <small class="text-muted" title="{{ $log->user_agent ?? '' }}">
                                {{ \Illuminate\Support\Str::limit($log->user_agent ?? '-', 30) }}
                            </small>
                        </td>
                        <td>
                            @if($log->status == 'success')
                                <span class="badge bg-success">成功</span>
                            @else
                                <span class="badge bg-danger">失败</span>
                            @endif
                        </td>
                        <td>{{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $auditLogs->links() }}
    </div>
</div>
@endsection