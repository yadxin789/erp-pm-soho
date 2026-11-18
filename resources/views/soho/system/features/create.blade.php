@extends('soho::layouts.app')

@section('title', '新增功能开关')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fa fa-plus-circle me-2"></i>新增功能开关</h4>
            <a href="{{ route('soho.system.features.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('soho.system.features.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="feature_key" class="form-label">
                            功能标识 <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('feature_key') is-invalid @enderror"
                               id="feature_key" name="feature_key"
                               value="{{ old('feature_key') }}"
                               placeholder="如：order_sync、auto_fulfillment、stock_alert"
                               required>
                        <div class="form-text">唯一标识，使用小写字母+下划线，创建后不可修改</div>
                        @error('feature_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="feature_name" class="form-label">
                            功能名称 <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('feature_name') is-invalid @enderror"
                               id="feature_name" name="feature_name"
                               value="{{ old('feature_name') }}"
                               placeholder="如：订单自动同步"
                               required>
                        @error('feature_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="module" class="form-label">
                                所属模块 <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('module') is-invalid @enderror" id="module" name="module" required>
                                <option value="">请选择模块</option>
                                @foreach($modules as $value => $label)
                                    <option value="{{ $value }}" {{ old('module') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('module')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled" value="1"
                                       {{ old('is_enabled') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_enabled">立即启用</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">功能描述</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror"
                               id="description" name="description"
                               value="{{ old('description') }}"
                               placeholder="简要描述此功能的用途"
                               maxlength="255">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="config" class="form-label">额外配置（JSON）</label>
                        <textarea class="form-control @error('config') is-invalid @enderror"
                                  id="config" name="config" rows="4"
                                  placeholder='{"interval": 300, "retry_times": 3}'>{{ old('config') }}</textarea>
                        <div class="form-text">可选，JSON格式的额外配置参数</div>
                        @error('config')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('soho.system.features.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-1"></i>取消
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i>保存
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
