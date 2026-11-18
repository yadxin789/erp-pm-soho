@extends('soho::layouts.app')
@section('title', '订单详情')
@section('content')
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">订单详情</h5>
        <div>
            @if($order->order_status === 'paid')
            <!-- 发货按钮 -->
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#shipModal">
                <i class="fa fa-truck me-1"></i> 发货
            </button>
            @endif

            @if(!in_array($order->order_status, ['frozen', 'completed', 'cancelled']))
            <!-- 冻结按钮 -->
            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#freezeModal">
                <i class="fa fa-lock me-1"></i> 冻结
            </button>
            @endif

            <!-- 备注按钮 -->
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#remarkModal">
                <i class="fa fa-pencil me-1"></i> 备注
            </button>

            <a href="{{ route('soho.order.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> 返回
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th width="120">订单号</th><td>{{ $order->order_number }}</td></tr>
                <tr><th>平台订单号</th><td>{{ $order->platform_order_no ?? '-' }}</td></tr>
                <tr><th>系统订单号</th><td>{{ $order->system_order_no ?? '-' }}</td></tr>
                <tr><th>支付状态</th><td>{{ $order->pay_status ?? '-' }}</td></tr>
                    <tr><th>店铺</th><td>{{ $order->shop->name ?? '-' }}</td></tr>
                    <tr><th>平台</th><td>{{ $order->platform_label }}</td></tr>
                    <tr><th>买家昵称</th><td>{{ $order->buyer_nickname ?? '-' }}</td></tr>
                    <tr><th>收货人</th><td>{{ $order->receiver_name ?? '-' }}</td></tr>
                    <tr><th>收货电话</th><td>{{ $order->receiver_phone ?? '-' }}</td></tr>
                    <tr><th>收货地址</th><td>{{ $order->receiver_province }}{{ $order->receiver_city }}{{ $order->receiver_district }} {{ $order->receiver_address }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th width="120">订单金额</th><td>¥{{ number_format($order->total_amount, 2) }}</td></tr>
                    <tr><th>实付金额</th><td>¥{{ number_format($order->pay_amount, 2) }}</td></tr>
                <tr><th>优惠金额</th><td>¥{{ number_format($order->discount_amount ?? 0, 2) }}</td></tr>
                <tr><th>运费</th><td>¥{{ number_format($order->freight_amount ?? 0, 2) }}</td></tr>
                <tr><th>币种</th><td>{{ $order->currency ?? 'CNY' }}</td></tr>
                <tr><th>买家留言</th><td>{{ $order->buyer_message ?? '-' }}</td></tr>
                    <tr><th>订单状态</th><td>
                        <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'frozen' ? 'danger' : ($order->status == 'shipped' ? 'info' : 'warning')) }}">
                            {{ $order->order_status_label }}
                        </span>
                        @if($order->is_abnormal) <span class="badge bg-warning">异常</span> @endif
                    </td></tr>
                    <tr><th>下单时间</th><td>{{ $order->order_created_at?->format('Y-m-d H:i:s') }}</td></tr>
                    <tr><th>发货时间</th><td>{{ $order->shipped_at ?? '-' }}</td></tr>
                    @if($order->abnormal_reason)
                    <tr><th>异常原因</th><td class="text-danger">{{ $order->abnormal_reason }}</td></tr>
                    @endif
                    @if($order->operator_remark)
                    <tr><th>运营备注</th><td>{{ $order->operator_remark }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- 订单明细 -->
        @if($order->orderItems->isNotEmpty())
        <h6 class="mt-3 mb-2">订单明细</h6>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>商品</th>
                    <th>数量</th>
                    <th>单价</th>
                    <th>小计</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->sku->name ?? ($item->platform_sku ?? '-') }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>¥{{ number_format($item->unit_price, 2) }}</td>
                    <td>¥{{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- 发货记录 -->
        @if($order->shipRecords->isNotEmpty())
        <h6 class="mt-3 mb-2">发货记录</h6>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>快递公司</th>
                    <th>物流单号</th>
                    <th>发货时间</th>
                    <th>状态</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->shipRecords as $record)
                <tr>
                    <td>{{ $record->expressCompany->name ?? '-' }}</td>
                    <td>{{ $record->tracking_number }}</td>
                    <td>{{ $record->ship_time?->format('Y-m-d H:i:s') }}</td>
                    <td>
                        @if($record->ship_status === 'pending')
                            <span class="badge bg-warning">待推送</span>
                        @elseif($record->ship_status === 'success')
                            <span class="badge bg-success">已推送</span>
                        @else
                            <span class="badge bg-danger">失败</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

<!-- 发货弹窗 -->
@if($order->order_status === 'paid')
<div class="modal fade" id="shipModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('soho.order.ship', $order) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">订单发货</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">快递公司</label>
                        <select name="express_company_id" class="form-select" required>
                            <option value="">请选择快递公司</option>
                            @foreach($expressCompanies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">物流单号</label>
                        <input type="text" name="tracking_number" class="form-control" required maxlength="200">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success">确认发货</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- 冻结弹窗 -->
@if(!in_array($order->order_status, ['frozen', 'completed', 'cancelled']))
<div class="modal fade" id="freezeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('soho.order.freeze', $order) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">冻结订单</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">确定要冻结此订单吗？冻结后订单将无法继续操作。</p>
                    <div class="mb-3">
                        <label class="form-label">冻结原因</label>
                        <textarea name="abnormal_reason" class="form-control" rows="3" maxlength="500" placeholder="请输入冻结原因（可选）"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">确认冻结</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- 备注弹窗 -->
<div class="modal fade" id="remarkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('soho.order.remark', $order) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">添加运营备注</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">备注内容</label>
                        <textarea name="operator_remark" class="form-control" rows="3" maxlength="500" required placeholder="请输入运营备注">{{ $order->operator_remark }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-warning">保存备注</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection