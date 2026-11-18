@extends('soho::layouts.app')

@section('title', '添加运费模板')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">添加运费模板</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.shipping-templates.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">模板名称 <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">适用省份</label>
                    <input type="text" name="province" class="form-control" value="{{ old('province') }}" placeholder="如：广东省">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">首重(kg) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="first_weight" class="form-control" value="{{ old('first_weight') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">首费(元) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="first_fee" class="form-control" value="{{ old('first_fee') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">续重(kg) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="additional_weight" class="form-control" value="{{ old('additional_weight') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">续费(元) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="additional_fee" class="form-control" value="{{ old('additional_fee') }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">免费配送金额</label>
                    <input type="number" step="0.01" name="free_shipping_amount" class="form-control" value="{{ old('free_shipping_amount') }}" placeholder="订单满此金额免运费">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">状态</label>
                    <select name="status" class="form-select" required>
                        <option value="active" @selected(old('status') == 'active')>启用</option>
                        <option value="inactive" @selected(old('status') == 'inactive')>禁用</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> 创建
            </button>
            <a href="{{ route('soho.shipping-templates.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection
