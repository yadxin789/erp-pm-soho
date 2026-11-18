@extends('soho::layouts.app')
@section('title', '结算管理')
@section('content')
<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">结算管理</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-0">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach(\App\Soho\Models\Shop::all() as $shop)
                        <option value="{{ $shop->id }}" @selected(request('shop_id') == $shop->id)>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="pending" @selected(request('status') == 'pending')>待结算</option>
                        <option value="processing" @selected(request('status') == 'processing')>结算中</option>
                        <option value="completed" @selected(request('status') == 'completed')>已结算</option>
                        <option value="failed" @selected(request('status') == 'failed')>结算失败</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.finance.settle') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">新建结算</h5></div>
            <div class="card-body">
                <form action="{{ route('soho.finance.settle.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">店铺 <span class="text-danger">*</span></label>
                        <select name="shop_id" class="form-select" required>
                            <option value="">请选择店铺</option>
                            @foreach(\App\Soho\Models\Shop::all() as $shop)
                            <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">结算金额 <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="请输入结算金额" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">结算方式 <span class="text-danger">*</span></label>
                        <select name="settle_method" class="form-select" required>
                            <option value="">请选择结算方式</option>
                            <option value="bank_transfer">银行转账</option>
                            <option value="alipay">支付宝</option>
                            <option value="wechat">微信</option>
                            <option value="cash">现金</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="请输入备注信息"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-plus me-1"></i> 创建结算
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">结算列表</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>结算号</th>
                                <th>店铺</th>
                                <th>金额</th>
                                <th>结算方式</th>
                                <th>状态</th>
                                <th>创建时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settles as $settle)
                            <tr>
                                <td>{{ $settle->settle_no }}</td>
                                <td>{{ $settle->shop->name ?? '-' }}</td>
                                <td>¥{{ number_format($settle->amount, 2) }}</td>
                                <td>
                                    @if($settle->settle_method == 'bank_transfer')<span class="badge bg-info">银行转账</span>
                                    @elseif($settle->settle_method == 'alipay')<span class="badge bg-success">支付宝</span>
                                    @elseif($settle->settle_method == 'wechat')<span class="badge bg-primary">微信</span>
                                    @elseif($settle->settle_method == 'cash')<span class="badge bg-warning">现金</span>
                                    @else<span class="badge bg-secondary">{{ $settle->settle_method }}</span>@endif
                                </td>
                                <td>
                                    @if($settle->status == 'pending')<span class="badge bg-warning">待结算</span>
                                    @elseif($settle->status == 'processing')<span class="badge bg-info">结算中</span>
                                    @elseif($settle->status == 'completed')<span class="badge bg-success">已结算</span>
                                    @elseif($settle->status == 'failed')<span class="badge bg-danger">结算失败</span>
                                    @else<span class="badge bg-secondary">{{ $settle->status }}</span>@endif
                                </td>
                                <td>{{ $settle->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">暂无结算记录</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $settles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection