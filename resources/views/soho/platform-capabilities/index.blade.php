@extends('soho::layouts.app')

@section('title', '平台能力')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">平台能力</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">平台能力列表</h5>
    </div>
    <div class="card-body">
        <!-- 筛选表单 -->
        <form method="GET" action="{{ route('soho.platform_capabilities.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="platform_code" class="form-select">
                        <option value="">全部平台</option>
                        <option value="taobao" @selected(request('platform_code') == 'taobao')>淘宝</option>
                        <option value="jd" @selected(request('platform_code') == 'jd')>京东</option>
                        <option value="pinduoduo" @selected(request('platform_code') == 'pinduoduo')>拼多多</option>
                        <option value="amazon" @selected(request('platform_code') == 'amazon')>亚马逊</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="active" @selected(request('status') == 'active')>启用</option>
                        <option value="inactive" @selected(request('status') == 'inactive')>禁用</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.platform_capabilities.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>平台账号</th>
                        <th>平台</th>
                        <th>能力版本</th>
                        <th>商品</th>
                        <th>订单</th>
                        <th>库存</th>
                        <th>发货</th>
                        <th>售后</th>
                        <th>限流(次/分)</th>
                        <th>状态</th>
                        <th>备注</th>
                        <th>更新时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($capabilities as $capability)
                    <tr>
                        <td>{{ $capability->id }}</td>
                        <td>{{ $capability->platformAccount?->account_name ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ $capability->platform_label }}</span></td>
                        <td>{{ $capability->capability_version }}</td>
                        <!-- 商品能力：拉取/推送/发布 -->
                        <td>
                            @if($capability->supports_product_pull)<span class="badge bg-success me-1" title="商品拉取">拉</span>@endif
                            @if($capability->supports_product_push)<span class="badge bg-primary me-1" title="商品推送">推</span>@endif
                            @if($capability->supports_product_publish)<span class="badge bg-info me-1" title="商品发布">发</span>@endif
                            @if(!$capability->supports_product_pull && !$capability->supports_product_push && !$capability->supports_product_publish)
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <!-- 订单能力：拉取/管理 -->
                        <td>
                            @if($capability->supports_order_pull)<span class="badge bg-success me-1" title="订单拉取">拉</span>@endif
                            @if($capability->supports_order_management)<span class="badge bg-primary me-1" title="订单管理">管</span>@endif
                            @if(!$capability->supports_order_pull && !$capability->supports_order_management)
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <!-- 库存能力：推送 -->
                        <td>
                            @if($capability->supports_inventory_push)
                                <span class="badge bg-success" title="库存推送">支持</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <!-- 发货能力：推送/物流轨迹 -->
                        <td>
                            @if($capability->supports_shipment_push)<span class="badge bg-success me-1" title="发货推送">推</span>@endif
                            @if($capability->supports_logistics_tracking)<span class="badge bg-info me-1" title="物流轨迹">迹</span>@endif
                            @if(!$capability->supports_shipment_push && !$capability->supports_logistics_tracking)
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <!-- 售后能力：拉取/退款推送 -->
                        <td>
                            @if($capability->supports_after_sale_pull)<span class="badge bg-success me-1" title="售后拉取">拉</span>@endif
                            @if($capability->supports_refund_push)<span class="badge bg-warning text-dark me-1" title="退款推送">退</span>@endif
                            @if(!$capability->supports_after_sale_pull && !$capability->supports_refund_push)
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <!-- API限流 -->
                        <td>{{ $capability->rate_limit_per_minute }}</td>
                        <!-- 状态 -->
                        <td>
                            <span class="badge bg-{{ $capability->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $capability->status_label }}
                            </span>
                        </td>
                        <!-- 备注 -->
                        <td>{{ $capability->remark ?? '-' }}</td>
                        <!-- 更新时间 -->
                        <td>{{ $capability->updated_at ? $capability->updated_at->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="13" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $capabilities->links() }}
    </div>
</div>

<!-- 能力说明 -->
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-info-circle"></i> 能力标记说明</h6>
    </div>
    <div class="card-body">
        <div class="row text-muted small">
            <div class="col-md-4">
                <p class="mb-1"><span class="badge bg-success">拉</span> 支持数据拉取 &nbsp; <span class="badge bg-primary">推</span> 支持数据推送</p>
                <p class="mb-1"><span class="badge bg-info">发</span> 支持商品发布 &nbsp; <span class="badge bg-info">管</span> 支持订单管理</p>
            </div>
            <div class="col-md-4">
                <p class="mb-1"><span class="badge bg-info">迹</span> 支持物流轨迹查询</p>
                <p class="mb-1"><span class="badge bg-warning text-dark">退</span> 支持退款推送</p>
            </div>
            <div class="col-md-4">
                <p class="mb-1">限流：每分钟最大API请求数</p>
                <p class="mb-1">能力版本：标识能力配置的版本号</p>
            </div>
        </div>
    </div>
</div>
@endsection