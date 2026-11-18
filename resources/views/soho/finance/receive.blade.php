@extends('soho::layouts.app')
@section('title', '收款记录')
@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">总收款金额</h6>
                        <h3 class="mb-0">¥{{ number_format($totalAmount ?? 0, 2) }}</h3>
                    </div>
                    <div class="fa fa-money fa-3x opacity-50"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">订单数量</h6>
                        <h3 class="mb-0">{{ $orderCount }}</h3>
                    </div>
                    <div class="fa fa-shopping-cart fa-3x opacity-50"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">收款记录</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="订单号/买家昵称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach(\App\Soho\Models\Shop::all() as $shop)
                        <option value="{{ $shop->id }}" @selected(request('shop_id') == $shop->id)>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.finance.receive') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>订单号</th>
                        <th>店铺</th>
                        <th>买家</th>
                        <th>支付金额</th>
                        <th>支付时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                    <tr>
                        <td>{{ $record->order_number }}</td>
                        <td>{{ $record->shop->name ?? '-' }}</td>
                        <td>{{ $record->buyer_nickname ?? '-' }}</td>
                        <td>¥{{ number_format($record->pay_amount, 2) }}</td>
                        <td>{{ $record->paid_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">暂无收款记录</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links() }}
    </div>
</div>
@endsection