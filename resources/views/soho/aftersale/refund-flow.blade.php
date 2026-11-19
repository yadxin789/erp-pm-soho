@extends('soho::layouts.app')

@section('title', '退款单 '.$r->refund_no)

@section('content')
@php
    $steps = ['pending' => '待审核', 'processing' => '退款中', 'completed' => '已退款'];
    $idxMap = ['pending' => 0, 'processing' => 1, 'completed' => 2];
    $current = $idxMap[$r->refund_status] ?? -1;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">{{ $r->refund_no }}
            <span class="badge bg-{{ $r->refund_status === 'completed' ? 'success' : ($r->refund_status === 'failed' ? 'danger' : ($r->refund_status === 'processing' ? 'info' : 'warning')) }}">
                {{ $r->refund_status === 'failed' ? '失败/驳回' : ($steps[$r->refund_status] ?? $r->refund_status) }}
            </span>
        </h4>
        <div class="text-muted small">
            退款金额 <span class="fw-semibold text-dark">¥{{ number_format((float) $r->refund_amount, 2) }}</span>
            ／ 类型 {{ $r->refund_type === 'return_refund' ? '退货退款' : '仅退款' }}
            ／ 售后单 {{ $afterSale->after_sale_no ?? '-' }}
        </div>
    </div>
    <a href="{{ route('soho.refund.records') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i> 返回台账
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            @foreach($steps as $key => $label)
                <div class="text-center flex-fill">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1"
                         style="width:34px;height:34px;{{ $current >= $idxMap[$key] ? 'background:#0d6efd;color:#fff;' : 'background:#e9ecef;color:#6c757d;' }}">
                        <i class="fa {{ $current >= $idxMap[$key] ? 'fa-check' : 'fa-circle-o' }}"></i>
                    </div>
                    <div class="small {{ $current >= $idxMap[$key] ? 'fw-semibold' : 'text-muted' }}">{{ $label }}</div>
                </div>
            @endforeach
        </div>
        @if($r->refund_status === 'failed')
            <div class="alert alert-danger mt-2 mb-0 py-2"><i class="fa fa-times-circle me-1"></i> 失败原因：{{ $r->failed_reason ?: '未填写' }}</div>
        @elseif($r->refund_status === 'completed')
            <div class="alert alert-success mt-2 mb-0 py-2">
                <i class="fa fa-check-circle me-1"></i> 退款已完成，平台退款单号 <span class="font-monospace">{{ $r->platform_refund_no }}</span>
            </div>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">售后明细</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SKU</th>
                                <th class="text-center">数量</th>
                                <th class="text-center">退款数量</th>
                                <th class="text-center">退货数量</th>
                                <th class="text-end">退款金额</th>
                                <th class="text-center">明细状态</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $i)
                                <tr>
                                    <td class="font-monospace small">SKU#{{ $i->sku_id }}</td>
                                    <td class="text-center">{{ $i->quantity }}</td>
                                    <td class="text-center">{{ $i->refund_quantity }}</td>
                                    <td class="text-center">{{ $i->return_quantity }}</td>
                                    <td class="text-end">¥{{ number_format((float) $i->refund_amount, 2) }}</td>
                                    <td class="text-center"><span class="badge bg-secondary">{{ $i->refund_status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4"><i class="fa fa-inbox fa-2x d-block mb-2"></i>该售后单没有明细行</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">退款操作</h6></div>
            <div class="card-body">
                @if($r->refund_status === 'pending')
                    <div class="d-flex gap-2 mb-3">
                        <form method="POST" action="{{ route('soho.refund.approve', $r->id) }}">
                            @csrf
                            <button class="btn btn-primary"><i class="fa fa-check me-1"></i> 审核通过</button>
                        </form>
                        <form method="POST" action="{{ route('soho.refund.reject', $r->id) }}" class="row g-2 align-items-center flex-fill"
                              onsubmit="return confirm('确认驳回该退款单？')">
                            @csrf
                            <div class="col">
                                <input type="text" name="failed_reason" class="form-control" required placeholder="驳回原因（必填）">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-outline-danger"><i class="fa fa-times me-1"></i> 驳回</button>
                            </div>
                        </form>
                    </div>
                    <div class="form-text">审核通过后进入「退款中」，再由财务执行退款。</div>
                @elseif($r->refund_status === 'processing')
                    <div class="d-flex gap-2 align-items-center">
                        <form method="POST" action="{{ route('soho.refund.execute', $r->id) }}"
                              onsubmit="return confirm('确认执行退款？将写入退款流水并回写售后单。')">
                            @csrf
                            <button class="btn btn-success"><i class="fa fa-money me-1"></i> 执行退款</button>
                        </form>
                        <form method="POST" action="{{ route('soho.refund.reject', $r->id) }}" class="row g-2 align-items-center flex-fill">
                            @csrf
                            <div class="col">
                                <input type="text" name="failed_reason" class="form-control" required placeholder="失败原因（必填）">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-outline-danger">标记失败</button>
                            </div>
                        </form>
                    </div>
                @elseif($r->refund_status === 'completed')
                    <div class="alert alert-success mb-0 py-2"><i class="fa fa-check me-1"></i> 已完成，无需继续操作。</div>
                @else
                    <div class="alert alert-secondary mb-0 py-2"><i class="fa fa-ban me-1"></i> 已驳回，无可用操作。</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">订单信息</h6></div>
            <div class="card-body small">
                @if($order)
                    <div class="mb-1"><span class="text-muted">系统单号：</span><span class="font-monospace">{{ $order->system_order_no }}</span></div>
                    <div class="mb-1"><span class="text-muted">买家：</span>{{ $order->buyer_nick ?: '-' }}</div>
                    <div class="mb-1"><span class="text-muted">订单状态：</span>{{ $order->order_status }} ／ 支付 {{ $order->pay_status }}</div>
                    <div><span class="text-muted">实付金额：</span>¥{{ number_format((float) $order->pay_amount, 2) }}</div>
                @else
                    <div class="text-muted">关联订单不存在</div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">支付记录</h6></div>
            <div class="card-body small">
                @forelse($payments as $p)
                    <div class="border-bottom pb-2 mb-2">
                        <div>¥{{ number_format((float) $p->amount, 2) }}　<span class="badge bg-secondary">{{ $p->payment_status }}</span></div>
                        <div class="text-muted">{{ $p->payment_method }}　{{ $p->paid_at }}</div>
                        <div class="font-monospace text-muted">{{ $p->transaction_no }}</div>
                    </div>
                @empty
                    <div class="text-muted">暂无支付记录。</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">退款流水</h6></div>
            <div class="card-body small">
                @forelse($flows as $f)
                    <div class="border-bottom pb-2 mb-2">
                        <div>¥{{ number_format((float) $f->amount, 2) }}　<span class="badge bg-success">{{ $f->status }}</span></div>
                        <div class="text-muted">{{ $f->order_no }}　{{ $f->customer_name }}</div>
                        <div class="text-muted">{{ $f->created_at }}</div>
                    </div>
                @empty
                    <div class="text-muted">尚未登记退款流水（执行退款后生成）。</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
