@extends('soho::layouts.app')
@section('title', '添加快递公司')
@section('content')
<div class="card"><div class="card-header"><h5 class="mb-0">添加快递公司</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.express.store') }}">@csrf
            <div class="mb-3"><label class="form-label">名称 <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
            <div class="mb-3"><label class="form-label">编码 <span class="text-danger">*</span></label><input type="text" name="code" class="form-control" value="{{ old('code') }}" required></div>
            <div class="mb-3"><label class="form-label">网站</label><input type="text" name="website" class="form-control" value="{{ old('website') }}"></div>
            <div class="mb-3"><label class="form-label">电话</label><input type="text" name="phone" class="form-control" value="{{ old('phone') }}"></div>
            <div class="mb-3"><div class="form-check"><input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', true) ? 'checked' : '' }}><label class="form-check-label">启用</label></div></div>
            <button type="submit" class="btn btn-primary">创建</button><a href="{{ route('soho.express.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection