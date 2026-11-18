@extends('soho::layouts.app')
@section('title', '添加仓库')
@section('content')
<div class="card"><div class="card-header"><h5 class="mb-0">添加仓库</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.warehouse.store') }}">@csrf
            <div class="row">
                <div class="col-md-6"><div class="mb-3"><label class="form-label">名称 <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div></div>
                <div class="col-md-6"><div class="mb-3"><label class="form-label">编码 <span class="text-danger">*</span></label><input type="text" name="code" class="form-control" value="{{ old('code') }}" required></div></div>
            </div>
            <div class="row">
                <div class="col-md-6"><div class="mb-3"><label class="form-label">类型</label><input type="text" name="type" class="form-control" value="{{ old('type') }}"></div></div>
                <div class="col-md-6"><div class="mb-3"><label class="form-label">联系人</label><input type="text" name="contact_name" class="form-control" value="{{ old('contact_name') }}"></div></div>
            </div>
            <div class="row"><div class="col-md-6"><div class="mb-3"><label class="form-label">联系电话</label><input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone') }}"></div></div>
                  <div class="col-md-3"><div class="mb-3"><label class="form-label">国家代码</label><input type="text" name="country_code" class="form-control" value="{{ old('country_code') }}"></div></div>
                  <div class="col-md-3"><div class="mb-3"><label class="form-label">优先级</label><input type="number" name="priority" class="form-control" value="{{ old('priority', 0) }}"></div></div></div>
              <div class="mb-3"><label class="form-label">地址</label><input type="text" name="address" class="form-control" value="{{ old('address') }}"></div>
            <div class="mb-3"><div class="form-check"><input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', true) ? 'checked' : '' }}><label class="form-check-label">启用</label></div></div>
            <button type="submit" class="btn btn-primary">创建</button><a href="{{ route('soho.warehouse.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection