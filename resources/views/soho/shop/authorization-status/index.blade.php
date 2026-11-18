@extends('soho::layouts.app')

@section('title', '授权状态监控')

@section('content')
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-plug me-2"></i>授权状态监控
        </h4>
        <div>
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#helpModal">
                <i class="fa fa-question-circle me-1"></i>帮助
            </button>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row mb-4">
        <!-- 店铺授权统计 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-store me-2"></i>店铺授权状态</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="display-4 text-primary">{{ $statistics['shops']['total'] }}</div>
                            <div class="small text-muted">总计</div>
                        </div>
                        <div class="col-3">
                            <div class="display-4 text-success">{{ $statistics['shops']['authorized'] }}</div>
                            <div class="small text-muted">已授权</div>
                        </div>
                        <div class="col-3">
                            <div class="display-4 text-secondary">{{ $statistics['shops']['unauthorized'] }}</div>
                            <div class="small text-muted">未授权</div>
                        </div>
                        <div class="col-3">
                            <div class="display-4 text-warning">{{ $statistics['shops']['expired'] }}</div>
                            <div class="small text-muted">已过期</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 平台账号授权统计 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-cloud me-2"></i>平台账号授权状态</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="display-4 text-primary">{{ $statistics['platform_accounts']['total'] }}</div>
                            <div class="small text-muted">总计</div>
                        </div>
                        <div class="col-3">
                            <div class="display-4 text-success">{{ $statistics['platform_accounts']['authorized'] }}</div>
                            <div class="small text-muted">已授权</div>
                        </div>
                        <div class="col-3">
                            <div class="display-4 text-secondary">{{ $statistics['platform_accounts']['unauthorized'] }}</div>
                            <div class="small text-muted">未授权</div>
                        </div>
                        <div class="col-3">
                            <div class="display-4 text-warning">{{ $statistics['platform_accounts']['expired'] }}</div>
                            <div class="small text-muted">已过期</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选表单 -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.shop.authorization-status.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">关键词</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="店铺名称/编码/平台">
                </div>
                <div class="col-md-2">
                    <label class="form-label">店铺平台</label>
                    <select class="form-select" name="platform">
                        <option value="">全部</option>
                        @foreach($platforms as $platform)
                            <option value="{{ $platform }}" {{ request('platform') == $platform ? 'selected' : '' }}>{{ $platform }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">店铺授权状态</label>
                    <select class="form-select" name="auth_status">
                        <option value="">全部</option>
                        @foreach($authStatusLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('auth_status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">平台账号平台</label>
                    <select class="form-select" name="platform_code">
                        <option value="">全部</option>
                        @foreach($platformCodes as $code)
                            <option value="{{ $code }}" {{ request('platform_code') == $code ? 'selected' : '' }}>{{ $code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">账号授权状态</label>
                    <select class="form-select" name="oauth_status">
                        <option value="">全部</option>
                        @foreach($oauthStatusLabels as $value => $label)
                            <option value="{{ $value }}" {{ request('oauth_status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fa fa-search"></i>
                    </button>
                    <a href="{{ route('soho.shop.authorization-status.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 店铺授权状态表 -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-store me-2"></i>店铺授权状态</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>店铺名称</th>
                            <th>平台</th>
                            <th>店铺编码</th>
                            <th>授权状态</th>
                            <th>Token过期时间</th>
                            <th>同步开关</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th width="100">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shops as $index => $shop)
                            <tr>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $shop->shop_name }}</div>
                                    @if($shop->shop_url)
                                        <a href="{{ $shop->shop_url }}" target="_blank" class="small text-muted">
                                            <i class="fa fa-external-link"></i> 查看店铺
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $shop->platform }}</span>
                                </td>
                                <td><code class="small">{{ $shop->shop_code ?: '-' }}</code></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            0 => 'secondary',
                                            1 => 'success',
                                            2 => 'warning',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$shop->auth_status] ?? 'secondary' }}">
                                        {{ $authStatusLabels[$shop->auth_status] ?? '未知' }}
                                    </span>
                                </td>
                                <td>
                                    @if($shop->token_expires_at)
                                        <div class="small">{{ $shop->token_expires_at->format('Y-m-d H:i') }}</div>
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
                                <td>
                                    <span class="badge bg-{{ $shop->sync_enabled ? 'success' : 'secondary' }}">
                                        {{ $shop->sync_enabled ? '开启' : '关闭' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $shop->status ? 'success' : 'secondary' }}">
                                        {{ $shop->status ? '启用' : '停用' }}
                                    </span>
                                </td>
                                <td class="small">{{ $shop->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('soho.shop.authorization-status.show-shop', $shop) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fa fa-inbox fa-2x mb-2"></i>
                                    <div>暂无数据</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 平台账号授权状态表 -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-cloud me-2"></i>平台账号授权状态</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>账号名称</th>
                            <th>平台</th>
                            <th>账号类型</th>
                            <th>开发者ID</th>
                            <th>授权状态</th>
                            <th>Token过期时间</th>
                            <th>最近刷新</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th width="100">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($platformAccounts as $index => $account)
                            <tr>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $account->account_name }}</div>
                                    @if($account->seller_name)
                                        <div class="small text-muted">{{ $account->seller_name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $account->platform_name }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $account->account_type }}
                                    </span>
                                </td>
                                <td><code class="small">{{ $account->developer_id ?: '-' }}</code></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'authorized' => 'success',
                                            'unauthorized' => 'secondary',
                                            'expired' => 'warning',
                                            'revoked' => 'danger',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$account->oauth_status] ?? 'secondary' }}">
                                        {{ $oauthStatusLabels[$account->oauth_status] ?? '未知' }}
                                    </span>
                                </td>
                                <td>
                                    @if($account->token_expires_at)
                                        <div class="small">{{ $account->token_expires_at->format('Y-m-d H:i') }}</div>
                                        @if($account->token_expires_at->lt(now()))
                                            <span class="badge bg-danger">已过期</span>
                                        @elseif($account->token_expires_at->lt(now()->addDays(7)))
                                            <span class="badge bg-warning">即将过期</span>
                                        @else
                                            <span class="badge bg-success">有效</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($account->last_token_refresh_at)
                                        {{ $account->last_token_refresh_at->format('Y-m-d H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $account->status === 'active' ? 'success' : ($account->status === 'suspended' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </td>
                                <td class="small">{{ $account->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('soho.shop.authorization-status.show-platform-account', $account) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="fa fa-inbox fa-2x mb-2"></i>
                                    <div>暂无数据</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 帮助模态框 -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">授权状态监控说明</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>功能说明</h6>
                <p class="small text-muted">
                    本页面用于监控所有店铺和平台账号的授权状态，包括：
                </p>
                <ul class="small text-muted">
                    <li>查看店铺的授权状态（已授权/未授权/已过期）</li>
                    <li>查看平台账号的OAuth授权状态</li>
                    <li>监控Token过期时间</li>
                    <li>查看最近刷新时间</li>
                    <li>快速定位授权异常的店铺/账号</li>
                </ul>

                <h6 class="mt-3">授权状态说明</h6>
                <ul class="small text-muted">
                    <li><strong>已授权</strong>：授权有效，可以正常同步数据</li>
                    <li><strong>未授权</strong>：尚未进行授权，无法同步数据</li>
                    <li><strong>已过期</strong>：授权已过期，需要重新授权</li>
                    <li><strong>已撤销</strong>：授权已被平台撤销</li>
                </ul>

                <h6 class="mt-3">Token状态说明</h6>
                <ul class="small text-muted">
                    <li><strong>有效</strong>：Token在有效期内，可正常使用</li>
                    <li><strong>即将过期</strong>：Token将在7天内过期，建议及时刷新</li>
                    <li><strong>已过期</strong>：Token已过期，需要重新授权或刷新</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
@endsection