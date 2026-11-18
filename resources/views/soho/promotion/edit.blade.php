@extends('soho::layouts.app')

@section('title', '编辑促销活动')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.promotion.index') }}">促销活动</a></li>
        <li class="breadcrumb-item active">编辑活动</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header"><h5 class="mb-0">编辑促销活动</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.promotion.update', $promotion) }}">
            @csrf @method('PUT')
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">活动名称 <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $promotion->name) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">促销类型 <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="fixed_discount" @selected($promotion->type == 'fixed_discount')>固定金额</option>
                        <option value="percentage" @selected($promotion->type == 'percentage')>百分比折扣</option>
                        <option value="buy_get" @selected($promotion->type == 'buy_get')>买赠</option>
                        <option value="flash_sale" @selected($promotion->type == 'flash_sale')>限时特惠</option>
                        <option value="bundle" @selected($promotion->type == 'bundle')>组合优惠</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">状态</label>
                    <select name="status" class="form-select">
                        <option value="draft" @selected($promotion->status == 'draft')>草稿</option>
                        <option value="active" @selected($promotion->status == 'active')>启用</option>
                        <option value="inactive" @selected($promotion->status == 'inactive')>停用</option>
                        <option value="expired" @selected($promotion->status == 'expired')>已过期</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">优惠值 <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="value" class="form-control" value="{{ old('value', $promotion->value) }}" required>
                    <small class="text-muted">固定金额填金额，百分比填数字（如 20 表示 20%）</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">最低消费金额</label>
                    <input type="number" step="0.01" name="min_amount" class="form-control" value="{{ old('min_amount', $promotion->min_amount ?? 0) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">是否可叠加</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="stackable" value="1" {{ old('stackable', $promotion->stackable) ? 'checked' : '' }}>
                        <label class="form-check-label">可与其他促销叠加使用</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">开始时间 <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at', $promotion->start_at?->format('Y-m-d\TH:i')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">结束时间 <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at', $promotion->end_at?->format('Y-m-d\TH:i')) }}" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> 保存
            </button>
            <a href="{{ route('soho.promotion.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> 返回
            </a>
        </form>
    </div>
</div>
@endsection