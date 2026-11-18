<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '登录') - 得道未来多平台电商店铺统一管理系统</title>
    <link rel="stylesheet" href="{{ asset('assets/soho/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/soho/css/font-awesome.min.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .auth-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }
        .auth-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .auth-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
            display: block;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px 15px;
            height: auto;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }
        .remember-me {
            display: flex;
            align-items: center;
        }
        .remember-me input {
            margin-right: 5px;
        }
        .forgot-link {
            float: right;
            color: #667eea;
            text-decoration: none;
        }
        .forgot-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <h3>多平台电商店铺统一管理系统</h3>
            <p>请登录您的账号</p>
        </div>
        <div class="auth-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">邮箱地址</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" 
                           placeholder="请输入邮箱地址" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" 
                           placeholder="请输入密码" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="remember-me">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" style="margin-bottom: 0;">记住我</label>
                        @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">忘记密码？</a>
                        @endif
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    登 录
                </button>
            </form>

            <div class="text-center mt-3">
                <p class="text-muted" style="font-size: 0.85rem;">
                    超级管理员账号: admin@ddwl-soho.com / admin123456
                </p>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/soho/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/soho/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>