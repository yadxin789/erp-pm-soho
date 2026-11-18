@extends('soho::layouts.app')
@section('title', '编辑包裹')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">编辑包裹</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.package.update', $package) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">快递公司 <span class="text-danger">*</span></label>
                <select name="express_company_id" class="form-select" required>
                    <option value="">请选择快递公司</option>
                    @foreach(\App\Soho\Models\ExpressCompany::where('status', true)->orderBy('sort_order')->get() as $company)
                    <option value="{{ $company->id }}" @selected(old('express_company_id', $package->express_company_id) == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">物流单号 <span class="text-danger">*</span></label>
                <input type="text" name="tracking_no" class="form-control" value="{{ old('tracking_no', $package->tracking_no) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">重量 (kg)</label>
                <input type="number" name="weight" class="form-control" step="0.01" min="0" value="{{ old('weight', $package->weight) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">状态 <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="pending" @selected(old('status', $package->status) == 'pending')>待发货</option>
                    <option value="shipped" @selected(old('status', $package->status) == 'shipped')>已发货</option>
                    <option value="delivered" @selected(old('status', $package->status) == 'delivered')>已送达</option>
                    <option value="cancelled" @selected(old('status', $package->status) == 'cancelled')>已取消</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">备注</label>
                <textarea name="remark" class="form-control" rows="3">{{ old('remark', $package->remark) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> 保存</button>
            <a href="{{ route('soho.package.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left me-1"></i> 返回</a>
        </form>
    </div>
</div>
@endsection