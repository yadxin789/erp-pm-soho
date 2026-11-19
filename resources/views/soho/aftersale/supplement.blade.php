@extends('soho::layouts.app')

@section('title', '待补充资料')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small mb-1">缺少说明的售后单</div>
            <div class="fs-4 fw-semibold text-warning">{{ $missing }}</div>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small mb-1">退款金额为 0 的售后单</div>
            <div class="fs-4 fw-semibold text-danger">{{ $noAmount }}</div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待补充资料</h5>
        <a href="{{ route('soho.aftersale.apply') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 售后申请
        </a>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted mb-3">
            售后单缺少说明或退款金额为 0 时无法继续处理，请在这里补录资料。
        </div>

        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索售后单号 / 买家 / 订单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.aftersale.supplement') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        @forelse($rows as $row)
            <div class="border rounded-3 mb-3">
                <div class="px-3 py-2 bg-light rounded-top d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <span class="font-monospace fw-semibold">{{ $row->after_sale_no }}</span>
                        <span class="text-muted small ms-2">订单 {{ $row->system_order_no ?: '-' }}</span>
                        <span class="text-muted small ms-2">买家 {{ $row->buyer_nick ?: '-' }}</span>
                        <span class="text-muted small ms-2">原因 {{ $row->reason ?: '-' }}</span>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        @if(! $row->description)
                            <span class="badge bg-warning text-dark">缺少说明</span>
                        @endif
                        @if((float) $row->refund_amount <= 0)
                            <span class="badge bg-danger">金额为 0</span>
                        @endif
                        <span class="badge bg-light text-dark border">¥{{ number_format((float) $row->refund_amount, 2) }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('soho.aftersale.supplement.save') }}" class="p-3">
                    @csrf
                    <input type="hidden" name="after_sale_id" value="{{ $row->id }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label small text-muted mb-1">补充说明</label>
                            <input type="text" name="description" class="form-control" value="{{ $row->description }}" placeholder="说明问题、凭证、处理意见" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">退款金额</label>
                            <input type="number" step="0.01" min="0" name="refund_amount" class="form-control" value="{{ (float) $row->refund_amount }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">保存</button>
                        </div>
                    </div>
                </form>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fa fa-circle-check text-success fs-3 d-block mb-2"></i>
                所有售后单资料齐全，无需补充。
            </div>
        @endforelse

        {{ $rows->links() }}
    </div>
</div>
@endsection
