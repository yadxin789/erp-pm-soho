@php
    $u = auth()->user();
    $count = app(\App\Core\Services\NotificationService::class)->unreadByModule($u)["total"] ?? 0;
@endphp
<div class="dw-strip">
    <span class="dw-strip-title">@yield("title")</span>
    <div class="dw-strip-actions">
        <a href="{{ route("core.notifications.index") }}" class="dw-topbar-bell" aria-label="站内通知">
            <i class="fas fa-bell" aria-hidden="true"></i>
            @if ($count > 0)<span class="dw-topbar-badge">{{ $count > 99 ? "99+" : $count }}</span>@endif
        </a>
        <a class="dw-strip-user" href="{{ route("core.profile") }}">
            <span class="dw-strip-avatar" aria-hidden="true">{{ mb_substr($u?->displayName() ?? "游", 0, 1) }}</span>
            <span>{{ $u?->displayName() }}<small>PM</small></span>
        </a>
        <form method="POST" action="{{ route("core.logout") }}" class="dw-topbar-form">@csrf
            <button type="submit" class="dw-topbar-link">退出</button>
        </form>
    </div>
</div>