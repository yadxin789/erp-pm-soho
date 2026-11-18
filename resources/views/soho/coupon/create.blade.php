@extends('soho::layouts.app')

@section('title', '创建优惠券')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.coupon.index') }}">优惠券管理</a></li>
        <li class="breadcrumb-item active">创建优惠券</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header"><h5 class="mb-0">创建优惠券</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.coupon.store') }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">优惠券码（留空自动生成）</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" maxlength="50" placeholder="留空自动生成">
                </div>
                <div class="col-md-3">
                    <label class="form-label">类型 <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="fixed_discount" @selected(old('type') == 'fixed_discount')>固定金额</option>
                        <option value="percentage" @selected(old('type') == 'percentage')>百分比折扣</option>
                        <option value="shipping" @selected(old('type') == 'shipping')>包邮</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">状态</label>
                    <select name="status" class="form-select">
                        <option value="active" @selected(old('status', 'active') == 'active')>启用</option>
                        <option value="inactive" @selected(old('status') == 'inactive')>停用</option>
                        <option value="expired" @selected(old('status') == 'expired')>已过期</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">优惠值 <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="value" class="form-control" value="{{ old('value') }}" required placeholder="金额或百分比">
                    <small class="text-muted">固定金额填金额，百分比填数字（如 20 表示 20%）</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">最低消费金额</label>
                    <input type="number" step="0.01" name="min_amount" class="form-control" value="{{ old('min_amount', 0) }}" placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">总发行量 <span class="text-danger">*</span></label>
                    <input type="number" name="total_quantity" class="form-control" value="{{ old('total_quantity', 1) }}" min="1" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">生效时间 <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">失效时间 <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at') }}" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-check"></i> 创建
            </button>
            <a href="{{ route('soho.coupon.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> 返回
            </a>
        </form>
    </div>
</div>
@endsection