@extends('soho::layouts.app')

@section('title', '编辑通知模板')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fa fa-edit me-2"></i>编辑通知模板</h4>
            <a href="{{ route('soho.system.notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>返回列表
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('soho.system.notifications.update', $notification) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">模板编码</label>
                            <input type="text" class="form-control-plaintext" value="{{ $notification->template_code }}" readonly>
                            <div class="form-text"><i class="fa fa-lock me-1 text-warning"></i>创建后不可修改</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="template_name" class="form-label">模板名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('template_name') is-invalid @enderror"
                                   id="template_name" name="template_name"
                                   value="{{ old('template_name', $notification->template_name) }}" required>
                            @error('template_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="channel" class="form-label">通知渠道 <span class="text-danger">*</span></label>
                            <select class="form-select @error('channel') is-invalid @enderror" id="channel" name="channel" required>
                                @foreach($channels as $value => $label)
                                    <option value="{{ $value }}" {{ old('channel', $notification->channel) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('channel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="business_type" class="form-label">业务类型 <span class="text-danger">*</span></label>
                            <select class="form-select @error('business_type') is-invalid @enderror" id="business_type" name="business_type" required>
                                @foreach($businessTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('business_type', $notification->business_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('business_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $notification->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">启用</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">模板标题</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror"
                               id="subject" name="subject" value="{{ old('subject', $notification->subject) }}">
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">模板内容 <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="6" required>{{ old('content', $notification->content) }}</textarea>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="available_variables" class="form-label">可用变量列表</label>
                        <textarea class="form-control @error('available_variables') is-invalid @enderror"
                                  id="available_variables" name="available_variables" rows="4">{{ old('available_variables', $notification->available_variables ? implode("\n", $notification->available_variables) : '') }}</textarea>
                        @error('available_variables')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="alert alert-light border">
                        <div class="row small text-muted">
                            <div class="col-md-6"><i class="fa fa-calendar me-1"></i>创建：{{ $notification->created_at->format('Y-m-d H:i:s') }}</div>
                            <div class="col-md-6"><i class="fa fa-calendar-check me-1"></i>更新：{{ $notification->updated_at->format('Y-m-d H:i:s') }}</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('soho.system.notifications.index') }}" class="btn btn-outline-secondary">取消</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>保存修改</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
