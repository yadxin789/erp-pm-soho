@extends('core::layouts.base')

@section('title', strtoupper($module).' 菜单 · '.config('app.name'))

@section('sidebar')
    @include('core::partials.menu', ['module' => $module])
@endsection

@section('content')
<div class="dw-page">
    <div class="dw-panel">
        <div class="dw-panel-head">
            <div class="left">
                <span class="ic"><i class="fas fa-bars" aria-hidden="true"></i></span>
                <h2>{{ strtoupper($module) }} 模块菜单
                    <span class="sub">基线 {{ $total }} 项 · 当前角色可见 {{ $visibleCount }} 项 · 因权限隐藏 {{ $hiddenCount }} 项 · 路由未迁入 {{ count($pending) }} 项</span>
                </h2>
            </div>
            <div class="tools">
                @foreach (['erp','pm','soho'] as $m)
                    <a class="dw-chip {{ $m === $module ? 'is-current' : '' }}" href="{{ url('/core/menu/'.$m) }}">{{ strtoupper($m) }}</a>
                @endforeach
            </div>
        </div>
    </div>

    @if (count($pending) > 0)
        <section class="dw-card dw-alert" role="status">
            <h2 class="dw-card-title">以下菜单项的路由尚未迁入本应用</h2>
            <p class="dw-hint">菜单结构与权限已就位，页面本体要等对应模块任务（B-02 ~ B-04）迁入后才能点开。此时侧边栏会把它们渲染成不可点状态，而不是 500。</p>
            <ul class="dw-plain-list">
                @foreach ($pending as $item)
                    <li><code>{{ $item['route'] }}</code> — {{ $item['label'] }}（原：<code>{{ $item['source'] ?? '-' }}</code>）</li>
                @endforeach
            </ul>
        </section>
    @else
        <section class="dw-card dw-alert dw-alert-ok" role="status">
            <h2 class="dw-card-title">全部菜单项的路由都已注册</h2>
            <p class="dw-hint">{{ $visibleCount }} 个链接均可直接点开。</p>
        </section>
    @endif

    <section class="dw-card">
        <h2 class="dw-card-title">按分组预览</h2>
        <div class="dw-grid-2">
            @foreach ($groups as $group)
                <div class="dw-card dw-card-flat">
                    <h3 class="dw-card-subtitle">{{ $group['group'] }} <span class="dw-badge">{{ count($group['items']) }}</span></h3>
                    <ul class="dw-plain-list">
                        @foreach ($group['items'] as $item)
                            <li>
                                <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                                {{ $item['label'] }}
                                <span class="dw-hint"><code>{{ $item['route'] }}</code></span>
                                @if (! empty($item['permission']))
                                    <span class="dw-badge dw-badge-soft">{{ $item['permission'] }}</span>
                                @endif
                                @if (is_array($item['roles'] ?? null) && count($item['roles']) > 0)
                                    <span class="dw-badge dw-badge-soft">角色白名单 {{ count($item['roles']) }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
