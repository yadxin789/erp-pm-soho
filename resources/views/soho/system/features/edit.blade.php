@extends('soho::layouts.app')

@section('title', '编辑功能开关')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fa fa-edit me-2"></i>编辑功能开关</h4>
            <a href="{{ route('soho.system.features.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('soho.system.features.update', $feature) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">功能标识</label>
                        <input type="text" class="form-control-plaintext" value="{{ $feature->feature_key }}" readonly>
                        <div class="form-text"><i class="fa fa-lock me-1 text-warning"></i>功能标识创建后不可修改</div>
                    </div>

                    <div class="mb-3">
                        <label for="feature_name" class="form-label">
                            功能名称 <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('feature_name') is-invalid @enderror"
                               id="feature_name" name="feature_name"
                               value="{{ old('feature_name', $feature->feature_name) }}"
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
                                @foreach($modules as $value => $label)
                                    <option value="{{ $value }}" {{ old('module', $feature->module) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('module')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled" value="1"
                                       {{ old('is_enabled', $feature->is_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_enabled">启用此功能</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">功能描述</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror"
                               id="description" name="description"
                               value="{{ old('description', $feature->description) }}"
                               maxlength="255">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="config" class="form-label">额外配置（JSON）</label>
                        <textarea class="form-control @error('config') is-invalid @enderror"
                                  id="config" name="config" rows="4">{{ old('config', $feature->config ? json_encode($feature->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        @error('config')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 元信息 --}}
                    <div class="alert alert-light border">
                        <div class="row small text-muted">
                            <div class="col-md-6">
                                <i class="fa fa-calendar me-1"></i>创建时间：{{ $feature->created_at->format('Y-m-d H:i:s') }}
                            </div>
                            <div class="col-md-6">
                                <i class="fa fa-calendar-check me-1"></i>更新时间：{{ $feature->updated_at->format('Y-m-d H:i:s') }}
                            </div>
                            @if($feature->enabler)
                                <div class="col-md-6 mt-1">
                                    <i class="fa fa-user me-1"></i>启用人：{{ $feature->enabler->name }}
                                </div>
                            @endif
                            @if($feature->enabled_at)
                                <div class="col-md-6 mt-1">
                                    <i class="fa fa-clock me-1"></i>启用时间：{{ $feature->enabled_at->format('Y-m-d H:i:s') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('soho.system.features.index') }}" class="btn btn-outline-secondary">
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
