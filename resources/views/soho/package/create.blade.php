@extends('soho::layouts.app')
@section('title', '创建包裹')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">创建包裹</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.package.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">选择订单 <span class="text-danger">*</span></label>
                <select name="order_id" class="form-select" required>
                    <option value="">请选择订单</option>
                    @foreach(\App\Soho\Models\PlatformOrder::orderBy('created_at', 'desc')->limit(100)->get() as $order)
                    <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>{{ $order->order_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">快递公司 <span class="text-danger">*</span></label>
                <select name="express_company_id" class="form-select" required>
                    <option value="">请选择快递公司</option>
                    @foreach(\App\Soho\Models\ExpressCompany::where('status', true)->orderBy('sort_order')->get() as $company)
                    <option value="{{ $company->id }}" @selected(old('express_company_id') == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">物流单号 <span class="text-danger">*</span></label>
                <input type="text" name="tracking_no" class="form-control" value="{{ old('tracking_no') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">重量 (kg)</label>
                <input type="number" name="weight" class="form-control" step="0.01" min="0" value="{{ old('weight') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">备注</label>
                <textarea name="remark" class="form-control" rows="3">{{ old('remark') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> 提交</button>
            <a href="{{ route('soho.package.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i> 返回</a>
        </form>
    </div>
</div>
@endsection