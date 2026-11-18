@extends('soho::layouts.app')

@section('title', '新增系统参数')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fa fa-plus-circle me-2"></i>新增系统参数</h4>
            <a href="{{ route('soho.system.parameters.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('soho.system.parameters.store') }}">
                    @csrf

                    {{-- 参数键 --}}
                    <div class="mb-3">
                        <label for="param_key" class="form-label">
                            参数键 <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('param_key') is-invalid @enderror"
                               id="param_key" name="param_key"
                               value="{{ old('param_key') }}"
                               placeholder="如：shopee_api_key、lazada_callback_url"
                               required>
                        <div class="form-text">唯一标识，使用小写字母+下划线，创建后不可修改</div>
                        @error('param_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 参数名称 --}}
                    <div class="mb-3">
                        <label for="param_name" class="form-label">
                            参数名称 <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('param_name') is-invalid @enderror"
                               id="param_name" name="param_name"
                               value="{{ old('param_name') }}"
                               placeholder="如：Shopee API Key"
                               required>
                        @error('param_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 参数值 --}}
                    <div class="mb-3">
                        <label for="param_value" class="form-label">参数值</label>
                        <textarea class="form-control @error('param_value') is-invalid @enderror"
                                  id="param_value" name="param_value" rows="3"
                                  placeholder="输入参数值，如API密钥、回调URL等">{{ old('param_value') }}</textarea>
                        <div class="form-text">
                            <i class="fa fa-info-circle me-1"></i>
                            如果下方勾选了「加密存储」，此值将自动加密后保存到数据库
                        </div>
                        @error('param_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- 分类 --}}
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">
                                分类 <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="">请选择分类</option>
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 参数类型 --}}
                        <div class="col-md-6 mb-3">
                            <label for="value_type" class="form-label">
                                参数类型 <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('value_type') is-invalid @enderror" id="value_type" name="value_type" required>
                                @foreach($valueTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('value_type', 'string') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('value_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- 描述 --}}
                    <div class="mb-3">
                        <label for="description" class="form-label">描述说明</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror"
                               id="description" name="description"
                               value="{{ old('description') }}"
                               placeholder="简要描述此参数的用途"
                               maxlength="255">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 开关选项 --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_encrypted" name="is_encrypted" value="1"
                                       {{ old('is_encrypted') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_encrypted">
                                    <i class="fa fa-lock me-1"></i>加密存储
                                </label>
                                <div class="form-text">敏感信息（如API密钥）建议勾选</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <i class="fa fa-check-circle me-1"></i>启用
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- 操作按钮 --}}
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('soho.system.parameters.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-1"></i>取消
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i>保存参数
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
