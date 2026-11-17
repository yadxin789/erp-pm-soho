<?php

namespace App\Core\Http\Controllers\Auth;

use App\Core\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * 统一登录（TC-03 / D10）。
 *
 * 规则：
 * - 单一入口 /login，凭据字段 login 同时支持 username 与 email（erp 的 name 亦作为用户名回填）；
 * - 限流 key = login:{标识}|{ip}，上限 5 次（沿用 pm 口径）；
 * - status = 0（原 pm 的 is_active=0）拒绝登录；
 * - 错误提示中文且不区分"账号不存在/密码错误/账号停用"以外的细节泄露；
 * - 登录成功后进 /home，由 HomeController 按 D15 准入链分流。
 */
class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->intended('/home');
        }

        return view('core::auth.login', [
            'demoAccounts' => [
                ['label' => 'ERP 管理员', 'login' => 'admin@erp.demo', 'password' => '123456'],
                ['label' => 'PM 负责人', 'login' => 'pm_director', 'password' => '123456'],
                ['label' => 'SOHO 管理员', 'login' => 'admin@soho.demo', 'password' => '123456'],
            ],
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'login.required'    => '请输入用户名或邮箱。',
            'password.required' => '请输入密码。',
        ]);

        $identifier = trim($credentials['login']);
        $throttleKey = 'login:'.Str::lower($identifier).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => "尝试次数过多，请在 {$seconds} 秒后再试。",
            ]);
        }

        // username 或 email 双字段探测：先按 email 命中，再按 username 命中
        $user = User::query()
            ->where(function ($q) use ($identifier) {
                $q->where('email', $identifier)
                    ->orWhere('username', $identifier);
            })
            ->first();

        $failure = null;

        if (! $user) {
            $failure = '账号或密码不正确。';
        } elseif ((int) $user->status !== 1) {
            $failure = '该账号已停用，请联系管理员。';
        } elseif (! $user->password || ! Hash::check($credentials['password'], $user->password)) {
            // 直接 Hash 校验：pm 的纯 username 账号没有 email，走 provider 凭据会失败
            $failure = '账号或密码不正确。';
        }

        if ($failure !== null) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages(['login' => $failure]);
        }

        Auth::login($user, (bool) ($credentials['remember'] ?? false));

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_seen_at'  => now(),
        ])->save();

        $modules = $user->resolveModules();
        $request->session()->put('active_module', $modules[0] ?? 'core');

        return redirect()->intended('/home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
