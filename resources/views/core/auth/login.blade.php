@extends('core::layouts.base')

@section('title', '登录 · DDWL 管理中枢')

@section('plain', 'plain')

@section('content')
<div class="dw-login-card">
    <div class="dw-login-brand">
        <span class="dw-login-logo"><i class="fas fa-cubes" aria-hidden="true"></i></span>
        <span class="dw-login-title">DDWL 管理中枢</span>
        <p class="dw-login-sub">ERP · 研发项目 · 电商，一套账号登录</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
        <div class="alert alert-success py-2" role="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="/login" novalidate>
        @csrf
        <div class="mb-3">
            <label class="form-label" for="login">用户名 / 邮箱</label>
            <div class="dw-input-icon">
                <span><i class="fas fa-user" aria-hidden="true"></i></span>
                <input id="login" name="login" type="text" value="{{ old('login') }}"
                       placeholder="请输入用户名或邮箱" autocomplete="username" required
                       aria-describedby="login-error">
            </div>
            @error('login')
                <div class="dw-error d-block" id="login-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">密码</label>
            <div class="dw-input-icon">
                <span><i class="fas fa-lock" aria-hidden="true"></i></span>
                <input id="password" name="password" type="password" placeholder="请输入密码"
                       autocomplete="current-password" required aria-describedby="password-error">
            </div>
            @error('password')
                <div class="dw-error d-block" id="password-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
            <label class="form-check-label" for="remember">记住我</label>
        </div>

        <button class="dw-login-btn" type="submit"><i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>登录</button>
    </form>

    <details class="dw-login-demo">
        <summary>演示账号（点击自动填充）</summary>
        <div class="mt-2">
            @foreach ($demoAccounts ?? [] as $demo)
                <button type="button" class="btn btn-sm btn-link p-0 me-3 dw-demo-fill"
                        data-login="{{ $demo['login'] }}" data-password="{{ $demo['password'] }}">
                    {{ $demo['label'] }}
                </button>
            @endforeach
        </div>
    </details>

    <div class="dw-login-links">
        <a href="/"><i class="fas fa-home me-1" aria-hidden="true"></i>返回首页</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.dw-demo-fill').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('login').value = btn.dataset.login;
            document.getElementById('password').value = btn.dataset.password;
        });
    });
</script>
@endpush
