@extends('soho::layouts.app')

@section('title', '新增通知模板')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fa fa-plus-circle me-2"></i>新增通知模板</h4>
            <a href="{{ route('soho.system.notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('soho.system.notifications.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="template_code" class="form-label">模板编码 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('template_code') is-invalid @enderror"
                                   id="template_code" name="template_code" value="{{ old('template_code') }}"
                                   placeholder="如：order_created_notify" required>
                            <div class="form-text">唯一标识，创建后不可修改</div>
                            @error('template_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="template_name" class="form-label">模板名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('template_name') is-invalid @enderror"
                                   id="template_name" name="template_name" value="{{ old('template_name') }}"
                                   placeholder="如：订单创建通知" required>
                            @error('template_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="channel" class="form-label">通知渠道 <span class="text-danger">*</span></label>
                            <select class="form-select @error('channel') is-invalid @enderror" id="channel" name="channel" required>
                                @foreach($channels as $value => $label)
                                    <option value="{{ $value }}" {{ old('channel') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('channel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="business_type" class="form-label">业务类型 <span class="text-danger">*</span></label>
                            <select class="form-select @error('business_type') is-invalid @enderror" id="business_type" name="business_type" required>
                                @foreach($businessTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('business_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('business_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">启用</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">模板标题</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror"
                               id="subject" name="subject" value="{{ old('subject') }}"
                               placeholder="支持变量，如：订单 &#123;&#123;order_no&#125;&#125; 已创建">
                        <div class="form-text">邮件/系统消息的标题，短信可不填</div>
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">模板内容 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="6" required
                                  placeholder="支持变量占位符，如：尊敬的客户，您的订单 &#123;&#123;order_no&#125;&#125; 已创建，金额 &#123;&#123;amount&#125;&#125; 元。">{{ old('content') }}</textarea>
                        <div class="form-text">使用 &#123;&#123;变量名&#125;&#125; 格式插入动态内容</div>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="available_variables" class="form-label">可用变量列表</label>
                        <textarea class="form-control @error('available_variables') is-invalid @enderror"
                                  id="available_variables" name="available_variables" rows="4"
                                  placeholder="每行一个变量名，如：&#10;order_no&#10;amount&#10;customer_name">{{ old('available_variables') }}</textarea>
                        <div class="form-text">列出此模板中可用的变量名，每行一个</div>
                        @error('available_variables')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('soho.system.notifications.index') }}" class="btn btn-outline-secondary">取消</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
