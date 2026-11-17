@php
    $section = 'erp-nav-section';
    $item = fn (bool $active) => $active ? 'active' : '';
@endphp

<div class="{{ $section }} mt-0">系统管理</div>
@if(\App\Erp\Support\Menu::can('admin.dashboard'))
<a href="{{ erp_route('admin.dashboard') }}" class="{{ $item(request()->routeIs('admin.dashboard')) }}">
    <i class="fa-solid fa-gauge-high"></i>系统概览
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.users.index'))
<a href="{{ erp_route('admin.users.index') }}" class="{{ $item(request()->routeIs('admin.users.*')) }}">
    <i class="fa-solid fa-users"></i>用户管理
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.roles.index'))
<a href="{{ erp_route('admin.roles.index') }}" class="{{ $item(request()->routeIs('admin.roles.*')) }}">
    <i class="fa-solid fa-user-shield"></i>角色管理
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.permissions.index'))
<a href="{{ erp_route('admin.permissions.index') }}" class="{{ $item(request()->routeIs('admin.permissions.*')) }}">
    <i class="fa-solid fa-key"></i>权限管理
</a>
@endif

<div class="{{ $section }}">组织架构</div>
@if(\App\Erp\Support\Menu::can('admin.departments.index'))
<a href="{{ erp_route('admin.departments.index') }}" class="{{ $item(request()->routeIs('admin.departments.*')) }}">
    <i class="fa-solid fa-sitemap"></i>部门管理
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.positions.index'))
<a href="{{ erp_route('admin.positions.index') }}" class="{{ $item(request()->routeIs('admin.positions.*')) }}">
    <i class="fa-solid fa-id-badge"></i>岗位管理
</a>
@endif

<div class="{{ $section }}">主数据维护</div>
@if(\App\Erp\Support\Menu::can('admin.items.index'))
<a href="{{ erp_route('admin.items.index') }}" class="{{ $item(request()->routeIs('admin.items.*')) }}">
    <i class="fa-solid fa-boxes-stacked"></i>物料管理
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.customers.index'))
<a href="{{ erp_route('admin.customers.index') }}" class="{{ $item(request()->routeIs('admin.customers.*')) }}">
    <i class="fa-solid fa-users"></i>客户管理
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.suppliers.index'))
<a href="{{ erp_route('admin.suppliers.index') }}" class="{{ $item(request()->routeIs('admin.suppliers.*')) }}">
    <i class="fa-solid fa-truck-field"></i>供应商管理
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.warehouses.index'))
<a href="{{ erp_route('admin.warehouses.index') }}" class="{{ $item(request()->routeIs('admin.warehouses.*')) }}">
    <i class="fa-solid fa-warehouse"></i>仓库管理
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.boms.index'))
<a href="{{ erp_route('admin.boms.index') }}" class="{{ $item(request()->routeIs('admin.boms.*')) }}">
    <i class="fa-solid fa-diagram-project"></i>BOM 管理
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.routings.index'))
<a href="{{ erp_route('admin.routings.index') }}" class="{{ $item(request()->routeIs('admin.routings.*')) }}">
    <i class="fa-solid fa-route"></i>工艺路线
</a>
@endif

<div class="{{ $section }}">运维监控</div>
@if(\App\Erp\Support\Menu::can('admin.monitor.system-status'))
<a href="{{ erp_route('admin.monitor.system-status') }}" class="{{ $item(request()->routeIs('admin.monitor.system-status*')) }}">
    <i class="fa-solid fa-heart-pulse"></i>系统工作状态
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.monitor.scheduled-tasks'))
<a href="{{ erp_route('admin.monitor.scheduled-tasks') }}" class="{{ $item(request()->routeIs('admin.monitor.scheduled-tasks*')) }}">
    <i class="fa-regular fa-clock"></i>定时任务
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.monitor.queue'))
<a href="{{ erp_route('admin.monitor.queue') }}" class="{{ $item(request()->routeIs('admin.monitor.queue*')) }}">
    <i class="fa-solid fa-layer-group"></i>队列监控
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.monitor.auto-start'))
<a href="{{ erp_route('admin.monitor.auto-start') }}" class="{{ $item(request()->routeIs('admin.monitor.auto-start*')) }}">
    <i class="fa-solid fa-rocket"></i>自启动程序
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.system-health'))
<a href="{{ erp_route('admin.system-health') }}" class="{{ $item(request()->routeIs('admin.system-health')) }}">
    <i class="fa-solid fa-bell"></i>告警与备份
</a>
@endif

<div class="{{ $section }}">系统设置</div>
@if(\App\Erp\Support\Menu::can('admin.settings.index'))
<a href="{{ erp_route('admin.settings.index') }}" class="{{ $item(request()->routeIs('admin.settings.*')) }}">
    <i class="fa-solid fa-gear"></i>系统设置
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.ai-settings.index'))
<a href="{{ erp_route('admin.ai-settings.index') }}" class="{{ $item(request()->routeIs('admin.ai-settings.*')) }}">
    <i class="fa-solid fa-robot"></i>AI 智能助手
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.logistics.settings'))
<a href="{{ erp_route('admin.logistics.settings') }}" class="{{ $item(request()->routeIs('admin.logistics.*')) }}">
    <i class="fa-solid fa-truck-fast"></i>物流快递
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.approval-flows.index'))
<a href="{{ erp_route('admin.approval-flows.index') }}" class="{{ $item(request()->routeIs('admin.approval-flows.*')) }}">
    <i class="fa-solid fa-list-check"></i>审批流配置
</a>
@endif
@if(\App\Erp\Support\Menu::can('admin.audit-logs.index'))
<a href="{{ erp_route('admin.audit-logs.index') }}" class="{{ $item(request()->routeIs('admin.audit-logs.*')) }}">
    <i class="fa-solid fa-clipboard-list"></i>操作日志
</a>
@endif
{{-- 管理员只做后台管理与系统维护，不提供业务系统与 AI 聊天入口 --}}
