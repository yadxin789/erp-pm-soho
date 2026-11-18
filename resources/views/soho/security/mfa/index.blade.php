@extends('soho::layouts.app')

@section('title', '二次验证策略')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-shield-alt me-2"></i>二次验证策略配置</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('soho.security.mfa.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="mfa_enabled" name="mfa_enabled" value="1"
                                   {{ $config['mfa_enabled'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="mfa_enabled">启用二次验证（MFA）</label>
                        </div>
                        <div class="form-text">开启后，用户登录时需进行二次验证</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">强制验证角色</label>
                        <div class="border rounded p-3">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="role_super_admin" name="mfa_required_roles[]" value="super_admin"
                                       {{ in_array('super_admin', $config['mfa_required_roles'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_super_admin">超级管理员</label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="role_tenant_admin" name="mfa_required_roles[]" value="tenant_admin"
                                       {{ in_array('tenant_admin', $config['mfa_required_roles'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_tenant_admin">租户管理员</label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="role_security_admin" name="mfa_required_roles[]" value="security_admin"
                                       {{ in_array('security_admin', $config['mfa_required_roles'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_security_admin">安全管理员</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="role_finance_manager" name="mfa_required_roles[]" value="finance_manager"
                                       {{ in_array('finance_manager', $config['mfa_required_roles'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_finance_manager">财务主管</label>
                            </div>
                        </div>
                        <div class="form-text">选中角色的用户必须完成二次验证才能登录</div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="mfa_code_length" class="form-label">验证码长度</label>
                            <select class="form-select" id="mfa_code_length" name="mfa_code_length">
                                @foreach([4, 5, 6, 7, 8] as $len)
                                    <option value="{{ $len }}" {{ $config['mfa_code_length'] == $len ? 'selected' : '' }}>{{ $len }} 位</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mfa_code_ttl" class="form-label">验证码有效期（秒）</label>
                            <select class="form-select" id="mfa_code_ttl" name="mfa_code_ttl">
                                @foreach([60, 120, 180, 300, 600] as $ttl)
                                    <option value="{{ $ttl }}" {{ $config['mfa_code_ttl'] == $ttl ? 'selected' : '' }}>{{ $ttl }} 秒</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mfa_trust_device_days" class="form-label">信任设备天数</label>
                            <select class="form-select" id="mfa_trust_device_days" name="mfa_trust_device_days">
                                <option value="0" {{ $config['mfa_trust_device_days'] == 0 ? 'selected' : '' }}>不信任</option>
                                @foreach([7, 14, 30, 60, 90] as $days)
                                    <option value="{{ $days }}" {{ $config['mfa_trust_device_days'] == $days ? 'selected' : '' }}>{{ $days }} 天</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>保存配置</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection