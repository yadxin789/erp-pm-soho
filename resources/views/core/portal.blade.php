@extends('core::layouts.base')

@section('title', '统一入口 · ' . config('app.name'))

@push('styles')
<style>
    .dw-portal-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 4px; }
    .dw-portal-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; margin-top: 22px; }
    @media (max-width: 900px) { .dw-portal-grid { grid-template-columns: 1fr; } }

    .dw-portal-card { display: flex; flex-direction: column; gap: 12px; padding: 24px 22px 20px;
        background: #fff; border: 1px solid var(--dw-line); border-radius: var(--dw-radius);
        color: inherit; text-decoration: none; height: 100%;
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
    .dw-portal-card:hover, .dw-portal-card:focus-visible { transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(47,111,237,.12); border-color: #cfd9f5; }
    @media (prefers-reduced-motion: reduce) { .dw-portal-card { transition: none; } .dw-portal-card:hover { transform: none; } }

    .dw-portal-card__icon { width: 44px; height: 44px; border-radius: 10px; display: grid; place-items: center; }
    .dw-portal-card__icon svg { width: 22px; height: 22px; stroke: currentColor; stroke-width: 1.7; fill: none; }
    .dw-portal-card--erp .dw-portal-card__icon { background: #eaf1ff; color: var(--dw-brand); }
    .dw-portal-card--pm .dw-portal-card__icon { background: #eaf7ef; color: var(--dw-ok); }
    .dw-portal-card--soho .dw-portal-card__icon { background: #fff5e0; color: #b78103; }

    .dw-portal-card__name { font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .dw-portal-card__desc { color: var(--dw-muted); font-size: 13px; line-height: 1.75; }
    .dw-portal-card__cta { margin-top: auto; font-size: 13px; font-weight: 600; color: var(--dw-brand); display: inline-flex; align-items: center; gap: 6px; }
    .dw-portal-card__cta::after { content: "→"; transition: transform .16s ease; }
    .dw-portal-card:hover .dw-portal-card__cta::after { transform: translateX(3px); }
</style>
@endpush

@section('content')
@if (session('warning'))
    <div class="dw-card dw-alert" role="alert" style="margin-bottom:18px">
        <p class="dw-hint" style="margin:0">{{ session('warning') }}</p>
    </div>
@endif
<div class="dw-portal-head">
    <div>
        <h1 class="dw-page-title">选择进入的管理板块</h1>
        <p class="dw-page-sub mb-0">三个板块共用一套账号与组织架构；登录后按你的部门归属自动进入对应板块。</p>
    </div>
    @if ($user)
        <div class="d-flex align-items-center gap-3">
            <a class="text-muted small" href="/profile">{{ $user->name ?: $user->username ?: $user->email }}</a>
            <span class="dw-badge dw-badge--muted">个人中心</span>
            <form method="POST" action="/logout">
                @csrf
                <button class="btn btn-sm btn-outline-secondary" type="submit">退出登录</button>
            </form>
        </div>
    @else
        <a class="btn btn-sm btn-primary" href="/login">登录</a>
    @endif
</div>

<div class="dw-portal-grid">
    @foreach ($cards as $card)
        @continue(! $card['allowed'])
        <a class="dw-portal-card dw-portal-card--{{ $card['module'] }}" href="{{ $card['href'] }}">
            <span class="dw-portal-card__icon" aria-hidden="true">
                @if ($card['module'] === 'erp')
                    <svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20h14V9.5"/><path d="M9.5 20v-5.5h5V20"/></svg>
                @elseif ($card['module'] === 'pm')
                    <svg viewBox="0 0 24 24"><rect x="3.5" y="4.5" width="17" height="16" rx="2.5"/><path d="M8 3v3M16 3v3M3.5 9.5h17"/><path d="M8 14h3M8 17h5"/></svg>
                @else
                    <svg viewBox="0 0 24 24"><path d="M4 8.5h16l-1.2 11.5H5.2L4 8.5Z"/><path d="M9 8.5V6a3 3 0 0 1 6 0v2.5"/></svg>
                @endif
            </span>
            <span class="dw-portal-card__name">
                {{ $card['name'] }}
                @if ($card['is_last'])
                    <span class="dw-badge dw-badge--info">上次进入</span>
                @endif
            </span>
            <span class="dw-portal-card__desc">{{ $card['desc'] }}</span>
            <span class="dw-portal-card__cta">{{ $card['cta'] }}</span>
        </a>
    @endforeach
</div>

@if (collect($cards)->where('allowed', false)->isNotEmpty())
    <p class="text-muted small mt-4 mb-0">
        其余板块未对该账号开通，入口已隐藏；如需使用请联系管理员。
    </p>
@endif

<p class="text-muted small mt-4 mb-0">
    @if ($user)
        当前账号可进入：{{ implode('、', array_map(fn ($m) => strtoupper($m), array_diff($user->resolveModules(), ['core'])) ?: ['（暂未分配业务模块，请联系管理员）']) }}
    @else
        未登录时点击卡片会先跳转登录，登录成功后直达对应板块。
    @endif
</p>
@endsection
