@extends('core::layouts.base')

@section('title', '统一入口 · DDWL 管理中枢')

@push('styles')
<style>
    .dw-portal-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    @media (max-width: 980px) { .dw-portal-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .dw-portal-grid { grid-template-columns: 1fr; } }

    .dw-portal-card { display: flex; flex-direction: column; gap: 10px; padding: 20px 20px 16px;
        background: #fff; border: 1px solid var(--dw-line); border-radius: 8px;
        color: inherit; text-decoration: none; height: 100%;
        transition: box-shadow .16s ease, border-color .16s ease; }
    .dw-portal-card:hover, .dw-portal-card:focus-visible { border-color: #c7d2fe; box-shadow: 0 6px 18px rgba(47,111,237,.10); }
    @media (prefers-reduced-motion: reduce) { .dw-portal-card { transition: none; } }

    .dw-portal-card__icon { width: 42px; height: 42px; border-radius: 8px; display: grid; place-items: center; font-size: 19px; }
    .dw-portal-card--erp .dw-portal-card__icon { background: #eef3ff; color: #2f6fed; }
    .dw-portal-card--pm .dw-portal-card__icon { background: #e9f7ef; color: #1c9d56; }
    .dw-portal-card--soho .dw-portal-card__icon { background: #fff4e2; color: #d18a00; }

    .dw-portal-card__name { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .dw-portal-card__desc { color: var(--dw-muted); font-size: 13px; line-height: 1.75; }
    .dw-portal-card__cta { margin-top: auto; font-size: 13px; font-weight: 600; color: var(--dw-brand); }
</style>
@endpush

@section('content')
@if (session('warning'))
    <div class="dw-panel"><div class="dw-panel-body" role="alert" style="border-left:3px solid var(--dw-warn)">
        <p class="dw-hint mb-0">{{ session('warning') }}</p>
    </div></div>
@endif

<div class="dw-panel">
    <div class="dw-panel-head">
        <h2><i class="fas fa-th-large me-2 text-primary" aria-hidden="true"></i>选择进入的管理板块</h2>
        <span class="dw-hint">三个板块共用一套账号与组织架构</span>
    </div>
    <div class="dw-panel-body">
        <div class="dw-portal-grid">
            @foreach ($cards as $card)
                @continue(! $card['allowed'])
                <a class="dw-portal-card dw-portal-card--{{ $card['module'] }}" href="{{ $card['href'] }}">
                    <span class="dw-portal-card__icon" aria-hidden="true">
                        <i class="fas {{ $card['module'] === 'erp' ? 'fa-industry' : ($card['module'] === 'pm' ? 'fa-cubes' : 'fa-shopping-cart') }}"></i>
                    </span>
                    <span class="dw-portal-card__name">
                        {{ $card['name'] }}
                        @if ($card['is_last'])
                            <span class="dw-badge dw-badge--info">上次进入</span>
                        @endif
                    </span>
                    <span class="dw-portal-card__desc">{{ $card['desc'] }}</span>
                    <span class="dw-portal-card__cta">进入 {{ strtoupper($card['module']) }} →</span>
                </a>
            @endforeach
        </div>

        @if (collect($cards)->where('allowed', false)->isNotEmpty())
            <p class="dw-hint mt-3 mb-0">其余板块未对该账号开通，入口已隐藏；如需使用请联系管理员。</p>
        @endif
    </div>
</div>
@endsection
