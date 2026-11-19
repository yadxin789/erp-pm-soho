@extends('soho::layouts.app')

@section('title', '履约单执行 #'.$f->id)

@section('content')
@php
    $steps = [
        'pending' => '待拣货',
        'picked' => '已拣货',
        'packed' => '已打包',
        'shipped' => '已发货',
    ];
    $stepIndex = ['pending' => 0, 'picked' => 1, 'packed' => 2, 'shipped' => 3];
    $current = $stepIndex[$f->status] ?? -1;
    $statusLabel = $f->status === 'cancelled' ? '已取消' : ($steps[$f->status] ?? $f->status);
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">履约单 #{{ $f->id }}　
            <span class="badge bg-{{ $f->status === 'cancelled' ? 'secondary' : ($f->status === 'shipped' ? 'success' : 'primary') }}">{{ $statusLabel }}</span>
        </h4>
        <div class="text-muted small">
            系统单号 <span class="font-monospace">{{ $order->system_order_no ?? '-' }}</span>
            ／ 仓库 {{ $warehouse ? $warehouse->code.' '.$warehouse->name : '-' }}
            ／ 拣货人 {{ $assignee ?: '未指派' }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('soho.order.fulfillment') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 返回列表
        </a>
    </div>
</div>

{{-- 进度条 --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            @foreach($steps as $key => $label)
                @php $idx = $stepIndex[$key]; @endphp
                <div class="text-center flex-fill">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1"
                         style="width:34px;height:34px;{{ $current >= $idx ? 'background:#0d6efd;color:#fff;' : 'background:#e9ecef;color:#6c757d;' }}">
                        <i class="fa {{ $current >= $idx ? 'fa-check' : 'fa-circle-o' }}"></i>
                    </div>
                    <div class="small {{ $current >= $idx ? 'fw-semibold' : 'text-muted' }}">{{ $label }}</div>
                </div>
            @endforeach
        </div>
        @if($f->status === 'cancelled')
            <div class="alert alert-secondary mt-3 mb-0 py-2"><i class="fa fa-ban me-1"></i> 该履约单已取消：{{ $f->remark ?: '无备注' }}</div>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">发货明细</h6>
                <span class="text-muted small">共 {{ $orderItems->count() }} 行 / {{ $orderItems->sum('quantity') }} 件</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>商品 / SKU</th>
                                <th class="text-center">声明数量</th>
                                <th class="text-center">订单数量</th>
                                <th class="text-center">本仓可用</th>
                                <th class="text-center">明细状态</th>
                                <th>运单</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderItems as $item)
                                @php $avail = $item->sku_id ? ($stock[$item->sku_id] ?? 0) : null; @endphp
                                <tr>
                                    <td>
                                        <div>{{ $item->product_name ?: '-' }}</div>
                                        <div class="text-muted small">{{ $item->sku_name ?: '-' }}</div>
                                    </td>
                                    <td class="text-center">{{ $declared[$item->id] ?? '-' }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">
                                        @if($avail === null)
                                            <span class="text-muted">-</span>
                                        @elseif($avail < $item->quantity)
                                            <span class="text-danger fw-semibold">{{ $avail }}</span>
                                        @else
                                            <span class="text-success">{{ $avail }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $isMap = ['pending' => ['待发货', 'warning'], 'shipped' => ['已发货', 'success'], 'fulfilled' => ['已履约', 'success']];
                                            [$il, $ic] = $isMap[$item->fulfillment_status] ?? [$item->fulfillment_status, 'secondary'];
                                        @endphp
                                        <span class="badge bg-{{ $ic }}">{{ $il }}</span>
                                    </td>
                                    <td class="small">
                                        @if($item->waybill_no)
                                            <div class="font-monospace">{{ $item->waybill_no }}</div>
                                            <div class="text-muted">{{ $item->express_company }}</div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4"><i class="fa fa-inbox fa-2x d-block mb-2"></i>该订单没有明细行</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 操作区 --}}
        <div class="card mt-3">
            <div class="card-header"><h6 class="mb-0">执行操作</h6></div>
            <div class="card-body">
                @if($f->status === 'pending')
                    <form method="POST" action="{{ route('soho.order.fulfillment.assign', $f->id) }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label small text-muted">指派拣货人（用户 ID）</label>
                            <input type="number" name="assignee_id" class="form-control" value="{{ $f->assignee_id }}" placeholder="例如 1">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100"><i class="fa fa-user-plus me-1"></i> 指派</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('soho.order.fulfillment.pick', $f->id) }}">
                        @csrf
                        <button class="btn btn-primary"><i class="fa fa-check me-1"></i> 拣货完成</button>
                    </form>
                @elseif($f->status === 'picked')
                    <form method="POST" action="{{ route('soho.order.fulfillment.pack', $f->id) }}">
                        @csrf
                        <button class="btn btn-primary"><i class="fa fa-box me-1"></i> 打包完成（生成包裹）</button>
                    </form>
                @elseif($f->status === 'packed')
                    <form method="POST" action="{{ route('soho.order.fulfillment.ship', $f->id) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label small text-muted">运单号 <span class="text-danger">*</span></label>
                            <input type="text" name="waybill_no" class="form-control" required placeholder="例如 SF1234567890">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">快递公司 <span class="text-danger">*</span></label>
                            <select name="express_company" class="form-select" required>
                                <option value="">请选择</option>
                                @foreach($express as $e)
                                    <option value="{{ $e->name }}">{{ $e->name }}（{{ $e->code }}）</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-success w-100"><i class="fa fa-truck me-1"></i> 发货并扣减库存</button>
                        </div>
                        <div class="col-12">
                            <div class="form-text">发货会在同一事务内完成：订单状态置「已发货」、明细分摊运单、包裹置已发货、按仓库批次 FIFO 扣减可用库存。</div>
                        </div>
                    </form>
                @elseif($f->status === 'shipped')
                    <div class="alert alert-success mb-0 py-2">
                        <i class="fa fa-check-circle me-1"></i> 已发货，库存已扣减。运单号
                        <span class="font-monospace">{{ $order->waybill_no ?? '-' }}</span>
                    </div>
                @else
                    <div class="alert alert-secondary mb-0 py-2"><i class="fa fa-ban me-1"></i> 履约单已取消，无可用操作。</div>
                @endif

                @if(in_array($f->status, ['pending', 'picked', 'packed'], true))
                    <hr>
                    <form method="POST" action="{{ route('soho.order.fulfillment.cancel', $f->id) }}" class="row g-2 align-items-end"
                          onsubmit="return confirm('确认取消该履约单？将同时释放订单库存预留。')">
                        @csrf
                        <div class="col-md-8">
                            <input type="text" name="remark" class="form-control" placeholder="取消原因（可选）">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-danger w-100"><i class="fa fa-ban me-1"></i> 取消履约单</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">收货信息</h6></div>
            <div class="card-body small">
                @if($order)
                    <div class="mb-1"><span class="text-muted">买家：</span>{{ $order->buyer_nick ?: '-' }}</div>
                    <div class="mb-1"><span class="text-muted">收货人：</span>{{ $order->receiver_name ?: '-' }} {{ $order->receiver_phone }}</div>
                    <div class="mb-1"><span class="text-muted">地址：</span>{{ trim(($order->receiver_province ?? '').($order->receiver_city ?? '').($order->receiver_district ?? '').($order->receiver_address ?? '')) ?: '-' }}</div>
                    <div class="mb-1"><span class="text-muted">订单状态：</span>{{ $order->order_status }} ／ 支付 {{ $order->pay_status }}</div>
                    <div><span class="text-muted">实付金额：</span>¥{{ number_format((float) $order->pay_amount, 2) }}</div>
                @else
                    <div class="text-muted">关联订单不存在</div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">包裹</h6></div>
            <div class="card-body small">
                @forelse($packages as $pkg)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="font-monospace">{{ $pkg->package_number }}</div>
                        <div class="text-muted">{{ $pkg->tracking_number ?: '未生成运单' }}</div>
                        <div>状态 <span class="badge bg-secondary">{{ $pkg->status }}</span></div>
                        @if($pkg->shipped_at)<div class="text-muted">发货 {{ $pkg->shipped_at }}</div>@endif
                    </div>
                @empty
                    <div class="text-muted">尚未打包，暂无包裹。</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">订单状态流水</h6></div>
            <div class="card-body small">
                @forelse($statusLogs as $log)
                    <div class="mb-2">
                        <div>{{ $log->from_status ?: '—' }} → <span class="fw-semibold">{{ $log->to_status }}</span></div>
                        <div class="text-muted">{{ $log->created_at }}　{{ $log->source }}</div>
                        @if($log->remark)<div class="text-muted">{{ $log->remark }}</div>@endif
                    </div>
                @empty
                    <div class="text-muted">暂无状态流水。</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
