@extends('soho::layouts.app')

{{-- 售后报表视图 --}}
@section('title', '售后报表')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">售后报表</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">运营报表</li>
                    <li class="breadcrumb-item active">售后报表</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.report.aftersale') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">开始日期</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">结束日期</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">店铺</label>
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->shop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">售后类型</label>
                    <select name="type" class="form-select">
                        <option value="">全部</option>
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.report.aftersale') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>退款金额</small>
                    <h3 class="mb-0">¥{{ number_format($stats['refund_amount'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>退货数量</small>
                    <h3 class="mb-0">{{ number_format($stats['return_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>换货数量</small>
                    <h3 class="mb-0">{{ number_format($stats['exchange_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body py-3">
                    <small>争议数量</small>
                    <h3 class="mb-0">{{ number_format($stats['dispute_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 图表占位区域 --}}
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">售后类型分布</h5></div>
        <div class="card-body">
            <div id="type-chart" style="width: 100%; height: 300px;"></div>
        </div>
    </div>

    {{-- 数据表格 --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>售后单号</th>
                        <th>类型</th>
                        <th>状态</th>
                        <th class="text-end">金额</th>
                        <th>原因</th>
                        <th>申请时间</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusColors = [
                            'pending' => 'warning', 'processing' => 'info',
                            'approved' => 'primary', 'rejected' => 'danger',
                            'completed' => 'success', 'cancelled' => 'secondary',
                        ];
                    @endphp
                    @forelse($aftersales as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td><code>{{ $item->after_sale_no }}</code></td>
                            <td><span class="badge bg-light text-dark">{{ $typeOptions[$item->type] ?? $item->type }}</span></td>
                            <td><span class="badge bg-{{ $statusColors[$item->status] ?? 'secondary' }}">{{ $item->status }}</span></td>
                            <td class="text-end">¥{{ number_format($item->refund_amount, 2) }}</td>
                            <td>{{ $item->reason ?? '-' }}</td>
                            <td class="small">{{ optional($item->apply_time)->format('Y-m-d H:i') }}</td>
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
        @if($aftersales->total() > 0)
            <div class="card-footer">{{ $aftersales->links() }}</div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chartDom = document.getElementById('type-chart');
            if (!chartDom || typeof echarts === 'undefined') return;

            var chart = echarts.init(chartDom);
            var data = {!! json_encode(collect($typeStats)->map(function($item) use ($typeOptions) {
                return ['name' => $typeOptions[$item->type] ?? $item->type, 'value' => $item->count];
            })->values()) !!};

            var option = {
                tooltip: { trigger: 'item', formatter: '{a} <br/>{b}: {c} ({d}%)' },
                legend: { orient: 'vertical', left: 'left' },
                series: [{
                    name: '售后类型',
                    type: 'pie',
                    radius: '50%',
                    data: data,
                    emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0, 0, 0, 0.5)' } }
                }]
            };
            chart.setOption(option);
            window.addEventListener('resize', function() { chart.resize(); });
        });
    </script>
@endsection
