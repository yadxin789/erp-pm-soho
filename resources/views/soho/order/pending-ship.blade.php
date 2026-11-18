@extends('soho::layouts.app')
@section('title', '待发货订单')
@section('content')
<div class="mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
            <li class="breadcrumb-item"><a href="{{ route('soho.order.index') }}">订单管理</a></li>
            <li class="breadcrumb-item active">待发货订单</li>
        </ol>
    </nav>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待发货订单列表</h5>
        <div>
            <span class="badge bg-info me-2">{{ $orders->total() }} 条记录</span>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#batchShipModal">
                <i class="fa fa-upload me-1"></i> 批量发货
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
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
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}" placeholder="结束日期">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.order.pending-ship') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>订单编号</th>
                        <th>店铺</th>
                        <th>买家</th>
                        <th>金额</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><input type="checkbox" name="order_ids[]" value="{{ $order->id }}"></td>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->shop->name ?? '-' }}</td>
                        <td>{{ $order->buyer_nickname ?? '-' }}</td>
                        <td>¥{{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-info">待发货</span>
                        </td>
                        <td>{{ $order->order_created_at?->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('soho.order.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                            <a href="{{ route('soho.order.ship', $order) }}" class="btn btn-sm btn-success">
                                <i class="fa fa-truck"></i> 发货
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">暂无数据</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- 批量发货弹窗 -->
<div class="modal fade" id="batchShipModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('soho.order.batch-ship') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">批量发货</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>格式说明：</strong>每行一条记录，支持两种格式：<br>
                        1. <code>订单号,物流单号,快递公司ID</code>（三列）<br>
                        2. <code>订单号,物流单号</code>（需选择下方默认快递公司）<br>
                        支持 CSV 文件上传或直接粘贴文本。
                    </div>
                    <div class="mb-3">
                        <label class="form-label">默认快递公司（可选）</label>
                        <select name="express_company_id" class="form-select">
                            <option value="">请选择默认快递公司</option>
                            @foreach(\App\Soho\Models\ExpressCompany::where('status', true)->orderBy('sort_order')->get() as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">上传 CSV 文件</label>
                        <input type="file" name="shipping_file" class="form-control" accept=".csv,.txt">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">或直接粘贴物流数据</label>
                        <textarea name="shipping_content" class="form-control" rows="6" placeholder="订单号1,物流单号1,快递公司ID&#10;订单号2,物流单号2&#10;订单号3,物流单号3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success">确认批量发货</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('input[name="order_ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection
