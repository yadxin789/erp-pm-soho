<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '产品研发管理系统')</title>
    <link rel="stylesheet" href="{{ asset('assets/core/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/core/font-awesome/css/fontawesome-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/core/css/ddwl.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/pm/css/pm.css') }}">
    @stack('styles')
</head>
<body>
<div class="dw-app">
    <aside class="dw-nav" aria-label="PM 主导航">
        <a class="dw-nav-brand" href="{{ url('/pm/project') }}">产品研发<br>管理系统
            <small>研发全生命周期管理</small>
        </a>
        @php $u = auth()->user(); @endphp
        <div class="dw-nav-group">工作台</div>
        <a class="dw-nav-item {{ request()->routeIs('pm.project.*') ? 'active' : '' }}" href="{{ route('pm.project.index') }}">
            <i class="fas fa-home" aria-hidden="true"></i>首页
        </a>
        <div class="dw-nav-group">研发项目</div>
        <a class="dw-nav-item {{ request()->routeIs('pm.project.index') ? 'active' : '' }}" href="{{ route('pm.project.index') }}">
            <i class="fas fa-folder-open" aria-hidden="true"></i>项目列表
        </a>
        @if (isset($project))
            <a class="dw-nav-item {{ request()->routeIs('pm.project.collab.*') ? 'active' : '' }}" href="{{ route('pm.project.collab.index', $project) }}">
                <i class="fas fa-users" aria-hidden="true"></i>协同办公
            </a>
            <a class="dw-nav-item {{ request()->routeIs('pm.project.messages.*') ? 'active' : '' }}" href="{{ route('pm.project.messages.index', $project->id) }}">
                <i class="fas fa-comments" aria-hidden="true"></i>聊天室
            </a>
            @if ($project->isMember($u))
                <a class="dw-nav-item {{ request()->routeIs('pm.project.voice.*') ? 'active' : '' }}" href="{{ route('pm.project.voice.index', $project) }}">
                    <i class="fas fa-phone" aria-hidden="true"></i>电话会议
                </a>
            @endif
        @endif
        <div class="dw-nav-group">协作</div>
        <a class="dw-nav-item {{ request()->routeIs('core.approvals.*') ? 'active' : '' }}" href="{{ route('core.approvals.index') }}">
            <i class="fas fa-check-circle" aria-hidden="true"></i>审批
        </a>
        <a class="dw-nav-item {{ request()->routeIs('core.files.*') ? 'active' : '' }}" href="{{ route('core.files.index') }}">
            <i class="fas fa-folder" aria-hidden="true"></i>文件仓库
        </a>
        <a class="dw-nav-item {{ request()->routeIs('core.chat.*') ? 'active' : '' }}" href="{{ route('core.chat.index') }}">
            <i class="fas fa-comments" aria-hidden="true"></i>全局聊天
        </a>
        <div class="dw-nav-group">账号</div>
        <a class="dw-nav-item {{ request()->routeIs('core.notifications.*') ? 'active' : '' }}" href="{{ route('core.notifications.index') }}">
            <i class="fas fa-bell" aria-hidden="true"></i>通知
        </a>
        <a class="dw-nav-item {{ request()->routeIs('core.profile*') ? 'active' : '' }}" href="{{ route('core.profile') }}">
            <i class="fas fa-id-card" aria-hidden="true"></i>个人中心
        </a>
        <div class="dw-nav-note">当前模块：PM</div>
    </aside>

    <div class="dw-body">
        @include('pm::partials.topstrip')
        <main class="dw-content">
            @if (session('status'))
                <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning py-2" role="alert">{{ session('warning') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<span id="dw-live" class="dw-sr" role="status" aria-live="polite"></span>
<script src="{{ asset('assets/core/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/core/js/ddwl.js') }}"></script>
@stack('scripts')
</body>
</html>
