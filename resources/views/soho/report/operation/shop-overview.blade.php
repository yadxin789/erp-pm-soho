@extends('soho::layouts.app')

{{-- 店铺概览视图 --}}
@section('title', '店铺概览')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">店铺概览</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">运营报表</li>
                    <li class="breadcrumb-item active">店铺概览</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.operation.shop.overview') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">平台</label>
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        @foreach($platformOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('platform') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.operation.shop.overview') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>店铺总数</small>
                    <h3 class="mb-0">{{ number_format($summary['total_shops'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>订单总数</small>
                    <h3 class="mb-0">{{ number_format($summary['total_orders'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>总销售额</small>
                    <h3 class="mb-0">¥{{ number_format($summary['total_sales'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>平均退款率</small>
                    <h3 class="mb-0">{{ number_format($summary['avg_refund_rate'] ?? 0, 2) }}%</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 数据表格 --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>店铺名称</th>
                        <th>平台</th>
                        <th class="text-end">订单数</th>
                        <th class="text-end">销售额</th>
                        <th class="text-end">退款率</th>
                        <th class="text-end">发货及时率</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // 平台中文名称映射
                        $platformNames = [
                            'taobao' => '淘宝', 'jd' => '京东', 'pdd' => '拼多多',
                            'douyin' => '抖音', 'amazon' => '亚马逊',
                        ];
                    @endphp
                    @forelse($shops as $index => $shop)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>{{ $shop->shop_name }}</td>
                            <td><span class="badge bg-secondary">{{ $platformNames[$shop->platform] ?? $shop->platform }}</span></td>
                            <td class="text-end">{{ number_format($shop->order_count) }}</td>
                            <td class="text-end">¥{{ number_format($shop->sales_amount, 2) }}</td>
                            <td class="text-end">
                                @php
                                    $rateColor = $shop->refund_rate >= 10 ? 'danger' : ($shop->refund_rate >= 5 ? 'warning' : 'success');
                                @endphp
                                <span class="badge bg-{{ $rateColor }}">{{ number_format($shop->refund_rate, 2) }}%</span>
                            </td>
                            <td class="text-end">
                                @php
                                    $shipColor = $shop->ship_on_time_rate >= 95 ? 'success' : ($shop->ship_on_time_rate >= 80 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $shipColor }}">{{ number_format($shop->ship_on_time_rate, 2) }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- 分页 --}}
        @if($shops->hasPages())
            <div class="card-footer">{{ $shops->links() }}</div>
        @endif
    </div>
@endsection
