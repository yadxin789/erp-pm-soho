{{--
    统一侧边栏菜单（TC-08）
    用法：@include('core::partials.menu', ['module' => 'erp'])
    - 数据来自 MenuRepository::visible($module, auth()->user())
    - 路由未注册时（模块尚未迁入）降级为不可点，避免 500
--}}
@php
    use App\Core\Menu\MenuRepository;
    $menuModule = $module ?? 'core';
    $menuGroups = MenuRepository::grouped(MenuRepository::visible($menuModule, auth()->user()));
@endphp

<nav class="core-menu" aria-label="{{ strtoupper($menuModule) }} 菜单">
    @foreach ($menuGroups as $group)
        <div class="core-menu-group">{{ $group['group'] }}</div>
        <ul class="core-menu-list">
            @foreach ($group['items'] as $item)
                @php $exists = MenuRepository::routeExists($item['route']); @endphp
                <li>
                    @if ($exists)
                        <a href="{{ route($item['route']) }}"
                           class="core-menu-item {{ request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'active' : '' }}">
                            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @else
                        <span class="core-menu-item is-pending" title="路由 {{ $item['route'] }} 尚未迁入">
                            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $item['label'] }}</span>
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endforeach
</nav>
