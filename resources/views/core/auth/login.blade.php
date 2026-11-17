@extends('core::layouts.base')

@section('title', '登录 · ' . config('app.name'))

@push('styles')
<style>
    .dw-login-wrap { min-height: 100vh; display: grid; place-items: center; padding: 2rem 1rem; }
    .dw-login-card { width: 100%; max-width: 26rem; }
</style>
@endpush

@section('content')
<div class="dw-login-wrap">
    <div class="dw-card dw-login-card">
        <div class="dw-card-body">
            <h1 class="h4 mb-1">{{ config('app.name') }}</h1>
            <p class="text-muted mb-4">请使用用户名或邮箱登录</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="login">用户名 / 邮箱</label>
                    <input class="form-control @error('login') is-invalid @enderror" id="login" name="login"
                           type="text" value="{{ old('login') }}" autocomplete="username" required
                           aria-describedby="login-error">
                    @error('login')
                        <div class="invalid-feedback" id="login-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">密码</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password"
                           type="password" autocomplete="current-password" required aria-describedby="password-error">
                    @error('password')
                        <div class="invalid-feedback" id="password-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                    <label class="form-check-label" for="remember">记住我</label>
                </div>

                <button class="btn btn-primary w-100" type="submit">登录</button>
            </form>
        </div>
    </div>
</div>
@endsection
