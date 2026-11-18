@extends('soho::layouts.app')

{{-- 页面标题 --}}
@section('title', '新建客户')

@section('content')
<div class="card">
    {{-- 卡片头部 --}}
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fa fa-user-plus me-2"></i>新建客户
        </h5>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('soho.customer.store') }}">
            @csrf

            {{-- 基本信息 --}}
            <h6 class="text-primary border-bottom pb-2 mb-3">
                <i class="fa fa-id-card me-1"></i>基本信息
            </h6>

            <div class="row">
                {{-- 客户姓名 --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">
                            客户姓名 <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name') }}" required
                            placeholder="请输入客户姓名">
                        <div class="form-text">客户在系统中的显示名称</div>
                    </div>
                </div>

                {{-- 客户分组 --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">客户分组</label>
                        <select name="group_id" class="form-select">
                            <option value="">请选择分组</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}"
                                    @selected(old('group_id') == $group->id)>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">可将客户归类到不同分组（如 VIP、普通客户）</div>
                    </div>
                </div>
            </div>

            {{-- 联系方式 --}}
            <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">
                <i class="fa fa-address-book me-1"></i>联系方式
            </h6>

            <div class="row">
                {{-- 邮箱 --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">邮箱</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email') }}"
                                placeholder="example@mail.com">
                        </div>
                        <div class="form-text">作为客户登录账号，需唯一</div>
                    </div>
                </div>

                {{-- 手机号 --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">手机号</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-phone"></i></span>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone') }}"
                                placeholder="请输入手机号">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 账户设置 --}}
            <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">
                <i class="fa fa-lock me-1"></i>账户设置
            </h6>

            <div class="row">
                {{-- 登录密码 --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">登录密码</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-key"></i></span>
                            <input type="password" name="password" class="form-control"
                                placeholder="至少 6 位（可选）"
                                minlength="6">
                        </div>
                        <div class="form-text">如客户无需登录后台可不填写</div>
                    </div>
                </div>

                {{-- 状态 --}}
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">状态</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox"
                                name="status" value="1" id="statusSwitch"
                                {{ old('status', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusSwitch">
                                启用客户账户
                            </label>
                        </div>
                        <div class="form-text">停用后客户将无法登录系统</div>
                    </div>
                </div>
            </div>

            {{-- 备注 --}}
            <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">
                <i class="fa fa-sticky-note me-1"></i>备注信息
            </h6>

            <div class="mb-3">
                <label class="form-label">备注</label>
                <textarea name="notes" class="form-control" rows="3"
                    placeholder="可填写客户偏好、特殊需求等">{{ old('notes') }}</textarea>
            </div>

            {{-- 操作按钮 --}}
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save me-1"></i>创建客户
                </button>
                <a href="{{ route('soho.customer.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i>返回列表
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
