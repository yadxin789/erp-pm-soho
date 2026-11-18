<?php

namespace App\Soho\Http\Controllers\Auth;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Soho\Models\User;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * 显示登录页面
     */
    public function create()
    {
        return view('layouts.auth');
    }

    /**
     * 处理身份验证尝试
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // 更新最后登录信息
            $user = Auth::user();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return redirect()->intended(route('soho.dashboard'))->with('success', '登录成功！');
        }

        throw ValidationException::withMessages([
            'email' => ['提供的凭证与我们的记录不匹配。'],
        ]);
    }

    /**
     * 销毁已认证的会话
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', '已成功退出登录');
    }
}