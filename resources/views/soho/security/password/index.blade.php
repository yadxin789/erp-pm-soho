@extends('soho::layouts.app')

@section('title', '密码策略')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-key me-2"></i>密码策略配置</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('soho.security.password-policy.update') }}">
                    @csrf
                    @method('PUT')

                    <h6 class="mb-3 text-primary"><i class="fa fa-lock me-2"></i>密码复杂度要求</h6>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label for="password_min_length" class="form-label">最小长度</label>
                            <input type="number" class="form-control" id="password_min_length" name="password_min_length"
                                   value="{{ $config['password_min_length'] }}" min="6" max="32">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">必须包含</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="password_require_uppercase" name="password_require_uppercase" value="1"
                                           {{ $config['password_require_uppercase'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="password_require_uppercase">大写字母</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="password_require_lowercase" name="password_require_lowercase" value="1"
                                           {{ $config['password_require_lowercase'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="password_require_lowercase">小写字母</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="password_require_number" name="password_require_number" value="1"
                                           {{ $config['password_require_number'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="password_require_number">数字</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="password_require_symbol" name="password_require_symbol" value="1"
                                           {{ $config['password_require_symbol'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="password_require_symbol">特殊符号</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-3 text-primary"><i class="fa fa-clock me-2"></i>密码有效期</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="password_expire_days" class="form-label">密码过期天数</label>
                            <select class="form-select" id="password_expire_days" name="password_expire_days">
                                <option value="0" {{ $config['password_expire_days'] == 0 ? 'selected' : '' }}>永不过期</option>
                                @foreach([30, 60, 90, 180, 365] as $days)
                                    <option value="{{ $days }}" {{ $config['password_expire_days'] == $days ? 'selected' : '' }}>{{ $days }} 天</option>
                                @endforeach
                            </select>
                            <div class="form-text">0 表示永不过期</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_history_count" class="form-label">历史密码检查</label>
                            <select class="form-select" id="password_history_count" name="password_history_count">
                                <option value="0" {{ $config['password_history_count'] == 0 ? 'selected' : '' }}>不检查</option>
                                @foreach([3, 5, 10, 15, 24] as $count)
                                    <option value="{{ $count }}" {{ $config['password_history_count'] == $count ? 'selected' : '' }}>最近 {{ $count }} 次</option>
                                @endforeach
                            </select>
                            <div class="form-text">禁止重复使用最近的密码</div>
                        </div>
                    </div>

                    <h6 class="mb-3 text-primary"><i class="fa fa-ban me-2"></i>锁定策略</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="password_lockout_threshold" class="form-label">失败次数阈值</label>
                            <select class="form-select" id="password_lockout_threshold" name="password_lockout_threshold">
                                @foreach([3, 5, 7, 10] as $count)
                                    <option value="{{ $count }}" {{ $config['password_lockout_threshold'] == $count ? 'selected' : '' }}>{{ $count }} 次失败</option>
                                @endforeach
                            </select>
                            <div class="form-text">连续失败多少次后锁定账号</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_lockout_duration" class="form-label">锁定时长（分钟）</label>
                            <select class="form-select" id="password_lockout_duration" name="password_lockout_duration">
                                @foreach([5, 10, 15, 30, 60, 120] as $min)
                                    <option value="{{ $min }}" {{ $config['password_lockout_duration'] == $min ? 'selected' : '' }}>{{ $min }} 分钟</option>
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