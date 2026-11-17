{{-- 顶栏（TC-09 起用）：站内通知入口 + 未读角标 + 个人中心 --}}
@php
    $bellUser = auth()->user();
    $bellCounts = $bellUser ? app(\App\Core\Services\NotificationService::class)->unreadByModule($bellUser) : ['total' => 0];
@endphp

<div class="dw-topbar">
    <div class="dw-topbar-brand">
        <a href="{{ route('core.home') }}"><i class="fa-solid fa-cubes" aria-hidden="true"></i> {{ config('app.name') }}</a>
    </div>
    <nav class="dw-topbar-actions" aria-label="账号与通知">
        <a href="{{ route('core.approvals.index') }}" class="dw-topbar-link">
            <i class="fa-solid fa-stamp" aria-hidden="true"></i> 审批
        </a>
        <a href="{{ route('core.notifications.index') }}" class="dw-topbar-bell">
            <i class="fa-solid fa-bell" aria-hidden="true"></i>
            <span class="dw-sr">站内通知</span>
            @if ($bellCounts['total'] > 0)
                <span class="dw-topbar-badge" aria-label="未读 {{ $bellCounts['total'] }} 条">{{ $bellCounts['total'] > 99 ? '99+' : $bellCounts['total'] }}</span>
            @endif
        </a>
        <a href="{{ route('core.profile') }}" class="dw-topbar-link">
            <i class="fa-solid fa-id-card" aria-hidden="true"></i> {{ $bellUser?->displayName() ?? '个人中心' }}
        </a>
        <form method="POST" action="{{ route('core.logout') }}" class="dw-topbar-form">
            @csrf
            <button type="submit" class="dw-topbar-link">退出</button>
        </form>
    </nav>
</div>
