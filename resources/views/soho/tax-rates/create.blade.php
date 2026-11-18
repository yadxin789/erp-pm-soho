@extends('soho::layouts.app')

@section('title', '添加税率')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">添加税率</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.tax-rate.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">税率名称 <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">国家/地区代码 <span class="text-danger">*</span></label>
                    <input type="text" name="country_code" class="form-control" value="{{ old('country_code') }}" required maxlength="10">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">省份</label>
                    <input type="text" name="province" class="form-control" value="{{ old('province') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">税率(%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="rate" class="form-control" value="{{ old('rate') }}" required min="0" max="100">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">优先级</label>
                    <input type="number" name="priority" class="form-control" value="{{ old('priority', 0) }}" min="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">状态</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="status" value="1" {{ old('status', true) ? 'checked' : '' }}>
                        <label class="form-check-label">启用</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_compound" value="1" {{ old('is_compound') ? 'checked' : '' }}>
                        <label class="form-check-label">复合税率</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="included_in_price" value="1" {{ old('included_in_price') ? 'checked' : '' }}>
                        <label class="form-check-label">含税价</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> 创建
            </button>
            <a href="{{ route('soho.tax-rate.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection
