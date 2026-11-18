@extends('soho::layouts.app')
@section('title', '编辑快递公司')
@section('content')
<div class="card"><div class="card-header"><h5 class="mb-0">编辑快递公司</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.express.update', $express) }}">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label">名称 <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $express->name) }}" required></div>
            <div class="mb-3"><label class="form-label">编码 <span class="text-danger">*</span></label><input type="text" name="code" class="form-control" value="{{ old('code', $express->code) }}" required></div>
            <div class="mb-3"><label class="form-label">网站</label><input type="text" name="website" class="form-control" value="{{ old('website', $express->website) }}"></div>
            <div class="mb-3"><label class="form-label">电话</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $express->phone) }}"></div>
            <div class="row"><div class="col-md-3"><div class="mb-3"><label class="form-label">淘宝编码</label><input type="text" name="taobao_code" class="form-control" value="{{ old('taobao_code', $express->taobao_code) }}"></div></div>
                <div class="col-md-3"><div class="mb-3"><label class="form-label">京东编码</label><input type="text" name="jd_code" class="form-control" value="{{ old('jd_code', $express->jd_code) }}"></div></div>
                <div class="col-md-3"><div class="mb-3"><label class="form-label">拼多多编码</label><input type="text" name="pdd_code" class="form-control" value="{{ old('pdd_code', $express->pdd_code) }}"></div></div>
                <div class="col-md-3"><div class="mb-3"><label class="form-label">亚马逊编码</label><input type="text" name="amazon_code" class="form-control" value="{{ old('amazon_code', $express->amazon_code) }}"></div></div></div>
            <div class="mb-3"><label class="form-label">排序</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $express->sort_order) }}"></div>
            <div class="mb-3"><div class="form-check"><input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $express->status) ? 'checked' : '' }}><label class="form-check-label">启用</label></div></div>
            <button type="submit" class="btn btn-primary">保存</button><a href="{{ route('soho.express.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection