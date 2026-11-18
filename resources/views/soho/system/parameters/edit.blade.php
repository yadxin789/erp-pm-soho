@extends('soho::layouts.app')

@section('title', '编辑系统参数')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fa fa-edit me-2"></i>编辑系统参数</h4>
            <a href="{{ route('soho.system.parameters.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('soho.system.parameters.update', $parameter) }}">
                    @csrf
                    @method('PUT')

                    {{-- 参数键（只读，不可修改） --}}
                    <div class="mb-3">
                        <label for="param_key" class="form-label">参数键</label>
                        <input type="text" class="form-control-plaintext" id="param_key"
                               value="{{ $parameter->param_key }}" readonly>
                        <div class="form-text"><i class="fa fa-lock me-1 text-warning"></i>参数键为唯一标识，创建后不可修改</div>
                    </div>

                    {{-- 参数名称 --}}
                    <div class="mb-3">
                        <label for="param_name" class="form-label">
                            参数名称 <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('param_name') is-invalid @enderror"
                               id="param_name" name="param_name"
                               value="{{ old('param_name', $parameter->param_name) }}"
                               required>
                        @error('param_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 参数值 --}}
                    <div class="mb-3">
                        <label for="param_value" class="form-label">
                            参数值
                            @if($parameter->is_encrypted)
                                <span class="badge bg-warning text-dark ms-1"><i class="fa fa-lock me-1"></i>加密字段</span>
                            @endif
                        </label>
                        <textarea class="form-control @error('param_value') is-invalid @enderror"
                                  id="param_value" name="param_value" rows="3"
                                  placeholder="输入参数值">{{ old('param_value', $parameter->param_value) }}</textarea>
                        @if($parameter->is_encrypted)
                            <div class="form-text">
                                <i class="fa fa-shield me-1 text-warning"></i>
                                此参数为加密字段，当前显示解密后的值。保存后将重新加密存储
                            </div>
                        @else
                            <div class="form-text">
                                <i class="fa fa-info-circle me-1"></i>
                                如果下方勾选了「加密存储」，此值将自动加密后保存
                            </div>
                        @endif
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
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" {{ old('category', $parameter->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                                    <option value="{{ $value }}" {{ old('value_type', $parameter->value_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                               value="{{ old('description', $parameter->description) }}"
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
                                       {{ old('is_encrypted', $parameter->is_encrypted) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_encrypted">
                                    <i class="fa fa-lock me-1"></i>加密存储
                                </label>
                                <div class="form-text">敏感信息（如API密钥）建议勾选</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $parameter->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <i class="fa fa-check-circle me-1"></i>启用
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- 元信息 --}}
                    <div class="alert alert-light border">
                        <div class="row small text-muted">
                            <div class="col-md-6">
                                <i class="fa fa-calendar me-1"></i>创建时间：{{ $parameter->created_at->format('Y-m-d H:i:s') }}
                            </div>
                            <div class="col-md-6">
                                <i class="fa fa-calendar-check me-1"></i>更新时间：{{ $parameter->updated_at->format('Y-m-d H:i:s') }}
                            </div>
                            @if($parameter->lastModifier)
                                <div class="col-md-6 mt-1">
                                    <i class="fa fa-user me-1"></i>最后修改：{{ $parameter->lastModifier->name }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 操作按钮 --}}
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('soho.system.parameters.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-1"></i>取消
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i>保存修改
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
