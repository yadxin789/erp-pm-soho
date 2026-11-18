@extends('soho::layouts.app')

@section('title', '登录失败记录')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-exclamation-triangle me-2"></i>登录失败记录</h4>
        <p class="text-muted mb-0 small">监控登录失败尝试，检测暴力破解和异常登录行为</p>
    </div>
</div>

{{-- 统计卡片 --}}
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-primary" style="font-size: 1.6rem;">{{ $stats['total'] }}</div>
                <div class="text-muted small">失败总数（{{ $stats['hours'] }}小时内）</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-warning" style="font-size: 1.6rem;">{{ $stats['unique_ips'] }}</div>
                <div class="text-muted small">唯一IP数</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-info" style="font-size: 1.6rem;">{{ $stats['unique_emails'] }}</div>
                <div class="text-muted small">受攻击邮箱数</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-danger" style="font-size: 1.6rem;">{{ $stats['lockouts'] }}</div>
                <div class="text-muted small">触发锁定次数</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- 左侧：失败记录列表 --}}
    <div class="col-lg-8 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fa fa-list me-2 text-primary"></i>失败记录</h6>
            </div>
            <div class="card-body p-0">
                @if($failures->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa fa-shield fa-3x text-success mb-3"></i>
                        <p class="text-muted">暂无登录失败记录</p>
                    </div>
                @else
                    @php
                        $reasonColors = \App\Soho\Models\LoginFailure::getReasonColors();
                        $reasonIcons = \App\Soho\Models\LoginFailure::getReasonIcons();
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>时间</th>
                                    <th>邮箱/用户</th>
                                    <th>IP地址</th>
                                    <th>失败原因</th>
                                    <th>锁定</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($failures as $failure)
                                    <tr>
                                        <td class="text-muted">{{ $failure->id }}</td>
                                        <td class="small">{{ $failure->created_at->format('m-d H:i:s') }}</td>
                                        <td>
                                            @if($failure->user)
                                                <div class="fw-semibold">{{ $failure->user->name }}</div>
                                                <div class="small text-muted">{{ $failure->email }}</div>
                                            @elseif($failure->email)
                                                <div>{{ $failure->email }}</div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <code class="small">{{ $failure->ip_address }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $reasonColors[$failure->failure_reason] ?? 'secondary' }}">
                                                <i class="fa {{ $reasonIcons[$failure->failure_reason] ?? 'fa-times' }} me-1"></i>
                                                {{ $reasons[$failure->failure_reason] ?? $failure->failure_reason }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($failure->triggered_lockout)
                                                <span class="badge bg-danger">已锁定</span>
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
                        <div class="small text-muted">共 {{ $failures->count() }} 条记录</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 右侧：统计面板 --}}
    <div class="col-lg-4 mb-3">
        {{-- 高风险IP --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fa fa-ban me-2 text-danger"></i>高风险IP（≥5次）</h6>
            </div>
            <div class="card-body p-0">
                @if($stats['high_risk_ips']->isEmpty())
                    <div class="text-center py-3">
                        <i class="fa fa-check-circle text-success me-1"></i>
                        <span class="text-muted small">暂无高风险IP</span>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($stats['high_risk_ips'] as $ip)
                            <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                                <code class="small">{{ $ip->ip_address }}</code>
                                <span class="badge bg-danger">{{ $ip->count }} 次</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- 失败原因分布 --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fa fa-chart-pie me-2 text-primary"></i>失败原因分布</h6>
            </div>
            <div class="card-body p-0">
                @if(empty($stats['by_reason']))
                    <div class="text-center py-3">
                        <span class="text-muted small">暂无数据</span>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($stats['by_reason'] as $reason => $count)
                            <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $reasonColors[$reason] ?? 'secondary' }}">
                                    {{ $reasons[$reason] ?? $reason }}
                                </span>
                                <span class="fw-semibold">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection