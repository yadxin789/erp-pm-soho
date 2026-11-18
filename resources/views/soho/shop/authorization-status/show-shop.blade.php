@extends('soho::layouts.app')

@section('title', '店铺授权详情')

@section('content')
<div class="container-fluid">
    <!-- 面包屑导航 -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
            <li class="breadcrumb-item"><a href="{{ route('soho.shop.authorization-status.index') }}">授权状态监控</a></li>
            <li class="breadcrumb-item active">{{ $shop->shop_name }}</li>
        </ol>
    </nav>

    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-store me-2"></i>{{ $shop->shop_name }}
        </h4>
        <div>
            <a href="{{ route('soho.shop.authorization-status.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>
    </div>

    <div class="row">
        <!-- 基本信息 -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-info-circle me-2"></i>基本信息</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="120">店铺ID</td>
                                <td><code>{{ $shop->id }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">店铺名称</td>
                                <td>{{ $shop->shop_name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">店铺编码</td>
                                <td><code>{{ $shop->shop_code ?: '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">平台</td>
                                <td><span class="badge bg-light text-dark">{{ $shop->platform }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">店铺链接</td>
                                <td>
                                    @if($shop->shop_url)
                                        <a href="{{ $shop->shop_url }}" target="_blank">
                                            {{ $shop->shop_url }} <i class="fa fa-external-link"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">状态</td>
                                <td>
                                    <span class="badge bg-{{ $shop->status ? 'success' : 'secondary' }}">
                                        {{ $shop->status ? '启用' : '停用' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">同步开关</td>
                                <td>
                                    <span class="badge bg-{{ $shop->sync_enabled ? 'success' : 'secondary' }}">
                                        {{ $shop->sync_enabled ? '开启' : '关闭' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">创建时间</td>
                                <td>{{ $shop->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">更新时间</td>
                                <td>{{ $shop->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 授权状态 -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-plug me-2"></i>授权状态</h6>
                </div>
                <div class="card-body">
                    @php
                        $authStatusLabels = [
                            0 => '未授权',
                            1 => '已授权',
                            2 => '已过期',
                        ];
                        $statusColors = [
                            0 => 'secondary',
                            1 => 'success',
                            2 => 'warning',
                        ];
                    @endphp
                    <div class="text-center mb-3">
                        <div class="display-4">
                            <span class="badge bg-{{ $statusColors[$shop->auth_status] ?? 'secondary' }} badge-lg">
                                {{ $authStatusLabels[$shop->auth_status] ?? '未知' }}
                            </span>
                        </div>
                    </div>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="120">App Key</td>
                                <td><code>{{ $shop->app_key ?: '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Token过期时间</td>
                                <td>
                                    @if($shop->token_expires_at)
                                        <div>{{ $shop->token_expires_at->format('Y-m-d H:i:s') }}</div>
                                        @if($shop->token_expires_at->lt(now()))
                                            <span class="badge bg-danger">已过期</span>
                                        @elseif($shop->token_expires_at->lt(now()->addDays(7)))
                                            <span class="badge bg-warning">即将过期</span>
                                        @else
                                            <span class="badge bg-success">有效</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">距离过期</td>
                                <td>
                                    @if($shop->token_expires_at)
                                        @if($shop->token_expires_at->lt(now()))
                                            <span class="text-danger">已过期</span>
                                        @else
                                            <span class="text-success">
                                                {{ $shop->token_expires_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 凭证信息 -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-key me-2"></i>凭证信息</h6>
        </div>
        <div class="card-body">
            @if($shop->credential)
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" width="150">Client ID</td>
                                    <td><code>{{ $shop->credential->client_id ?: '-' }}</code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Token类型</td>
                                    <td>{{ $shop->credential->token_type ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">授权时间</td>
                                    <td>{{ $shop->credential->authorized_at ? $shop->credential->authorized_at->format('Y-m-d H:i:s') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">最近刷新时间</td>
                                    <td>{{ $shop->credential->last_refresh_at ? $shop->credential->last_refresh_at->format('Y-m-d H:i:s') : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" width="150">Token过期时间</td>
                                    <td>{{ $shop->credential->token_expires_at ? $shop->credential->token_expires_at->format('Y-m-d H:i:s') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">刷新Token过期</td>
                                    <td>{{ $shop->credential->refresh_expires_at ? $shop->credential->refresh_expires_at->format('Y-m-d H:i:s') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">权限范围</td>
                                    <td><code class="small">{{ $shop->credential->scope ?: '-' }}</code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">创建时间</td>
                                    <td>{{ $shop->credential->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fa fa-key fa-2x mb-2"></i>
                    <div>暂无凭证信息</div>
                </div>
            @endif
        </div>
    </div>

    <!-- 备注 -->
    @if($shop->remark)
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-comment me-2"></i>备注</h6>
        </div>
        <div class="card-body">
            {{ $shop->remark }}
        </div>
    </div>
    @endif
</div>
@endsection