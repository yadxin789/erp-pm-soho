@extends('soho::layouts.app')

@section('title', '登录日志')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-sign-in-alt me-2"></i>登录日志</h4>
        <p class="text-muted mb-0 small">查看用户登录历史，监控异常登录行为</p>
    </div>
</div>

{{-- 统计卡片 --}}
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-primary" style="font-size: 1.6rem;">{{ $stats['total'] }}</div>
                <div class="text-muted small">总记录</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-success" style="font-size: 1.6rem;">{{ $stats['success'] }}</div>
                <div class="text-muted small">登录成功</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-danger" style="font-size: 1.6rem;">{{ $stats['failed'] }}</div>
                <div class="text-muted small">登录失败</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-info" style="font-size: 1.6rem;">{{ $stats['unique_users'] }}</div>
                <div class="text-muted small">独立用户</div>
            </div>
        </div>
    </div>
</div>

{{-- 列表 --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fa fa-list me-2 text-primary"></i>登录记录</h6>
    </div>
    <div class="card-body p-0">
        @if($logs->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">暂无登录记录</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>时间</th>
                            <th>用户</th>
                            <th>状态</th>
                            <th>IP地址</th>
                            <th>浏览器/设备</th>
                            <th>登录方式</th>
                            <th>登出时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td class="text-muted">{{ $log->id }}</td>
                                <td class="small">{{ $log->login_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    @if($log->user)
                                        <div class="fw-semibold">{{ $log->user->name }}</div>
                                        <div class="small text-muted">{{ $log->user->email }}</div>
                                    @elseif($log->email)
                                        <div>{{ $log->email }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$log->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$log->status] ?? $log->status }}
                                    </span>
                                </td>
                                <td><code class="small">{{ $log->ip_address }}</code></td>
                                <td>
                                    <div class="small">
                                        {{ $log->browser ?? '-' }} / {{ $log->os ?? '-' }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $deviceTypeLabels[$log->device_type] ?? $log->device_type ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $loginTypeLabels[$log->login_type] ?? $log->login_type }}
                                    </span>
                                </td>
                                <td class="small">
                                    @if($log->logout_at)
                                        {{ $log->logout_at->format('Y-m-d H:i') }}
                                        @if($log->getOnlineDuration())
                                            <div class="text-muted">（{{ $log->getOnlineDuration() }}分钟）</div>
                                        @endif
                                    @elseif($log->isOnline())
                                        <span class="badge bg-success">在线</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
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