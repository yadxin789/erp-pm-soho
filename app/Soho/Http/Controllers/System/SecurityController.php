<?php

namespace App\Soho\Http\Controllers\System;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SecurityController extends Controller
{
    /**
     * 安全管理页面
     */
    public function index()
    {
        $securityConfig = Cache::get('system_security_config', []);
        return view('system.security', compact('securityConfig'));
    }

    /**
     * 更新安全配置
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'password_min_length' => 'nullable|integer|min:6|max:128',
            'password_require_uppercase' => 'boolean',
            'password_require_number' => 'boolean',
            'password_require_special' => 'boolean',
            'max_login_attempts' => 'nullable|integer|min:1|max:20',
            'lockout_duration' => 'nullable|integer|min:5|max:1440',
            'session_lifetime' => 'nullable|integer|min:30|max:10080',
            'require_2fa' => 'boolean',
            'allow_ip_whitelist' => 'boolean',
        ]);

        Cache::put('system_security_config', $validated, now()->addDays(30));

        return back()->with('success', '安全配置已更新');
    }
}