@extends('soho::layouts.app')

@section('title', '平台账号授权详情')

@section('content')
<div class="container-fluid">
    <!-- 面包屑导航 -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
            <li class="breadcrumb-item"><a href="{{ route('soho.shop.authorization-status.index') }}">授权状态监控</a></li>
            <li class="breadcrumb-item active">{{ $platformAccount->account_name }}</li>
        </ol>
    </nav>

    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-cloud me-2"></i>{{ $platformAccount->account_name }}
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
                                <td class="text-muted" width="120">账号ID</td>
                                <td><code>{{ $platformAccount->id }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">账号名称</td>
                                <td>{{ $platformAccount->account_name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">平台</td>
                                <td><span class="badge bg-light text-dark">{{ $platformAccount->platform_name }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">账号类型</td>
                                <td><span class="badge bg-light text-dark">{{ $platformAccount->account_type }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">开发者ID</td>
                                <td><code>{{ $platformAccount->developer_id ?: '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">卖家主体</td>
                                <td>
                                    @if($platformAccount->seller_name)
                                        <div>{{ $platformAccount->seller_name }}</div>
                                        <div class="small text-muted">{{ $platformAccount->seller_id }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">站点/市场</td>
                                <td>
                                    @if($platformAccount->marketplace_name)
                                        <div>{{ $platformAccount->marketplace_name }}</div>
                                        <div class="small text-muted">{{ $platformAccount->marketplace_id }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">国家/地区</td>
                                <td>{{ $platformAccount->country_name ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">状态</td>
                                <td>
                                    <span class="badge bg-{{ $platformAccount->status === 'active' ? 'success' : ($platformAccount->status === 'suspended' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($platformAccount->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">创建人</td>
                                <td>
                                    @if($platformAccount->createdBy)
                                        {{ $platformAccount->createdBy->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">创建时间</td>
                                <td>{{ $platformAccount->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- OAuth授权状态 -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-plug me-2"></i>OAuth授权状态</h6>
                </div>
                <div class="card-body">
                    @php
                        $oauthStatusLabels = [
                            'authorized' => '已授权',
                            'unauthorized' => '未授权',
                            'expired' => '已过期',
                            'revoked' => '已撤销',
                        ];
                        $statusColors = [
                            'authorized' => 'success',
                            'unauthorized' => 'secondary',
                            'expired' => 'warning',
                            'revoked' => 'danger',
                        ];
                    @endphp
                    <div class="text-center mb-3">
                        <div class="display-4">
                            <span class="badge bg-{{ $statusColors[$platformAccount->oauth_status] ?? 'secondary' }} badge-lg">
                                {{ $oauthStatusLabels[$platformAccount->oauth_status] ?? '未知' }}
                            </span>
                        </div>
                    </div>
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="150">授权时间</td>
                                <td>{{ $platformAccount->authorized_at ? $platformAccount->authorized_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">最近Token刷新</td>
                                <td>{{ $platformAccount->last_token_refresh_at ? $platformAccount->last_token_refresh_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">最近API调用</td>
                                <td>{{ $platformAccount->last_api_call_at ? $platformAccount->last_api_call_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Token信息 -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-key me-2"></i>Token信息</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="150">Token类型</td>
                                <td>{{ $platformAccount->token_type ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Token过期时间</td>
                                <td>
                                    @if($platformAccount->token_expires_at)
                                        <div>{{ $platformAccount->token_expires_at->format('Y-m-d H:i:s') }}</div>
                                        @if($platformAccount->token_expires_at->lt(now()))
                                            <span class="badge bg-danger">已过期</span>
                                        @elseif($platformAccount->token_expires_at->lt(now()->addDays(7)))
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
                                <td class="text-muted">刷新Token过期</td>
                                <td>{{ $platformAccount->refresh_expires_at ? $platformAccount->refresh_expires_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" width="150">权限范围</td>
                                <td><code class="small">{{ $platformAccount->scope ?: '-' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">API版本</td>
                                <td>{{ $platformAccount->api_version ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">凭据版本</td>
                                <td><code>{{ $platformAccount->credential_version }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">LWA Client ID</td>
                                <td><code class="small">{{ $platformAccount->lwa_client_id ?: '-' }}</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 平台配置 -->
    @if($platformAccount->config)
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-cog me-2"></i>平台配置</h6>
        </div>
        <div class="card-body">
            <pre class="mb-0"><code>{{ json_encode($platformAccount->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
        </div>
    </div>
    @endif
</div>
@endsection