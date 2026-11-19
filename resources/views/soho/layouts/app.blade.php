<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        // 获取当前用户的角色名称，用于页面标题
        $roleName = auth()->user() && auth()->user()->roles->first()
            ? auth()->user()->roles->first()->name
            : '';
        // 使用中文角色名生成标题：角色名 + "操作页面"
        $titleRoleName = $roleName ? \App\Soho\Support\MenuConfig::getRoleChineseName($roleName) : '';
        $pageTitle = $titleRoleName ? $titleRoleName . '操作页面' : '多平台电商店铺统一管理系统';
    @endphp
    <title>{{ $pageTitle }}</title>
    <link rel="stylesheet" href="{{ asset('assets/soho/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/soho/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/soho/css/style.css') }}">
    <style>
        body {
            background-color: #f5f7fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .active {
            background-color: rgba(102, 126, 234, 0.3);
            border-left: 3px solid #667eea;
        }
        .navbar {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .user-info {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
        }
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 0.75rem;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s ease;
        }
        .user-avatar:hover {
            transform: scale(1.1);
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-name {
            color: #333;
            font-size: 0.9rem;
        }
        .user-role {
            color: #666;
            font-size: 0.75rem;
        }
        /* 通知徽章样式 */
        .notification-icon {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f3f4f6;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #374151;
        }
        .notification-icon:hover {
            background: #e5e7eb;
            color: #667eea;
        }
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            border-radius: 9px;
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            line-height: 1;
        }
        /* 聊天室按钮样式 */
        .chat-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        .chat-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
        }
        /* 主菜单分组容器 */
        .menu-group {
            position: relative;
        }
        /* 主菜单标题 - 可悬停展开 */
        .menu-group-title {
            color: #888;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 0.5rem;
            transition: background-color 0.2s ease;
            /* 使用 flex 布局让菜单名、徽章、箭头水平排列 */
            display: flex;
            align-items: center;
        }
        .menu-group-title .menu-group-name {
            /* 菜单名占据剩余空间，把箭头推到最右 */
            flex: 1;
        }
        /* 子菜单数量徽章 */
        .menu-group-title .menu-badge {
            display: inline-block;
            min-width: 18px;
            height: 16px;
            line-height: 16px;
            text-align: center;
            padding: 0 5px;
            margin-right: 0.5rem;
            font-size: 0.65rem;
            font-weight: 600;
            color: #adb5bd;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            letter-spacing: 0;
        }
        /* 激活状态下徽章高亮 */
        .menu-group-title.has-active .menu-badge {
            color: #fff;
            background: rgba(102, 126, 234, 0.5);
        }
        .menu-group:first-child .menu-group-title {
            border-top: none;
            margin-top: 0;
        }
        .menu-group-title:hover {
            background-color: rgba(255, 255, 255, 0.06);
        }
        /* 展开/折叠切换按钮（v/^） */
        .menu-group-title .menu-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 18px;
            font-size: 0.7rem;
            font-weight: 700;
            color: #888;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            cursor: pointer;
            transition: color 0.2s ease, background 0.2s ease;
            user-select: none;
            line-height: 1;
            margin-left: 2px;
        }
        .menu-group-title .menu-toggle-btn:hover {
            color: #fff;
            background: rgba(102, 126, 234, 0.4);
        }
        /* 展开状态下切换按钮高亮 */
        .menu-group.expanded > .menu-group-title .menu-toggle-btn {
            color: #fff;
            background: rgba(102, 126, 234, 0.4);
        }
        /* 分组下存在当前激活菜单时，主菜单高亮提示（因默认折叠看不到激活项） */
        .menu-group-title.has-active {
            color: #fff;
        }
        .menu-group-title.has-active::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #667eea;
            margin-right: 0.5rem;
        }
        /* 子菜单容器 - 默认折叠 */
        .menu-subnav {
            list-style: none;
            padding-left: 0;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        /* 展开状态时显示子菜单（点击 v 触发） */
        .menu-group.expanded > .menu-subnav {
            max-height: 1500px;
        }
        /* 子菜单项样式 */
        .menu-subnav .nav-link {
            padding: 0.45rem 1rem 0.45rem 2rem;
            font-size: 0.85rem;
        }
        .content-wrapper {
            min-height: calc(100vh - 56px);
        }
    </style>
    @yield('head')
</head>
<body>
    @php
        $currentUser = auth()->user();
        $userName = $currentUser ? $currentUser->name : '未知';
        // 获取中文角色名用于显示
        $userRoleName = $currentUser && $currentUser->roles->first() ? $currentUser->roles->first()->name : '';
        $userRole = $userRoleName ? \App\Soho\Support\MenuConfig::getRoleChineseName($userRoleName) : '用户';
        // 用户头像URL（使用访问器自动处理默认/自定义头像路径）
        $userAvatarUrl = $currentUser ? $currentUser->avatar_url : null;
        // 计算未读通知数量（用于顶部徽章显示）
        $unreadNotificationCount = $currentUser ? \App\Soho\Models\Notification::where('user_type', get_class($currentUser))
            ->where('user_id', $currentUser->id)
            ->whereNull('read_at')
            ->count() : 0;
        // 获取当前用户可见的菜单分组
        $menuGroups = $currentUser ? \App\Soho\Support\MenuConfig::getUserMenuGroups($currentUser) : [];
    @endphp

    <div class="container-fluid">
        <div class="row">
            <!-- 侧边栏 - 根据角色动态生成 -->
            <nav class="col-md-2 d-none d-md-block sidebar" aria-label="主导航">
                <div class="sidebar-sticky">
                    <div class="p-4">
                        <h3 class="text-white text-center">电商管理系统</h3>
                        <p class="mb-0" style="color: #aaa;">多平台统一管理</p>
                    </div>
                    <ul class="nav flex-column" role="navigation">
                        @foreach($menuGroups as $groupName => $menus)
                            @php
                                // 检查当前分组下是否有激活的菜单项，用于主菜单高亮提示（默认折叠时看不到激活项）
                                $groupHasActive = false;
                                foreach ($menus as $menu) {
                                    if (request()->routeIs('soho.' . $menu['route']) || request()->routeIs('soho.' . $menu['route'] . '*')) {
                                        $groupHasActive = true;
                                        break;
                                    }
                                }
                            @endphp
                            <li class="menu-group {{ $groupHasActive ? 'expanded' : '' }}">
                                <!-- 主菜单标题（点击 v/^ 按钮展开/收起子菜单） -->
                                <div class="menu-group-title {{ $groupHasActive ? 'has-active' : '' }}">
                                    <span class="menu-group-name">{{ $groupName }}</span>
                                    {{-- 子菜单数量徽章 --}}
                                    <span class="menu-badge">{{ count($menus) }}</span>
                                    <span class="menu-toggle-btn" title="点击展开/收起子菜单">{{ $groupHasActive ? '^' : 'v' }}</span>
                                </div>
                                <!-- 子菜单列表 -->
                                <ul class="menu-subnav">
                                    @foreach($menus as $key => $menu)
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('soho.' . $menu['route']) || request()->routeIs('soho.' . $menu['route'] . '*') ? 'active' : '' }}" href="{{ soho_route($menu['route']) }}">
                                                <i class="fa {{ $menu['icon'] }} me-2"></i> {{ $menu['title'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </nav>

            <!-- 主内容区 -->
            <main role="main" class="col-md-10 ms-sm-auto px-4">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <!-- 移动端汉堡菜单按钮 -->
                        <button class="navbar-toggler d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="打开导航菜单">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                            <ul class="navbar-nav align-items-center">
                                <!-- 聊天室按钮 -->
                                <li class="nav-item me-3">
                                    <a href="{{ route('soho.chat.index') }}" class="chat-btn">
                                        <i class="fa fa-comments"></i>
                                        <span>聊天室</span>
                                    </a>
                                </li>
                                <!-- 通知图标带徽章 -->
                                <li class="nav-item me-3">
                                    <a href="{{ route('soho.notifications.index') }}" class="notification-icon" title="通知消息">
                                        <i class="fa fa-bell"></i>
                                        {{-- 徽章始终显示，包括0 --}}
                                        <span class="notification-badge">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                                    </a>
                                </li>
                                <!-- 用户信息 -->
                                <li class="nav-item user-info">
                                    <div class="user-avatar" title="点击更换头像" onclick="document.getElementById('avatarInput').click()">
                                        @if($userAvatarUrl)
                                            <img src="{{ $userAvatarUrl }}" alt="{{ $userName }}">
                                        @else
                                            <span>{{ mb_substr($userName, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="user-name">{{ $userName }}</div>
                                        <div class="user-role">{{ $userRole }}</div>
                                    </div>
                                </li>
                                <!-- 头像上传隐藏表单 -->
                                <li style="display:none;">
                                    <form id="avatarForm" method="POST" action="{{ route('soho.profile.avatar.update') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none;" onchange="document.getElementById('avatarForm').submit();">
                                    </form>
                                </li>
                                <li class="nav-item">
                                    <form method="POST" action="{{ route('core.logout') }}" style="display: inline;" onsubmit="return confirm('确定退出登录？')">
                                        @csrf
                                        <button type="submit" class="nav-link btn btn-link" style="border: none; background: none; padding: 0.5rem 1rem;">
                                            <i class="fa fa-sign-out"></i> 退出
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>

                <div class="content-wrapper py-3">
                    @if(session('success'))
                        <div class="alert alert-success mt-3" id="success-alert">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger mt-3" id="error-alert">{{ session('error') }}</div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning mt-3" id="warning-alert">{{ session('warning') }}</div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info mt-3" id="info-alert">{{ session('info') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- 移动端 Offcanvas 侧边栏 -->
    <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" style="background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: #fff; max-width: 280px;">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title text-white" id="mobileSidebarLabel">电商管理系统</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="关闭"></button>
        </div>
        <div class="offcanvas-body p-0">
            <ul class="nav flex-column" style="font-size: 14px;">
                @foreach($menuGroups as $groupName => $menus)
                    <li class="menu-group-title" style="color: #888; font-size: 0.75rem; padding: 1rem 1rem 0.5rem; text-transform: uppercase; display: flex; align-items: center;">
                        <span style="flex: 1;">{{ $groupName }}</span>
                        <span class="menu-badge">{{ count($menus) }}</span>
                    </li>
                    @foreach($menus as $key => $menu)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('soho.' . $menu['route']) || request()->routeIs('soho.' . $menu['route'] . '*') ? 'active' : '' }}" href="{{ soho_route($menu['route']) }}" style="color: #fff;">
                                <i class="fa {{ $menu['icon'] }} me-2"></i> {{ $menu['title'] }}
                            </a>
                        </li>
                    @endforeach
                @endforeach
                <li class="nav-item" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 1rem; padding-top: 1rem;">
                    <form method="POST" action="{{ route('core.logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="nav-link btn btn-link" style="color: #fff; border: none; background: none; padding: 0 1rem;">
                            <i class="fa fa-sign-out me-2"></i> 退出登录
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <script src="{{ asset('assets/soho/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/soho/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/soho/js/echarts.min.js') }}"></script>
    <script>
        // 成功消息3秒后自动消失
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('success-alert');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.style.transition = 'opacity 0.5s';
                    successAlert.style.opacity = '0';
                    setTimeout(function() {
                        successAlert.remove();
                    }, 500);
                }, 3000);
            }

            const errorAlert = document.getElementById('error-alert');
            if (errorAlert) {
                setTimeout(function() {
                    errorAlert.style.transition = 'opacity 0.5s';
                    errorAlert.style.opacity = '0';
                    setTimeout(function() {
                        errorAlert.remove();
                    }, 500);
                }, 10000);
            }

            // 表单防重复提交
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    var submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        if (form.dataset.submitting === 'true') {
                            e.preventDefault();
                            return;
                        }
                        form.dataset.submitting = 'true';
                        var originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>提交中...';
                        setTimeout(function() {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                            form.dataset.submitting = 'false';
                        }, 30000);
                    }
                });
            });

            // 侧边栏菜单：点击 v/^ 按钮展开/收起子菜单
            document.querySelectorAll('.menu-toggle-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var menuGroup = this.closest('.menu-group');
                    if (menuGroup.classList.contains('expanded')) {
                        // 收起
                        menuGroup.classList.remove('expanded');
                        this.textContent = 'v';
                    } else {
                        // 展开
                        menuGroup.classList.add('expanded');
                        this.textContent = '^';
                    }
                });
            });
        });
    </script>
    @yield('scripts')
@include('layouts.partials.form-guard')
</body>
</html>
