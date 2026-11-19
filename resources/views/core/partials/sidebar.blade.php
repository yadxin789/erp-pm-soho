@php
    $u = auth()->user();
    $nav = [
        ['group' => '工作台', 'items' => [
            ['route' => 'core.home', 'label' => '首页', 'icon' => 'fa-home', 'match' => 'core.home'],
        ]],
        ['group' => '协作', 'items' => [
            ['route' => 'core.todos.index', 'label' => '我的待办', 'icon' => 'fa-list-check', 'match' => 'core.todos.*'],
            ['route' => 'core.approvals.index', 'label' => '审批', 'icon' => 'fa-check-circle', 'match' => 'core.approvals.*'],
            ['route' => 'core.files.index', 'label' => '文件', 'icon' => 'fa-folder-open', 'match' => 'core.files.*'],
            ['route' => 'core.chat.index', 'label' => '聊天', 'icon' => 'fa-comments', 'match' => 'core.chat.*'],
            ['route' => 'core.voice.index', 'label' => '语音会议', 'icon' => 'fa-phone', 'match' => 'core.voice.*'],
        ]],
        ['group' => '账号', 'items' => [
            ['route' => 'core.notifications.index', 'label' => '通知', 'icon' => 'fa-bell', 'match' => 'core.notifications.*'],
            ['route' => 'core.profile', 'label' => '个人中心', 'icon' => 'fa-id-card', 'match' => 'core.profile*'],
        ]],
    ];
@endphp
<aside class="dw-nav" aria-label="主导航">
    <a class="dw-nav-brand" href="{{ route('core.home') }}">
        DDWL 管理中枢
        <small>ERP · PM · SOHO 共用一套</small>
    </a>

    @foreach ($nav as $section)
        <div class="dw-nav-group">{{ $section['group'] }}</div>
        @foreach ($section['items'] as $item)
            @php $active = request()->routeIs($item['match']); @endphp
            <a class="dw-nav-item {{ $active ? 'active' : '' }}" href="{{ route($item['route']) }}"
               @if ($active) aria-current="page" @endif>
                <i class="fas {{ $item['icon'] }}" aria-hidden="true"></i>{{ $item['label'] }}
            </a>
        @endforeach
    @endforeach

    <div class="dw-nav-note">当前模块：{{ strtoupper(session('active_module') ?: ($u?->module ?: 'core')) }}</div>
</aside>
