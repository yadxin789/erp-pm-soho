@extends('soho::layouts.app')

@section('title', '设备管理')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-laptop me-2"></i>设备管理</h4>
        <p class="text-muted mb-0 small">查看用户登录设备，支持强制下线</p>
    </div>
</div>

{{-- 统计卡片 --}}
<div class="row mb-4">
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-primary" style="font-size: 1.6rem;">{{ $stats['total_devices'] }}</div>
                <div class="text-muted small">登录设备</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-success" style="font-size: 1.6rem;">{{ $stats['active_devices'] }}</div>
                <div class="text-muted small">在线设备</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-info" style="font-size: 1.6rem;">{{ $stats['users_count'] }}</div>
                <div class="text-muted small">关联用户</div>
            </div>
        </div>
    </div>
</div>

{{-- 设备列表 --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fa fa-desktop me-2 text-primary"></i>设备列表</h6>
    </div>
    <div class="card-body p-0">
        @if($devices->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-laptop fa-3x text-muted mb-3"></i>
                <p class="text-muted">暂无登录设备</p>
            </div>
        @else
            <div class="row g-0 p-3">
                @foreach($devices as $device)
                    <div class="col-md-6 col-xl-4 mb-3">
                        <div class="border rounded-3 p-3 h-100 {{ $device->is_current ? 'border-primary' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fa {{ $device->os == 'iOS' || $device->os == 'Android' ? 'fa-mobile-alt' : 'fa-desktop' }} fa-lg me-2 text-primary"></i>
                                    <div>
                                        <div class="fw-semibold">{{ $device->device_name }}</div>
                                        <div class="small text-muted">
                                            @if($device->user_id)
                                                {{ $device->user_name }}
                                            @else
                                                访客
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($device->is_current)
                                    <span class="badge bg-primary">当前</span>
                                @elseif($device->is_online)
                                    <span class="badge bg-success">在线</span>
                                @else
                                    <span class="badge bg-secondary">离线</span>
                                @endif
                            </div>
                            <div class="small text-muted mb-2">
                                <i class="fa fa-map-marker-alt me-1"></i>{{ $device->ip_address }}
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">
                                    {{ $device->last_activity_at->diffForHumans() }}
                                </span>
                                @if(!$device->is_current)
                                    <form method="POST" action="{{ route('soho.security.devices.destroy', $device->id) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('确定下线该设备？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-sign-out me-1"></i>下线
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="card-footer bg-white">
                <div class="small text-muted">共 {{ $devices->count() }} 个设备</div>
            </div>
        @endif
    </div>
</div>
@endsection