@extends('soho::layouts.app')

@section('title', '编辑店铺')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">编辑店铺</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.shop.update', $shop) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">店铺名称 <span class="text-danger">*</span></label>
                <input type="text" name="shop_name" class="form-control" value="{{ old('shop_name', $shop->shop_name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">平台 <span class="text-danger">*</span></label>
                <select name="platform" class="form-select" required>
                    <option value="taobao" @selected($shop->platform == 'taobao')>淘宝</option>
                    <option value="jd" @selected($shop->platform == 'jd')>京东</option>
                    <option value="pinduoduo" @selected($shop->platform == 'pinduoduo')>拼多多</option>
                    <option value="amazon" @selected($shop->platform == 'amazon')>亚马逊</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">店铺编码 <span class="text-danger">*</span></label>
                <input type="text" name="shop_code" class="form-control" value="{{ old('shop_code', $shop->shop_code) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">App Key</label>
                <input type="text" name="app_key" class="form-control" value="{{ old('app_key', $shop->app_key) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">App Secret</label>
                <input type="text" name="app_secret" class="form-control" value="{{ old('app_secret', $shop->app_secret) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Access Token</label>
                <input type="text" name="access_token" class="form-control" value="{{ old('access_token', $shop->access_token) }}">
                <small class="text-muted">留空则保持不变</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Refresh Token</label>
                <input type="text" name="refresh_token" class="form-control" value="{{ old('refresh_token', $shop->refresh_token) }}">
                <small class="text-muted">用于自动刷新 Access Token</small>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="status" class="form-check-input" value="1" @checked(old('status', $shop->status))>
                    <label class="form-check-label">启用</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="{{ route('soho.shop.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection