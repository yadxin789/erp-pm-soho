@extends('soho::layouts.app')
@section('title', '对账管理')
@section('content')
<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">对账管理</h5></div>
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
                        <option value="pending" @selected(request('status') == 'pending')>待对账</option>
                        <option value="processing" @selected(request('status') == 'processing')>对账中</option>
                        <option value="completed" @selected(request('status') == 'completed')>已对账</option>
                        <option value="exception" @selected(request('status') == 'exception')>对账异常</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.finance.reconcile') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">新建对账</h5></div>
            <div class="card-body">
                <form action="{{ route('soho.finance.reconcile.store') }}" method="POST">
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
                        <label class="form-label">对账周期开始 <span class="text-danger">*</span></label>
                        <input type="date" name="period_start" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">对账周期结束 <span class="text-danger">*</span></label>
                        <input type="date" name="period_end" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="请输入备注信息"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-plus me-1"></i> 创建对账
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">对账列表</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>对账号</th>
                                <th>店铺</th>
                                <th>周期</th>
                                <th>金额</th>
                                <th>状态</th>
                                <th>创建时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reconciles as $reconcile)
                            <tr>
                                <td>{{ $reconcile->reconcile_no }}</td>
                                <td>{{ $reconcile->shop->name ?? '-' }}</td>
                                <td>{{ $reconcile->period_start?->format('Y-m-d') }} ~ {{ $reconcile->period_end?->format('Y-m-d') }}</td>
                                <td>¥{{ number_format($reconcile->amount, 2) }}</td>
                                <td>
                                    @if($reconcile->status == 'pending')<span class="badge bg-warning">待对账</span>
                                    @elseif($reconcile->status == 'processing')<span class="badge bg-info">对账中</span>
                                    @elseif($reconcile->status == 'completed')<span class="badge bg-success">已对账</span>
                                    @elseif($reconcile->status == 'exception')<span class="badge bg-danger">对账异常</span>
                                    @else<span class="badge bg-secondary">{{ $reconcile->status }}</span>@endif
                                </td>
                                <td>{{ $reconcile->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">暂无对账记录</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $reconciles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection