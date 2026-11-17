@php
    $stripUser = auth()->user();
    $stripCounts = $stripUser ? app(\App\Core\Services\NotificationService::class)->unreadByModule($stripUser) : ['total' => 0];
    $rawTitle = trim($__env->yieldContent('title'));
    $stripTitle = trim(explode('·', $rawTitle)[0] ?: $rawTitle);
@endphp
<div class="dw-strip">
    <span class="dw-strip-title">{{ $stripTitle }}</span>
    <div class="dw-strip-actions">
        <a href="{{ route('core.notifications.index') }}" class="dw-topbar-bell" aria-label="站内通知">
            <i class="fas fa-bell" aria-hidden="true"></i>
            @if ($stripCounts['total'] > 0)
                <span class="dw-topbar-badge">{{ $stripCounts['total'] > 99 ? '99+' : $stripCounts['total'] }}</span>
            @endif
        </a>
        <a class="dw-strip-user" href="{{ route('core.profile') }}">
            <span class="dw-strip-avatar" aria-hidden="true">{{ mb_substr($stripUser?->displayName() ?? '游', 0, 1) }}</span>
            <span>{{ $stripUser?->displayName() ?? '未登录' }}<small>{{ strtoupper(session('active_module') ?: ($stripUser?->module ?: 'core')) }}</small></span>
        </a>
        <form method="POST" action="{{ route('core.logout') }}" class="dw-topbar-form">
            @csrf
            <button type="submit" class="dw-topbar-link">退出</button>
        </form>
    </div>
</div>
