<?php

namespace App\Soho\Listeners;

use App\Soho\Models\OperationLog;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;

class LoginListener
{
    /**
     * 处理用户登录成功事件
     */
    public function handleLogin(Login $event): void
    {
        $user = $event->user;

        // 记录登录 IP 和最后登录时间
        if ($user) {
            $user->update([
                'last_login_ip'  => request()->ip(),
                'last_login_at'  => now(),
            ]);

            // 记录操作日志
            OperationLog::create([
                'user_id'     => $user->id,
                'action'      => 'login',
                'model_type'  => get_class($user),
                'model_id'    => $user->id,
                'description' => "用户 {$user->name} ({$user->email}) 登录成功",
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);
        }
    }

    /**
     * 处理用户登录失败事件
     */
    public function handleFailed(Failed $event): void
    {
        OperationLog::create([
            'action'       => 'login_failed',
            'description'  => "登录失败尝试 - 邮箱: {$event->credentials['email']}",
            'changes'      => ['credentials' => ['email' => $event->credentials['email'] ?? '']],
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }

    /**
     * 处理用户登出事件
     */
    public function handleLogout(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            OperationLog::create([
                'user_id'     => $user->id,
                'action'      => 'logout',
                'model_type'  => get_class($user),
                'model_id'    => $user->id,
                'description' => "用户 {$user->name} 登出",
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);
        }
    }
}
