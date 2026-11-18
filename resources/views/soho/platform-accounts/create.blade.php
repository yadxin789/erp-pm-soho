@extends('soho::layouts.app')

@section('title', '创建平台账号')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.platform_accounts.index') }}">平台账号</a></li>
        <li class="breadcrumb-item active">创建账号</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header"><h5 class="mb-0">创建平台账号</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.platform_accounts.store') }}">
            @csrf
            <!-- 平台信息 -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">平台编码 <span class="text-danger">*</span></label>
                    <select name="platform_code" class="form-select" required>
                        <option value="">选择平台</option>
                        <option value="taobao" @selected(old('platform_code') == 'taobao')>淘宝</option>
                        <option value="jd" @selected(old('platform_code') == 'jd')>京东</option>
                        <option value="pinduoduo" @selected(old('platform_code') == 'pinduoduo')>拼多多</option>
                        <option value="amazon" @selected(old('platform_code') == 'amazon')>亚马逊</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">平台名称 <span class="text-danger">*</span></label>
                    <input type="text" name="platform_name" class="form-control" value="{{ old('platform_name') }}" required>
                </div>
            </div>

            <!-- 账号信息 -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">账号名称 <span class="text-danger">*</span></label>
                    <input type="text" name="account_name" class="form-control" value="{{ old('account_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">账号类型 <span class="text-danger">*</span></label>
                    <select name="account_type" class="form-select" required>
                        <option value="developer" @selected(old('account_type') == 'developer')>开发者账号</option>
                        <option value="seller" @selected(old('account_type') == 'seller')>卖家主体</option>
                        <option value="agency" @selected(old('account_type') == 'agency')>代理账号</option>
                    </select>
                </div>
            </div>

            <!-- 开发者凭证 -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">开发者ID / AppKey</label>
                    <input type="text" name="developer_id" class="form-control" value="{{ old('developer_id') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">App Secret</label>
                    <input type="text" name="app_secret" class="form-control" value="{{ old('app_secret') }}">
                </div>
            </div>

            <!-- 卖家主体信息 -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">卖家主体ID</label>
                    <input type="text" name="seller_id" class="form-control" value="{{ old('seller_id') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">卖家主体名称</label>
                    <input type="text" name="seller_name" class="form-control" value="{{ old('seller_name') }}">
                </div>
            </div>

            <!-- 站点/市场信息 -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">站点/市场编码</label>
                    <input type="text" name="marketplace_id" class="form-control" value="{{ old('marketplace_id') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">站点/市场名称</label>
                    <input type="text" name="marketplace_name" class="form-control" value="{{ old('marketplace_name') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">国家/地区编码</label>
                    <input type="text" name="country_code" class="form-control" value="{{ old('country_code') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">国家/地区名称</label>
                    <input type="text" name="country_name" class="form-control" value="{{ old('country_name') }}">
                </div>
            </div>

            <!-- 状态 -->
            <div class="mb-3">
                <label class="form-label">状态</label>
                <select name="status" class="form-select">
                    <option value="active" @selected(old('status', 'active') == 'active')>启用</option>
                    <option value="inactive" @selected(old('status') == 'inactive')>禁用</option>
                    <option value="suspended" @selected(old('status') == 'suspended')>已暂停</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-check"></i> 创建
            </button>
            <a href="{{ route('soho.platform_accounts.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> 返回
            </a>
        </form>
    </div>
</div>
@endsection