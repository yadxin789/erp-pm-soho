@extends('soho::layouts.app')

@section('title', '系统健康检查')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-heartbeat me-2"></i>系统健康检查</h4>
        <p class="text-muted mb-0 small">检查系统各组件运行状态，监控整体健康度</p>
    </div>
    <a href="{{ route('soho.system.health') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-refresh me-1"></i>重新检查
    </a>
</div>

@php
    // 状态样式映射
    $statusConfig = [
        'healthy' => ['label' => '正常', 'color' => 'success', 'icon' => 'fa-check-circle'],
        'warning' => ['label' => '警告', 'color' => 'warning', 'icon' => 'fa-exclamation-triangle'],
        'critical' => ['label' => '异常', 'color' => 'danger', 'icon' => 'fa-times-circle'],
    ];
@endphp

<div class="row">
    {{-- 左侧：健康度概览 --}}
    <div class="col-lg-4 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-4">
                <h6 class="text-muted mb-3">整体健康度</h6>
                {{-- 圆环进度 --}}
                <div class="position-relative d-inline-block mb-3">
                    @php
                        $scoreColor = $healthScore['status'] === 'healthy' ? 'success' : ($healthScore['status'] === 'warning' ? 'warning' : 'danger');
                    @endphp
                    <svg width="160" height="160" viewBox="0 0 160 160">
                        {{-- 背景圆环 --}}
                        <circle cx="80" cy="80" r="68" fill="none" stroke="#e9ecef" stroke-width="12"/>
                        {{-- 进度圆环 --}}
                        @php
                            $circumference = 2 * 3.14159 * 68;
                            $offset = $circumference - ($circumference * $healthScore['score'] / 100);
                        @endphp
                        <circle cx="80" cy="80" r="68" fill="none"
                                stroke="var(--bs-{{ $scoreColor }})" stroke-width="12"
                                stroke-dasharray="{{ $circumference }}"
                                stroke-dashoffset="{{ $offset }}"
                                stroke-linecap="round"
                                transform="rotate(-90 80 80)"
                                style="transition: stroke-dashoffset 1s ease;"/>
                    </svg>
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <div class="fw-bold text-{{ $scoreColor }}" style="font-size: 2rem;">{{ $healthScore['score'] }}</div>
                        <div class="text-muted small">分</div>
                    </div>
                </div>
                <div>
                    @if($healthScore['status'] === 'healthy')
                        <span class="badge bg-success px-3 py-2">
                            <i class="fa fa-check-circle me-1"></i>系统运行正常
                        </span>
                    @elseif($healthScore['status'] === 'warning')
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="fa fa-exclamation-triangle me-1"></i>存在警告
                        </span>
                    @else
                        <span class="badge bg-danger px-3 py-2">
                            <i class="fa fa-times-circle me-1"></i>存在异常
                        </span>
                    @endif
                </div>

                {{-- 统计 --}}
                <div class="row mt-4 text-center">
                    <div class="col-4">
                        <div class="fw-bold text-success">{{ $healthScore['healthy'] }}</div>
                        <div class="text-muted small">正常</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-warning">{{ $healthScore['warning'] }}</div>
                        <div class="text-muted small">警告</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-danger">{{ $healthScore['critical'] }}</div>
                        <div class="text-muted small">异常</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 右侧：检查项列表 --}}
    <div class="col-lg-8 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fa fa-stethoscope me-2 text-primary"></i>检查项详情</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($checks as $key => $check)
                        @php
                            $cfg = $statusConfig[$check['status']] ?? $statusConfig['warning'];
                        @endphp
                        <div class="list-group-item px-3 py-3">
                            <div class="d-flex align-items-start">
                                {{-- 状态图标 --}}
                                <div class="me-3">
                                    <i class="fa {{ $cfg['icon'] }} text-{{ $cfg['color'] }}" style="font-size: 20px;"></i>
                                </div>
                                {{-- 内容 --}}
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="fw-semibold">{{ $check['name'] }}</div>
                                        <span class="badge bg-{{ $cfg['color'] }} {{ $check['status'] === 'warning' ? 'text-dark' : '' }}">{{ $cfg['label'] }}</span>
                                    </div>
                                    <div class="small text-muted mt-1">{{ $check['message'] }}</div>
                                    @if(!empty($check['detail']))
                                        <details class="mt-1">
                                            <summary class="small text-muted cursor-pointer">详细信息</summary>
                                            <code class="small d-block mt-1 p-2 bg-light rounded text-break">{{ $check['detail'] }}</code>
                                        </details>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 系统资源 --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fa fa-microchip me-2 text-primary"></i>系统资源</h6>
    </div>
    <div class="card-body">
        <div class="row">
            {{-- 磁盘使用 --}}
            <div class="col-md-6 mb-3">
                <div class="border rounded-3 p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold small"><i class="fa fa-hdd me-1"></i>磁盘使用</div>
                        <span class="badge bg-{{ $resources['disk_usage_percent'] > 90 ? 'danger' : ($resources['disk_usage_percent'] > 70 ? 'warning' : 'success') }}">
                            {{ $resources['disk_usage_percent'] }}%
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-{{ $resources['disk_usage_percent'] > 90 ? 'danger' : ($resources['disk_usage_percent'] > 70 ? 'warning' : 'success') }}"
                             style="width: {{ $resources['disk_usage_percent'] }}%"></div>
                    </div>
                    <div class="small text-muted">
                        已用 {{ $resources['disk_used'] }} / 总计 {{ $resources['disk_total'] }}
                        <span class="text-success">（剩余 {{ $resources['disk_free'] }}）</span>
                    </div>
                </div>
            </div>

            {{-- 内存使用 --}}
            <div class="col-md-6 mb-3">
                <div class="border rounded-3 p-3 h-100">
                    <div class="fw-semibold small mb-2"><i class="fa fa-memory me-1"></i>内存使用</div>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted small ps-0" style="width: 50%;">内存限制</td>
                            <td class="fw-semibold small">{{ $resources['memory_limit'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small ps-0">当前使用</td>
                            <td class="fw-semibold small">{{ $resources['memory_usage'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small ps-0">峰值使用</td>
                            <td class="fw-semibold small">{{ $resources['memory_peak'] }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
