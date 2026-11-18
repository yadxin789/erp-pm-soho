@extends('soho::layouts.app')

@section('title', 'Token状态监控')

@section('content')
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-key me-2"></i>Token状态监控
        </h4>
        <div>
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#helpModal">
                <i class="fa fa-question-circle me-1"></i>帮助
            </button>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row mb-4">
        <!-- 店铺Token统计 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-store me-2"></i>店铺Token状态</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="display-4 text-primary">{{ $statistics['shops']['total'] }}</div>
                            <div class="small text-muted">总计</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-success">{{ $statistics['shops']['valid'] }}</div>
                            <div class="small text-muted">有效</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-warning">{{ $statistics['shops']['expiring_soon'] }}</div>
                            <div class="small text-muted">即将过期</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-danger">{{ $statistics['shops']['expired'] }}</div>
                            <div class="small text-muted">已过期</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 平台账号Token统计 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-cloud me-2"></i>平台账号Token状态</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="display-4 text-primary">{{ $statistics['platform_accounts']['total'] }}</div>
                            <div class="small text-muted">总计</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-success">{{ $statistics['platform_accounts']['valid'] }}</div>
                            <div class="small text-muted">有效</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-warning">{{ $statistics['platform_accounts']['expiring_soon'] }}</div>
                            <div class="small text-muted">即将过期</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-danger">{{ $statistics['platform_accounts']['expired'] }}</div>
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
            <form method="GET" action="{{ route('soho.shop.token-status') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">关键词</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="店铺名称/编码/平台">
                </div>
                <div class="col-md-3">
                    <label class="form-label">店铺平台</label>
                    <select class="form-select" name="platform">
                        <option value="">全部</option>
                        @foreach($platforms as $platform)
                            <option value="{{ $platform }}" {{ request('platform') == $platform ? 'selected' : '' }}>{{ $platform }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">平台账号平台</label>
                    <select class="form-select" name="platform_code">
                        <option value="">全部</option>
                        @foreach($platformCodes as $code)
                            <option value="{{ $code }}" {{ request('platform_code') == $code ? 'selected' : '' }}>{{ $code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fa fa-search"></i>
                    </button>
                    <a href="{{ route('soho.shop.token-status') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 店铺Token状态表 -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-store me-2"></i>店铺Token状态</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>店铺名称</th>
                            <th>平台</th>
                            <th>Token状态</th>
                            <th>Token过期时间</th>
                            <th>距离过期</th>
                            <th>最近刷新</th>
                            <th>状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shops as $index => $shop)
                            <tr>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $shop->shop_name }}</div>
                                    <div class="small text-muted">{{ $shop->shop_code ?: '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $shop->platform }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $shop->token_status['color'] }}">
                                        {{ $shop->token_status['label'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($shop->credential && $shop->credential->token_expires_at)
                                        <div class="small">{{ $shop->credential->token_expires_at->format('Y-m-d H:i') }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($shop->token_status['days_until_expiry']))
                                        @if($shop->token_status['days_until_expiry'] < 0)
                                            <span class="text-danger">已过期{{ abs($shop->token_status['days_until_expiry']) }}天</span>
                                        @else
                                            <span class="text-success">{{ $shop->token_status['days_until_expiry'] }}天</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($shop->credential && $shop->credential->last_refresh_at)
                                        {{ $shop->credential->last_refresh_at->format('Y-m-d H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $shop->status ? 'success' : 'secondary' }}">
                                        {{ $shop->status ? '启用' : '停用' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
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

    <!-- 平台账号Token状态表 -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-cloud me-2"></i>平台账号Token状态</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>账号名称</th>
                            <th>平台</th>
                            <th>Token状态</th>
                            <th>Token过期时间</th>
                            <th>距离过期</th>
                            <th>最近刷新</th>
                            <th>最近API调用</th>
                            <th>状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($platformAccounts as $index => $account)
                            <tr>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $account->account_name }}</div>
                                    <div class="small text-muted">{{ $account->developer_id ?: '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $account->platform_name }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $account->token_status['color'] }}">
                                        {{ $account->token_status['label'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($account->token_expires_at)
                                        <div class="small">{{ $account->token_expires_at->format('Y-m-d H:i') }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($account->token_status['days_until_expiry']))
                                        @if($account->token_status['days_until_expiry'] < 0)
                                            <span class="text-danger">已过期{{ abs($account->token_status['days_until_expiry']) }}天</span>
                                        @else
                                            <span class="text-success">{{ $account->token_status['days_until_expiry'] }}天</span>
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
                                <td class="small">
                                    @if($account->last_api_call_at)
                                        {{ $account->last_api_call_at->format('Y-m-d H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $account->status === 'active' ? 'success' : ($account->status === 'suspended' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
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
                <h5 class="modal-title">Token状态监控说明</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>功能说明</h6>
                <p class="small text-muted">
                    本页面用于监控所有店铺和平台账号的Token状态，包括：
                </p>
                <ul class="small text-muted">
                    <li>查看Token的有效性状态</li>
                    <li>监控Token过期时间</li>
                    <li>查看距离过期的剩余天数</li>
                    <li>查看最近刷新时间和最近API调用时间</li>
                    <li>快速定位需要刷新Token的店铺/账号</li>
                </ul>

                <h6 class="mt-3">Token状态说明</h6>
                <ul class="small text-muted">
                    <li><strong>有效</strong>：Token在有效期内，且距离过期时间超过7天</li>
                    <li><strong>即将过期</strong>：Token将在7天内过期，建议及时刷新</li>
                    <li><strong>已过期</strong>：Token已过期，需要重新授权或刷新</li>
                    <li><strong>无Token</strong>：尚未设置Token</li>
                </ul>

                <h6 class="mt-3">建议操作</h6>
                <ul class="small text-muted">
                    <li>定期检查Token状态，确保所有Token都在有效期内</li>
                    <li>对于即将过期的Token，提前安排刷新计划</li>
                    <li>对于已过期的Token，及时进行重新授权或刷新</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
@endsection