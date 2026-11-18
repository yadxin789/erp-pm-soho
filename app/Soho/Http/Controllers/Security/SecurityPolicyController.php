<?php

namespace App\Soho\Http\Controllers\Security;

use App\Soho\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 安全策略控制器
 * 管理二次验证策略和密码策略配置
 */
class SecurityPolicyController extends Controller
{
    /**
     * 显示二次验证策略配置页面
     */
    public function mfaIndex()
    {
        // 获取当前 MFA 配置（从系统配置读取）
        $config = $this->getMfaConfig();

        return view('security.mfa.index', compact('config'));
    }

    /**
     * 更新二次验证策略配置
     */
    public function mfaUpdate(Request $request)
    {
        $validated = $request->validate([
            'mfa_enabled' => 'boolean',
            'mfa_required_roles' => 'nullable|array',
            'mfa_code_length' => 'integer|min:4|max:8',
            'mfa_code_ttl' => 'integer|min:60|max:600',
            'mfa_trust_device_days' => 'integer|min:0|max:90',
        ]);

        // 保存配置（可存入系统配置表或缓存）
        foreach ($validated as $key => $value) {
            \App\Soho\Models\SystemConfig::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        return back()->with('success', '二次验证策略已更新');
    }

    /**
     * 显示密码策略配置页面
     */
    public function passwordIndex()
    {
        $config = $this->getPasswordConfig();

        return view('security.password.index', compact('config'));
    }

    /**
     * 更新密码策略配置
     */
    public function passwordUpdate(Request $request)
    {
        $validated = $request->validate([
            'password_min_length' => 'integer|min:6|max:32',
            'password_require_uppercase' => 'boolean',
            'password_require_lowercase' => 'boolean',
            'password_require_number' => 'boolean',
            'password_require_symbol' => 'boolean',
            'password_expire_days' => 'integer|min:0|max:365',
            'password_history_count' => 'integer|min:0|max:24',
            'password_lockout_threshold' => 'integer|min:3|max:10',
            'password_lockout_duration' => 'integer|min:5|max:120',
        ]);

        foreach ($validated as $key => $value) {
            \App\Soho\Models\SystemConfig::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', '密码策略已更新');
    }

    /**
     * 获取 MFA 配置
     */
    private function getMfaConfig(): array
    {
        return [
            'mfa_enabled' => (bool) \App\Soho\Models\SystemConfig::where('key', 'mfa_enabled')->value('value') ?? false,
            'mfa_required_roles' => json_decode(\App\Soho\Models\SystemConfig::where('key', 'mfa_required_roles')->value('value') ?? '[]', true),
            'mfa_code_length' => (int) \App\Soho\Models\SystemConfig::where('key', 'mfa_code_length')->value('value') ?? 6,
            'mfa_code_ttl' => (int) \App\Soho\Models\SystemConfig::where('key', 'mfa_code_ttl')->value('value') ?? 300,
            'mfa_trust_device_days' => (int) \App\Soho\Models\SystemConfig::where('key', 'mfa_trust_device_days')->value('value') ?? 30,
        ];
    }

    /**
     * 获取密码策略配置
     */
    private function getPasswordConfig(): array
    {
        return [
            'password_min_length' => (int) \App\Soho\Models\SystemConfig::where('key', 'password_min_length')->value('value') ?? 8,
            'password_require_uppercase' => (bool) \App\Soho\Models\SystemConfig::where('key', 'password_require_uppercase')->value('value') ?? true,
            'password_require_lowercase' => (bool) \App\Soho\Models\SystemConfig::where('key', 'password_require_lowercase')->value('value') ?? true,
            'password_require_number' => (bool) \App\Soho\Models\SystemConfig::where('key', 'password_require_number')->value('value') ?? true,
            'password_require_symbol' => (bool) \App\Soho\Models\SystemConfig::where('key', 'password_require_symbol')->value('value') ?? false,
            'password_expire_days' => (int) \App\Soho\Models\SystemConfig::where('key', 'password_expire_days')->value('value') ?? 90,
            'password_history_count' => (int) \App\Soho\Models\SystemConfig::where('key', 'password_history_count')->value('value') ?? 5,
            'password_lockout_threshold' => (int) \App\Soho\Models\SystemConfig::where('key', 'password_lockout_threshold')->value('value') ?? 5,
            'password_lockout_duration' => (int) \App\Soho\Models\SystemConfig::where('key', 'password_lockout_duration')->value('value') ?? 30,
        ];
    }
}