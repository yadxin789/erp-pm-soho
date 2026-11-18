@extends('soho::layouts.app')

@section('title', '创建优惠券')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">创建优惠券</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.coupon.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <!-- 券名称 -->
                    <div class="mb-3">
                        <label class="form-label">券名称 <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="如：新春满100减20券">
                    </div>
                    <!-- 券编码 -->
                    <div class="mb-3">
                        <label class="form-label">券编码 <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" required placeholder="唯一标识，如：CNY2024">
                        <div class="form-text">可用于手动输入领取，留空则自动生成</div>
                    </div>
                    <!-- 券类型 -->
                    <div class="mb-3">
                        <label class="form-label">券类型 <span class="text-danger">*</span></label>
                        <select name="type" id="couponType" class="form-select" required>
                            <option value="">请选择类型</option>
                            <option value="fixed" @selected(old('type') == 'fixed')>固定金额券</option>
                            <option value="percent" @selected(old('type') == 'percent')>百分比折扣券</option>
                            <option value="shipping" @selected(old('type') == 'shipping')>包邮券</option>
                        </select>
                    </div>
                    <!-- 面值 -->
                    <div class="mb-3">
                        <label class="form-label">面值 <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" id="valueUnit">¥</span>
                            <input type="number" step="0.01" name="value" id="couponValue" class="form-control" value="{{ old('value') }}" required placeholder="根据类型填写金额或百分比">
                            <span class="input-group-text" id="valueSuffix">.00</span>
                        </div>
                    </div>
                    <!-- 最低消费金额 -->
                    <div class="mb-3">
                        <label class="form-label">最低消费金额</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" step="0.01" name="min_amount" class="form-control" value="{{ old('min_amount', 0) }}" placeholder="0 表示无门槛">
                        </div>
                        <div class="form-text">填写 0 表示无使用门槛</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- 开始日期 -->
                    <div class="mb-3">
                        <label class="form-label">有效期开始 <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                    </div>
                    <!-- 结束日期 -->
                    <div class="mb-3">
                        <label class="form-label">有效期结束 <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                    </div>
                    <!-- 发放总量 -->
                    <div class="mb-3">
                        <label class="form-label">发放总量</label>
                        <input type="number" name="total" class="form-control" value="{{ old('total') }}" placeholder="留空表示不限量">
                        <div class="form-text">留空表示不限数量</div>
                    </div>
                    <!-- 每人限领 -->
                    <div class="mb-3">
                        <label class="form-label">每人限领数量</label>
                        <input type="number" name="limit_per_user" class="form-control" value="{{ old('limit_per_user', 1) }}" min="1">
                    </div>
                    <!-- 状态 -->
                    <div class="mb-3">
                        <label class="form-label">状态</label>
                        <select name="status" class="form-select">
                            <option value="enabled" @selected(old('status', 'enabled') == 'enabled')>启用</option>
                            <option value="disabled" @selected(old('status') == 'disabled')>禁用</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- 使用说明 -->
            <div class="mb-3">
                <label class="form-label">使用说明</label>
                <textarea name="description" class="form-control" rows="3" placeholder="请输入优惠券使用说明">{{ old('description') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">创建</button>
            <a href="{{ route('soho.coupon.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('couponType');
    var valueInput = document.getElementById('couponValue');
    var valueUnit = document.getElementById('valueUnit');
    var valueSuffix = document.getElementById('valueSuffix');

    function updateValueUnit() {
        var type = typeSelect.value;
        if (type === 'fixed') {
            valueUnit.textContent = '¥';
            valueSuffix.textContent = '.00';
            valueInput.placeholder = '如 20 表示减20元';
        } else if (type === 'percent') {
            valueUnit.textContent = '';
            valueSuffix.textContent = '%';
            valueInput.placeholder = '如 20 表示减20%';
        } else if (type === 'shipping') {
            valueUnit.textContent = '';
            valueSuffix.textContent = '';
            valueInput.placeholder = '包邮券无需填写面值，填 0 即可';
        } else {
            valueUnit.textContent = '¥';
            valueSuffix.textContent = '.00';
            valueInput.placeholder = '根据类型填写金额或百分比';
        }
    }

    typeSelect.addEventListener('change', updateValueUnit);
    updateValueUnit();
});
</script>
@endsection