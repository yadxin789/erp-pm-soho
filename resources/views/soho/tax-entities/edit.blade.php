@extends('soho::layouts.app')

@section('title', '编辑税务主体')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.tax_entities.index') }}">税务主体</a></li>
        <li class="breadcrumb-item active">编辑税务主体</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header"><h5 class="mb-0">编辑税务主体</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.tax_entities.update', $taxEntity) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">主体名称 <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $taxEntity->name) }}" required>
                @if($errors->first('name'))
                    <div class="text-danger small mt-1">{{ $errors->first('name') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">主体类型 <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    <option value="company" @selected($taxEntity->type == 'company')>企业</option>
                    <option value="individual" @selected($taxEntity->type == 'individual')>个人</option>
                    <option value="partnership" @selected($taxEntity->type == 'partnership')>合伙企业</option>
                </select>
                @if($errors->first('type'))
                    <div class="text-danger small mt-1">{{ $errors->first('type') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">税号</label>
                <input type="text" name="tax_no" class="form-control" value="{{ old('tax_no', $taxEntity->tax_no) }}">
                @if($errors->first('tax_no'))
                    <div class="text-danger small mt-1">{{ $errors->first('tax_no') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">税率 (%)</label>
                <input type="number" name="tax_rate" class="form-control" step="0.01" min="0" max="100" value="{{ old('tax_rate', $taxEntity->tax_rate) }}">
                @if($errors->first('tax_rate'))
                    <div class="text-danger small mt-1">{{ $errors->first('tax_rate') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">地址</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $taxEntity->address) }}">
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="status" class="form-check-input" value="1" @checked(old('status', $taxEntity->status))>
                    <label class="form-check-label">启用</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> 保存
            </button>
            <a href="{{ route('soho.tax_entities.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> 返回
            </a>
        </form>
    </div>
</div>
@endsection