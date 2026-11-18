@extends('soho::layouts.app')

@section('title', '编辑调价申请')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('price.price-change-requests.index') }}">调价申请</a></li>
        <li class="breadcrumb-item active">编辑申请</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-edit me-2"></i>编辑调价申请</h4>
    <a href="{{ route('price.price-change-requests.show', $request) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i>返回详情
    </a>
</div>

@if($request->status !== 'pending')
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle me-2"></i>
        当前申请状态为「{{ $statusLabels[$request->status] ?? $request->status }}」，仅待审批状态可编辑。
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('price.price-change-requests.update', $request) }}" @if($request->status !== 'pending') style="pointer-events: none; opacity: 0.6;" @endif>
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">商品</label>
                        <input type="text" class="form-control" value="{{ $request->product->name ?? '-' }}" readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku', $request->sku) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">当前价格 (¥)</label>
                            <input type="number" step="0.01" name="current_price" class="form-control" value="{{ old('current_price', $request->current_price) }}" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">申请价格 (¥) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="requested_price" class="form-control" value="{{ old('requested_price', $request->requested_price) }}" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">价格变动</label>
                            <div class="form-control bg-light">
                                @php
                                    $changePercent = $request->current_price > 0 ? (($request->requested_price - $request->current_price) / $request->current_price) * 100 : 0;
                                @endphp
                                @if($changePercent > 0)
                                    <span class="text-success fw-semibold">↑ +{{ number_format($changePercent, 2) }}%</span>
                                @elseif($changePercent < 0)
                                    <span class="text-danger fw-semibold">↓ {{ number_format($changePercent, 2) }}%</span>
                                @else
                                    <span class="text-muted">0%</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">调价原因 <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select" required>
                            <option value="">请选择原因</option>
                            @foreach($reasonOptions ?? ['成本调整', '市场竞争', '促销活动', '清库存', '策略调整', '其他'] as $reason)
                                <option value="{{ $reason }}" {{ old('reason', $request->reason) == $reason ? 'selected' : '' }}>{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="3">{{ old('remark', $request->remark) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('price.price-change-requests.show', $request) }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-1"></i>取消
                        </a>
                        <button type="submit" class="btn btn-primary" @if($request->status !== 'pending') disabled @endif>
                            <i class="fa fa-save me-1"></i>保存修改
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection