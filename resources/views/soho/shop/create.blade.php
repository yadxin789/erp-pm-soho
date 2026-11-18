@extends('soho::layouts.app')

@section('title', '添加店铺')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">添加店铺</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.shop.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">店铺名称 <span class="text-danger">*</span></label>
                <input type="text" name="shop_name" class="form-control" value="{{ old('shop_name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">平台 <span class="text-danger">*</span></label>
                <select name="platform" class="form-select" required>
                    <option value="">选择平台</option>
                    <option value="taobao">淘宝</option>
                    <option value="jd">京东</option>
                    <option value="pinduoduo">拼多多</option>
                    <option value="amazon">亚马逊</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">店铺编码 <span class="text-danger">*</span></label>
                <input type="text" name="shop_code" class="form-control" value="{{ old('shop_code') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">App Key</label>
                <input type="text" name="app_key" class="form-control" value="{{ old('app_key') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">App Secret</label>
                <input type="text" name="app_secret" class="form-control" value="{{ old('app_secret') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Access Token</label>
                <input type="text" name="access_token" class="form-control" value="{{ old('access_token') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Refresh Token</label>
                <input type="text" name="refresh_token" class="form-control" value="{{ old('refresh_token') }}">
            </div>
            <div class="mb-3">
                <div class="mb-3">
                    <label class="form-label">店铺地址</label>
                    <input type="text" name="shop_url" class="form-control" value="{{ old('shop_url') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">备注</label>
                    <textarea name="remark" class="form-control">{{ old('remark') }}</textarea>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="sync_enabled" class="form-check-input" value="1" @checked(old('sync_enabled', true))>
                        <label class="form-check-label">开启库存同步</label>
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="status" class="form-check-input" value="1" @checked(old('status', true))>
                    <label class="form-check-label">启用</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">创建</button>
            <a href="{{ route('soho.shop.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>
@endsection