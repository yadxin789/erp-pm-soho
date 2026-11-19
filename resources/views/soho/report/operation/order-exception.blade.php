@extends('soho::layouts.app')

{{-- 订单异常报表视图 --}}
@section('title', '订单异常报表')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">订单异常报表</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">运营报表</li>
                    <li class="breadcrumb-item active">订单异常</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.operation.order.exception') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">开始日期</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">结束日期</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">异常类型</label>
                    <select name="type" class="form-select">
                        <option value="">全部</option>
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
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
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.operation.order.exception') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>异常总数</small>
                    <h3 class="mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>地址异常</small>
                    <h3 class="mb-0">{{ number_format($stats['address'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>支付异常</small>
                    <h3 class="mb-0">{{ number_format($stats['payment'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>库存不足</small>
                    <h3 class="mb-0">{{ number_format($stats['stock'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body py-3">
                    <small>物流异常</small>
                    <h3 class="mb-0">{{ number_format($stats['logistics'] ?? 0) }}</h3>
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
                        <th>订单号</th>
                        <th>店铺</th>
                        <th>平台</th>
                        <th>订单状态</th>
                        <th>异常类型</th>
                        <th>异常描述</th>
                        <th class="text-end">订单金额</th>
                        <th>发生时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td><code>{{ $item->platform_order_no }}</code></td>
                            <td>{{ $item->shop_name ?? '-' }}</td>
                            <td>{{ $item->platform ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $item->order_status }}</span></td>
                            <td><span class="badge bg-danger">{{ $item->abnormal_reason ?? '未标注' }}</span></td>
                            <td class="small">{{ $item->abnormal_reason ?? '-' }}</td>
                            <td class="text-end">¥{{ number_format($item->total_amount, 2) }}</td>
                            <td class="small">{{ optional($item->order_created_at)->format('Y-m-d H:i') }}</td>
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
        @if($items->total() > 0)
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
