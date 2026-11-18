@extends('soho::layouts.app')

@section('title', '账号锁定记录')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-lock me-2"></i>账号锁定记录</h4>
        <p class="text-muted mb-0 small">查看账号锁定历史，支持手动解锁操作</p>
    </div>
</div>

{{-- 统计卡片 --}}
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-primary" style="font-size: 1.6rem;">{{ $stats['total'] }}</div>
                <div class="text-muted small">锁定总记录</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-danger" style="font-size: 1.6rem;">{{ $stats['active'] }}</div>
                <div class="text-muted small">当前锁定中</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-success" style="font-size: 1.6rem;">{{ $stats['unlocked'] }}</div>
                <div class="text-muted small">已解锁</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="fw-bold text-warning" style="font-size: 1.6rem;">{{ $stats['permanent'] }}</div>
                <div class="text-muted small">永久锁定</div>
            </div>
        </div>
    </div>
</div>

{{-- 列表 --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fa fa-history me-2 text-primary"></i>锁定记录</h6>
    </div>
    <div class="card-body p-0">
        @if($lockouts->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-unlock-alt fa-3x text-success mb-3"></i>
                <p class="text-muted">暂无账号锁定记录</p>
            </div>
        @else
            @php
                $lockTypeColors = \App\Soho\Models\AccountLockout::getLockTypeColors();
            @endphp
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>用户</th>
                            <th>锁定类型</th>
                            <th>锁定时间</th>
                            <th>时长</th>
                            <th>状态</th>
                            <th>解锁信息</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lockouts as $lockout)
                            <tr>
                                <td class="text-muted">{{ $lockout->id }}</td>
                                <td>
                                    @if($lockout->user)
                                        <div class="fw-semibold">{{ $lockout->user->name }}</div>
                                        <div class="small text-muted">{{ $lockout->user->email }}</div>
                                    @else
                                        <span class="text-muted">用户已删除</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $lockTypeColors[$lockout->lock_type] ?? 'secondary' }}">
                                        {{ $lockTypes[$lockout->lock_type] ?? $lockout->lock_type }}
                                    </span>
                                </td>
                                <td class="small">{{ $lockout->locked_at->format('Y-m-d H:i') }}</td>
                                <td class="small">
                                    @if($lockout->isPermanent())
                                        <span class="text-danger">永久</span>
                                    @else
                                        {{ $lockout->lock_duration }} 分钟
                                    @endif
                                </td>
                                <td>
                                    @if($lockout->isActive())
                                        <span class="badge bg-danger">
                                            <i class="fa fa-lock me-1"></i>锁定中
                                        </span>
                                    @elseif($lockout->isUnlocked())
                                        <span class="badge bg-success">
                                            <i class="fa fa-unlock me-1"></i>已解锁
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">已过期</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($lockout->isUnlocked())
                                        @if($lockout->unlocker)
                                            <div>{{ $lockout->unlocker->name }}</div>
                                        @endif
                                        <div class="text-muted">{{ $lockout->unlocked_at->format('m-d H:i') }}</div>
                                    @elseif($lockout->isActive())
                                        <form method="POST" action="{{ route('soho.security.lockouts.unlock', $lockout) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('确定解锁该账号？')">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="fa fa-unlock me-1"></i>解锁
                                            </button>
                                        </form>
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
                <div class="small text-muted">共 {{ $lockouts->count() }} 条记录</div>
            </div>
        @endif
    </div>
</div>
@endsection