@extends('soho::layouts.app')

@section('title', '系统版本')

@section('content')
<div class="mb-3">
    <h4 class="mb-1"><i class="fa fa-info-circle me-2"></i>系统版本信息</h4>
    <p class="text-muted mb-0 small">查看系统版本号、运行环境、已安装模块及更新日志</p>
</div>

<div class="row">
    {{-- 左侧：系统概览 --}}
    <div class="col-lg-5 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-4">
                {{-- 系统图标 --}}
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                     style="width: 88px; height: 88px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fa fa-cubes text-white" style="font-size: 38px;"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $systemInfo['system_name'] }}</h5>
                <div class="mb-3">
                    <span class="badge bg-primary px-3 py-2">{{ $systemInfo['version'] }}</span>
                    <span class="badge bg-light text-dark border px-3 py-2 ms-1">
                        <i class="fa fa-calendar me-1"></i>{{ $systemInfo['release_date'] }}
                    </span>
                </div>
                <p class="text-muted small mb-0">多平台电商店铺统一管理系统</p>
            </div>
        </div>
    </div>

    {{-- 右侧：运行环境 --}}
    <div class="col-lg-7 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fa fa-server me-2 text-primary"></i>运行环境</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0 align-middle">
                    <tbody>
                        @foreach([
                            'Laravel 版本' => $systemInfo['laravel_version'],
                            'PHP 版本' => $systemInfo['php_version'],
                            '服务器软件' => $systemInfo['server_software'],
                            'PHP 运行模式' => $systemInfo['php_sapi'],
                            '数据库类型' => $systemInfo['db_driver'],
                            '数据库版本' => $systemInfo['db_version'],
                            '时区' => $systemInfo['timezone'],
                            '语言' => $systemInfo['locale'],
                            '运行环境' => $systemInfo['env'],
                            '调试模式' => $systemInfo['debug'],
                            '最大执行时间' => $systemInfo['max_execution_time'],
                            '内存限制' => $systemInfo['max_memory'],
                            '上传限制' => $systemInfo['upload_max'],
                        ] as $label => $value)
                            <tr>
                                <td class="text-muted small ps-3" style="width: 40%;">{{ $label }}</td>
                                <td class="fw-semibold small">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 已安装模块 --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fa fa-puzzle-piece me-2 text-primary"></i>已安装模块</h6>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($modules as $module)
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="fw-semibold">{{ $module['name'] }}</div>
                            @if($module['status'] === 'stable')
                                <span class="badge bg-success">稳定</span>
                            @elseif($module['status'] === 'beta')
                                <span class="badge bg-warning text-dark">测试</span>
                            @else
                                <span class="badge bg-secondary">内测</span>
                            @endif
                        </div>
                        <div class="text-muted small mb-1">{{ $module['desc'] }}</div>
                        <div class="text-muted small">
                            <i class="fa fa-code-branch me-1"></i>v{{ $module['version'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- 更新日志 --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0"><i class="fa fa-history me-2 text-primary"></i>更新日志</h6>
    </div>
    <div class="card-body">
        @php
            $typeLabels = ['major' => '主版本', 'minor' => '次版本', 'patch' => '补丁'];
            $typeColors = ['major' => 'primary', 'minor' => 'info', 'patch' => 'secondary'];
        @endphp
        @foreach($changelog as $log)
            <div class="d-flex mb-4">
                {{-- 时间线 --}}
                <div class="me-3 text-center" style="min-width: 80px;">
                    <div class="fw-bold">{{ $log['version'] }}</div>
                    <div class="small text-muted">{{ $log['date'] }}</div>
                    <span class="badge bg-{{ $typeColors[$log['type']] ?? 'secondary' }} mt-1">
                        {{ $typeLabels[$log['type']] ?? $log['type'] }}
                    </span>
                </div>
                {{-- 变更内容 --}}
                <div class="flex-grow-1 border-start border-2 ps-3">
                    <ul class="mb-0">
                        @foreach($log['changes'] as $change)
                            <li class="small text-muted mb-1">{{ $change }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
