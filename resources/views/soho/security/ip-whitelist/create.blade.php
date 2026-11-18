@extends('soho::layouts.app')

@section('title', '新增 IP 规则')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-plus me-2"></i>新增 IP 规则</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('soho.security.ip-whitelist.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ip_address" class="form-label">IP地址/网段 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ip_address') is-invalid @enderror"
                                   id="ip_address" name="ip_address" value="{{ old('ip_address') }}" required
                                   placeholder="如：192.168.1.1 或 192.168.1.0/24">
                            <div class="form-text">支持单IP或CIDR格式网段</div>
                            @error('ip_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">类型 <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                @foreach($typeLabels as $value => $label)
                                    <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="scope" class="form-label">适用范围 <span class="text-danger">*</span></label>
                            <select class="form-select" id="scope" name="scope" required>
                                @foreach($scopeLabels as $value => $label)
                                    <option value="{{ $value }}" {{ old('scope') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">状态</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">启用</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">描述说明</label>
                            <input type="text" class="form-control" id="description" name="description"
                                   value="{{ old('description') }}" placeholder="规则说明">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('security.ip-whitelist') }}" class="btn btn-outline-secondary">取消</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection