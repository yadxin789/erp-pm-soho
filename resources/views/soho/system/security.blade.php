@extends('soho::layouts.app')

@section('title', '安全管理')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">安全管理</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">密码策略</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('soho.security.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">最小密码长度</label>
                        <input type="number" name="min_password_length" class="form-control" min="6" max="32" value="{{ old('min_password_length', $securityConfig->min_password_length ?? 8) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">密码有效期 (天)</label>
                        <input type="number" name="password_expiry_days" class="form-control" min="0" value="{{ old('password_expiry_days', $securityConfig->password_expiry_days ?? 90) }}">
                        <small class="text-muted">0 表示永不过期</small>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="require_uppercase" class="form-check-input" value="1" @checked(old('require_uppercase', $securityConfig->require_uppercase ?? true))>
                            <label class="form-check-label">要求包含大写字母</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="require_numbers" class="form-check-input" value="1" @checked(old('require_numbers', $securityConfig->require_numbers ?? true))>
                            <label class="form-check-label">要求包含数字</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="require_special_chars" class="form-check-input" value="1" @checked(old('require_special_chars', $securityConfig->require_special_chars ?? false))>
                            <label class="form-check-label">要求包含特殊字符</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> 保存密码策略
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">登录安全</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('soho.security.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">最大登录尝试次数</label>
                        <input type="number" name="max_login_attempts" class="form-control" min="1" max="20" value="{{ old('max_login_attempts', $securityConfig->max_login_attempts ?? 5) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">锁定时间 (分钟)</label>
                        <input type="number" name="lockout_minutes" class="form-control" min="5" value="{{ old('lockout_minutes', $securityConfig->lockout_minutes ?? 30) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">会话超时 (分钟)</label>
                        <input type="number" name="session_timeout" class="form-control" min="5" value="{{ old('session_timeout', $securityConfig->session_timeout ?? 60) }}">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="two_factor_enabled" class="form-check-input" value="1" @checked(old('two_factor_enabled', $securityConfig->two_factor_enabled ?? false))>
                            <label class="form-check-label">启用双因素认证</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> 保存登录安全设置
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">操作日志摘要</h5></div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-primary">{{ $stats->total_logins ?? 0 }}</h3>
                        <p class="text-muted">总登录次数</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-success">{{ $stats->success_logins ?? 0 }}</h3>
                        <p class="text-muted">成功登录</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-danger">{{ $stats->failed_logins ?? 0 }}</h3>
                        <p class="text-muted">失败登录</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h3 class="text-warning">{{ $stats->locked_accounts ?? 0 }}</h3>
                        <p class="text-muted">锁定账号</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('soho.log.audit.index') }}" class="btn btn-outline-primary">
                <i class="fa fa-list"></i> 查看完整审计日志
            </a>
        </div>
    </div>
</div>
@endsection