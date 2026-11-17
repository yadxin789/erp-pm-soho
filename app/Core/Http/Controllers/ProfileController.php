<?php

namespace App\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * 个人中心（TC-06）：三模块统一到 core::profile。
 *
 * Tab 设计：
 *   1) 基本信息（name/real_name/email/phone/signature + 只读的部门/岗位/模块）
 *   2) 修改密码
 *   3) 通知偏好（wecom_* 字段来自 pm，保留不删；无绑定则置灰说明）
 *   4) 会话与设备（database session 驱动，直接读 sessions 表）
 *   5) AI 设置（erp ai-settings 与 pm AI 工作台的落点，TC-13 迁入前只做占位说明）
 */
class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->loadMissing(['department', 'position']);

        $sessions = [];
        if (config('session.driver') === 'database') {
            $sessions = DB::table('sessions')
                ->where('user_id', $user->getAuthIdentifier())
                ->orderByDesc('last_activity')
                ->limit(10)
                ->get()
                ->map(fn ($row) => [
                    'id'          => $row->id,
                    'ip'          => $row->ip_address,
                    'agent'       => $this->shortAgent($row->user_agent),
                    'last_active' => date('Y-m-d H:i', $row->last_activity),
                    'is_current'  => $row->id === $request->session()->getId(),
                ])
                ->all();
        }

        return view('core::profile.index', [
            'user'      => $user,
            'sessions'  => $sessions,
            'modules'   => array_diff($user->resolveModules(), ['core']),
        ]);
    }

    public function updateBasic(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'real_name' => ['nullable', 'string', 'max:50'],
            'email'     => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)->whereNull('deleted_at')],
            'phone'     => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)->whereNull('deleted_at')],
            'signature' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => '显示名不能为空。',
            'email.email'   => '邮箱格式不正确。',
            'email.unique'  => '该邮箱已被其他账号使用。',
            'phone.unique'  => '该手机号已被其他账号使用。',
        ]);

        $user->fill($data)->save();

        return redirect('/profile')->with('status', '基本信息已保存。');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => '请输入当前密码。',
            'password.required'         => '请输入新密码。',
            'password.min'              => '新密码至少 8 位。',
            'password.confirmed'        => '两次输入的新密码不一致。',
        ]);

        if (! $user->password || ! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => '当前密码不正确。'])->withInput();
        }

        $user->forceFill(['password' => Hash::make($data['password'])])->save();

        // 改密后让其他设备失效（database 驱动下直接删别的会话记录）
        if (config('session.driver') === 'database') {
            DB::table('sessions')
                ->where('user_id', $user->getAuthIdentifier())
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        return redirect('/profile')->with('status', '密码已更新，其他设备需重新登录。');
    }

    public function updateNotifications(Request $request)
    {
        $user = $request->user();

        $settings = [
            'approval' => $request->boolean('notify_approval'),
            'task'     => $request->boolean('notify_task'),
            'chat'     => $request->boolean('notify_chat'),
        ];

        $user->forceFill([
            'wecom_notify_enabled'  => $request->boolean('wecom_notify_enabled'),
            'wecom_notify_settings' => $settings,
        ])->save();

        return redirect('/profile')->with('status', '通知偏好已保存。');
    }

    /** 退出除当前以外的会话（database 驱动才有效） */
    public function destroyOtherSessions(Request $request)
    {
        if (config('session.driver') !== 'database') {
            return redirect('/profile')->with('status', '当前会话驱动不支持远程下线。');
        }

        DB::table('sessions')
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return redirect('/profile')->with('status', '其他设备的登录已退出。');
    }

    private function shortAgent(?string $agent): string
    {
        if (! $agent) {
            return '未知设备';
        }

        $os = '未知系统';
        foreach (['Windows', 'Android', 'iPhone', 'iPad', 'Mac OS', 'Linux'] as $needle) {
            if (stripos($agent, $needle) !== false) {
                $os = $needle;
                break;
            }
        }

        $browser = '未知浏览器';
        foreach (['Edg', 'Chrome', 'Firefox', 'Safari'] as $needle) {
            if (stripos($agent, $needle) !== false) {
                $browser = $needle;
                break;
            }
        }

        return $os.' · '.$browser;
    }
}
