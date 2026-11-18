@extends('soho::layouts.app')

@section('title', '店铺连接状态监控')

@section('content')
<div class="container-fluid">
    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="fa fa-plug me-2"></i>店铺连接状态监控
        </h4>
        <div>
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#helpModal">
                <i class="fa fa-question-circle me-1"></i>帮助
            </button>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row mb-4">
        <!-- 店铺连接统计 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-store me-2"></i>店铺连接状态</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="display-4 text-primary">{{ $statistics['shops']['total'] }}</div>
                            <div class="small text-muted">总计</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-success">{{ $statistics['shops']['connected'] }}</div>
                            <div class="small text-muted">已连接</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-warning">{{ $statistics['shops']['degraded'] }}</div>
                            <div class="small text-muted">降级</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-danger">{{ $statistics['shops']['disconnected'] }}</div>
                            <div class="small text-muted">已断开</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 平台账号连接统计 -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-cloud me-2"></i>平台账号连接状态</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="display-4 text-primary">{{ $statistics['platform_accounts']['total'] }}</div>
                            <div class="small text-muted">总计</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-success">{{ $statistics['platform_accounts']['connected'] }}</div>
                            <div class="small text-muted">已连接</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-warning">{{ $statistics['platform_accounts']['degraded'] }}</div>
                            <div class="small text-muted">降级</div>
                        </div>
                        <div class="col">
                            <div class="display-4 text-danger">{{ $statistics['platform_accounts']['disconnected'] }}</div>
                            <div class="small text-muted">已断开</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 筛选表单 -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.monitor.shop-connection') }}" class="row g-3">
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
                    <a href="{{ route('soho.monitor.shop-connection') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 店铺连接状态表 -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-store me-2"></i>店铺连接状态</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>店铺名称</th>
                            <th>平台</th>
                            <th>连接状态</th>
                            <th>授权状态</th>
                            <th>同步开关</th>
                            <th>Token过期时间</th>
                            <th>连接说明</th>
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
                                    <span class="badge bg-{{ $shop->connection_status['color'] }}">
                                        {{ $shop->connection_status['label'] }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $authStatusLabels = [0 => '未授权', 1 => '已授权', 2 => '已过期'];
                                        $authStatusColors = [0 => 'secondary', 1 => 'success', 2 => 'warning'];
                                    @endphp
                                    <span class="badge bg-{{ $authStatusColors[$shop->auth_status] ?? 'secondary' }}">
                                        {{ $authStatusLabels[$shop->auth_status] ?? '未知' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $shop->sync_enabled ? 'success' : 'secondary' }}">
                                        {{ $shop->sync_enabled ? '开启' : '关闭' }}
                                    </span>
                                </td>
                                <td>
                                    @if($shop->credential && $shop->credential->token_expires_at)
                                        <div class="small">{{ $shop->credential->token_expires_at->format('Y-m-d H:i') }}</div>
                                        @if($shop->credential->token_expires_at->lt(now()))
                                            <span class="badge bg-danger">已过期</span>
                                        @elseif($shop->credential->token_expires_at->lt(now()->addDays(7)))
                                            <span class="badge bg-warning">即将过期</span>
                                        @else
                                            <span class="badge bg-success">有效</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $shop->connection_status['message'] }}
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

    <!-- 平台账号连接状态表 -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-cloud me-2"></i>平台账号连接状态</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>账号名称</th>
                            <th>平台</th>
                            <th>连接状态</th>
                            <th>OAuth状态</th>
                            <th>Token过期时间</th>
                            <th>最近API调用</th>
                            <th>连接说明</th>
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
                                    <span class="badge bg-{{ $account->connection_status['color'] }}">
                                        {{ $account->connection_status['label'] }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $oauthStatusLabels = ['authorized' => '已授权', 'unauthorized' => '未授权', 'expired' => '已过期', 'revoked' => '已撤销'];
                                        $oauthStatusColors = ['authorized' => 'success', 'unauthorized' => 'secondary', 'expired' => 'warning', 'revoked' => 'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $oauthStatusColors[$account->oauth_status] ?? 'secondary' }}">
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
                                    @if($account->last_api_call_at)
                                        {{ $account->last_api_call_at->format('Y-m-d H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $account->connection_status['message'] }}
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
</div>

<!-- 帮助模态框 -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">店铺连接状态监控说明</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>功能说明</h6>
                <p class="small text-muted">
                    本页面用于监控所有店铺和平台账号的连接状态，帮助运维人员快速定位连接异常的店铺和账号。
                </p>

                <h6 class="mt-3">连接状态说明</h6>
                <ul class="small text-muted">
                    <li><strong>已连接</strong>：连接状态正常，授权有效，Token有效，同步开启</li>
                    <li><strong>降级</strong>：连接状态降级，可能是Token即将过期或同步关闭</li>
                    <li><strong>已断开</strong>：连接已断开，可能是未授权、授权过期、Token过期或账号停用</li>
                    <li><strong>未知</strong>：无法判断连接状态，缺少必要信息</li>
                </ul>

                <h6 class="mt-3">建议操作</h6>
                <ul class="small text-muted">
                    <li>定期检查连接状态，确保所有店铺和平台账号都正常连接</li>
                    <li>对于降级状态的店铺/账号，及时安排Token刷新或开启同步</li>
                    <li>对于已断开的店铺/账号，及时进行重新授权或Token刷新</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
@endsection