@php
    $section = 'erp-nav-section';
    $item = fn (bool $active) => $active ? 'active' : '';
@endphp

<div class="{{ $section }} mt-0">业务概览</div>
@if(\App\Erp\Support\Menu::can('app.dashboard'))
<a href="{{ erp_route('app.dashboard') }}" class="{{ $item(request()->routeIs('app.dashboard')) }}">
    <i class="fa-solid fa-gauge-high"></i>仪表盘
</a>
@endif

<div class="{{ $section }}">电商订单</div>
@if(\App\Erp\Support\Menu::can('app.ecommerce.index'))
<a href="{{ erp_route('app.ecommerce.index') }}" class="{{ $item(request()->routeIs('app.ecommerce.index')) }}">
    <i class="fa-solid fa-cart-shopping"></i>订单列表
</a>
@endif
@if(\App\Erp\Support\Menu::can('app.ecommerce.create'))
<a href="{{ erp_route('app.ecommerce.create') }}" class="{{ $item(request()->routeIs('app.ecommerce.create')) }}">
    <i class="fa-solid fa-circle-plus"></i>手工建单
</a>
@endif

<div class="{{ $section }}">智能助手</div>
@if(\App\Erp\Support\Menu::can('app.ai.chat'))
<a href="{{ erp_route('app.ai.chat') }}" class="{{ $item(request()->routeIs('app.ai.chat*')) }}">
    <i class="fa-solid fa-robot"></i>AI 聊天
</a>
@endif

<div class="{{ $section }}">协作沟通</div>
@if(\App\Erp\Support\Menu::can('app.chat'))
<a href="{{ erp_route('app.chat') }}" class="{{ $item(request()->routeIs('app.chat')) }}">
    <i class="fa-solid fa-comments"></i>群聊
</a>
@endif
@if(\App\Erp\Support\Menu::can('app.profile.edit'))
<a href="{{ erp_route('app.profile.edit') }}" class="{{ $item(request()->routeIs('app.profile.*')) }}">
    <i class="fa-solid fa-id-card"></i>个人资料
</a>
@endif

<div class="{{ $section }}">基础信息</div>
@if(\App\Erp\Support\Menu::can('app.masterdata.items.index'))
<a href="{{ erp_route('app.masterdata.items.index') }}" class="{{ $item(request()->routeIs('app.masterdata.items.*')) }}">
    <i class="fa-solid fa-box"></i>物料信息
</a>
@endif
@if(\App\Erp\Support\Menu::can('app.masterdata.customers.index'))
<a href="{{ erp_route('app.masterdata.customers.index') }}" class="{{ $item(request()->routeIs('app.masterdata.customers.*')) }}">
    <i class="fa-solid fa-users"></i>客户信息
</a>
@endif

<div class="{{ $section }}">报表中心</div>
@if(\App\Erp\Support\Menu::can('app.reports.index'))
<a href="{{ erp_route('app.reports.index') }}" class="{{ $item(request()->routeIs('app.reports.*')) }}">
    <i class="fa-solid fa-chart-simple"></i>统计报表
</a>
@endif
