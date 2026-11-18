@extends('soho::layouts.app')

@section('title', '添加货币')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">添加货币</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.currencies.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">货币代码 <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" required maxlength="3">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">货币名称 <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">符号</label>
                    <input type="text" name="symbol" class="form-control" value="{{ old('symbol') }}" placeholder="如：$、€、¥">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">汇率(兑人民币) <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" name="rate" class="form-control" value="{{ old('rate') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">小数位数</label>
                    <input type="number" name="decimal_places" class="form-control" value="{{ old('decimal_places', 2) }}" min="0" max="4">
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="status" value="1" {{ old('status', true) ? 'checked' : '' }}>
                    <label class="form-check-label">启用</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> 创建
            </button>
            <a href="{{ route('soho.currencies.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection
