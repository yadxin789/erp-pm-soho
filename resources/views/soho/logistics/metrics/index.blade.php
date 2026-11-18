@extends('soho::layouts.app')

{{-- 发货及时率/物流时效统计页面标题 --}}
@section('title', '物流时效统计')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">发货及时率 / 物流时效</h4>
            {{-- 面包屑导航 --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('soho.logistics.on-time') }}">物流管理</a></li>
                    <li class="breadcrumb-item active">物流时效统计</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 时间范围筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.logistics.on-time') }}">
                <div class="row g-2 align-items-end">
                    {{-- 时间范围选择 --}}
                    <div class="col-md-3">
                        <label class="form-label small">统计时间范围</label>
                        <div class="btn-group w-100" role="group">
                            @foreach(['7' => '近7天', '30' => '近30天', '90' => '近90天'] as $days => $label)
                                <input type="radio" class="btn-check" name="days" id="days{{ $days }}" value="{{ $days }}" autocomplete="off" {{ (request('days', '7') == $days) ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="days{{ $days }}">{{ $label }}</label>
                            @endforeach
                        </div>
                    </div>
                    {{-- 自定义日期范围 --}}
                    <div class="col-md-3">
                        <label class="form-label small">开始日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    {{-- 快递公司筛选（可选） --}}
                    <div class="col-md-2">
                        <label class="form-label small">快递公司</label>
                        <select class="form-select" name="express_company">
                            <option value="">全部</option>
                            @foreach(($expressCompanies ?? []) as $value => $label)
                                <option value="{{ $value }}" {{ request('express_company') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 4个核心指标卡片 --}}
    <div class="row g-3 mb-3">
        {{-- 发货及时率 --}}
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>发货及时率</small>
                        <i class="fa fa-truck-fast"></i>
                    </div>
                    <h3 class="mb-0">{{ $metrics['on_time_rate'] ?? 0 }}%</h3>
                </div>
            </div>
        </div>
        {{-- 平均物流时效 --}}
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>平均物流时效</small>
                        <i class="fa fa-clock"></i>
                    </div>
                    <h3 class="mb-0">{{ $metrics['avg_delivery_hours'] ?? 0 }} <small>小时</small></h3>
                </div>
            </div>
        </div>
        {{-- 超时发货数 --}}
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>超时发货数</small>
                        <i class="fa fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="mb-0">{{ $metrics['overtime_ship_count'] ?? 0 }} <small>单</small></h3>
                </div>
            </div>
        </div>
        {{-- 超时未签收数 --}}
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>超时未签收数</small>
                        <i class="fa fa-hourglass-end"></i>
                    </div>
                    <h3 class="mb-0">{{ $metrics['overtime_unsigned_count'] ?? 0 }} <small>单</small></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- 时效分布表格 --}}
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fa fa-chart-bar me-1"></i> 时效分布</h6>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>时效区间</th>
                                <th class="text-end">订单数</th>
                                <th class="text-end">占比</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // 时效分布数据，支持后端传入或默认空数据
                                $distribution = $distribution ?? [
                                    '24h'    => ['count' => 0, 'ratio' => '0%'],
                                    '48h'    => ['count' => 0, 'ratio' => '0%'],
                                    '72h'    => ['count' => 0, 'ratio' => '0%'],
                                    '120h'   => ['count' => 0, 'ratio' => '0%'],
                                    '120h+'  => ['count' => 0, 'ratio' => '0%'],
                                ];
                                // 时效区间颜色映射
                                $rangeColors = [
                                    '24h'    => 'success',
                                    '48h'    => 'success',
                                    '72h'    => 'info',
                                    '120h'   => 'warning',
                                    '120h+'  => 'danger',
                                ];
                            @endphp
                            @foreach($distribution as $range => $data)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $rangeColors[$range] ?? 'secondary' }}">{{ $range }}</span>
                                    </td>
                                    <td class="text-end">{{ $data['count'] ?? 0 }}</td>
                                    <td class="text-end">{{ $data['ratio'] ?? '0%' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 按快递公司时效统计表格 --}}
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fa fa-building me-1"></i> 各快递公司时效统计</h6>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>快递公司</th>
                                <th class="text-end">订单数</th>
                                <th class="text-end">平均时效(h)</th>
                                <th class="text-end">72h签收率</th>
                                <th>趋势</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($companyStats ?? []) as $stat)
                                <tr>
                                    <td>{{ $stat['express_company'] ?? $stat->express_company ?? '-' }}</td>
                                    <td class="text-end">{{ $stat['order_count'] ?? $stat->order_count ?? 0 }}</td>
                                    <td class="text-end">{{ $stat['avg_hours'] ?? $stat->avg_hours ?? 0 }}</td>
                                    <td class="text-end">
                                        @php
                                            // 72h 签收率颜色映射
                                            $signRate = $stat['sign_rate_72h'] ?? $stat->sign_rate_72h ?? 0;
                                            $rateColor = $signRate >= 90 ? 'success' : ($signRate >= 70 ? 'warning' : 'danger');
                                        @endphp
                                        <span class="badge bg-{{ $rateColor }}">{{ $signRate }}%</span>
                                    </td>
                                    <td>
                                        {{-- 简易进度条展示72h签收率 --}}
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $rateColor }}" role="progressbar" style="width: {{ min(100, $signRate) }}%;"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
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
@endsection
