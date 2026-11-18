<?php

namespace App\Soho\Http\Controllers\Security;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\AccountLockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 账号锁定记录控制器
 * 展示账号锁定历史，支持手动解锁操作
 */
class AccountLockoutController extends Controller
{
    /**
     * 显示锁定记录列表
     */
    public function index(Request $request)
    {
        $query = AccountLockout::query()->with(['user:id,name,email', 'unlocker:id,name']);

        // 筛选用户
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // 筛选锁定类型
        if ($type = $request->input('type')) {
            $query->where('lock_type', $type);
        }

        // 筛选状态
        $status = $request->input('status');
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'unlocked') {
            $query->unlocked();
        }

        // 一次性获取全部记录
        $lockouts = $query->orderByDesc('locked_at')->get();

        // 统计数据
        $stats = [
            'total' => $lockouts->count(),
            'active' => $lockouts->filter(fn($l) => $l->isActive())->count(),
            'unlocked' => $lockouts->filter(fn($l) => $l->isUnlocked())->count(),
            'permanent' => $lockouts->filter(fn($l) => $l->isPermanent())->count(),
        ];

        $lockTypes = AccountLockout::getLockTypeLabels();

        return view('security.lockouts.index', compact('lockouts', 'stats', 'lockTypes'));
    }

    /**
     * 显示锁定详情
     */
    public function show(AccountLockout $lockout)
    {
        $lockout->load(['user', 'unlocker']);

        // 该用户的其他锁定记录
        $relatedLockouts = AccountLockout::where('user_id', $lockout->user_id)
            ->where('id', '!=', $lockout->id)
            ->orderByDesc('locked_at')
            ->limit(5)
            ->get();

        return view('security.lockouts.show', compact('lockout', 'relatedLockouts'));
    }

    /**
     * 手动解锁账号
     */
    public function unlock(Request $request, AccountLockout $lockout)
    {
        // 检查是否仍在锁定中
        if (!$lockout->isActive()) {
            return back()->with('error', '该账号已解锁或锁定已过期');
        }

        $request->validate([
            'unlock_note' => 'nullable|string|max:500',
        ]);

        $lockout->update([
            'unlocked_by' => Auth::id(),
            'unlocked_at' => now(),
            'unlock_type' => 'manual',
            'unlock_note' => $request->input('unlock_note'),
        ]);

        return back()->with('success', "账号「{$lockout->user->name}」已解锁");
    }
}