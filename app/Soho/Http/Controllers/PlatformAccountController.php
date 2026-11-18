<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\PlatformAccount;
use Illuminate\Http\Request;

class PlatformAccountController extends Controller
{
    /**
     * 平台账号列表
     */
    public function index(Request $request)
    {
        $platformAccounts = PlatformAccount::with(['tenant', 'creator'])
            ->when($request->search, function ($query) use ($request) {
                $query->where('account_name', 'like', "%{$request->search}%")
                    ->orWhere('platform_name', 'like', "%{$request->search}%")
                    ->orWhere('seller_name', 'like', "%{$request->search}%");
            })
            ->when($request->platform_code, function ($query) use ($request) {
                $query->where('platform_code', $request->platform_code);
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('platform-accounts.index', compact('platformAccounts'));
    }

    /**
     * 创建平台账号
     */
    public function create()
    {
        return view('platform-accounts.create');
    }

    /**
     * 保存新平台账号
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform_code' => 'required|string|max:50',
            'platform_name' => 'required|string|max:100',
            'account_type' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'developer_id' => 'nullable|string|max:255',
            'app_secret' => 'nullable|string',
            'seller_id' => 'nullable|string|max:255',
            'seller_name' => 'nullable|string|max:255',
            'marketplace_id' => 'nullable|string|max:255',
            'marketplace_name' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|max:10',
            'country_name' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,inactive,suspended',
        ]);

        // 设置默认值
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['tenant_id'] = auth()->user()->tenant_id ?? null;
        $validated['created_by'] = auth()->id();

        // 加密 AppSecret
        if (!empty($validated['app_secret'])) {
            $validated['app_secret_encrypted'] = encrypt($validated['app_secret']);
        }
        unset($validated['app_secret']);

        PlatformAccount::create($validated);

        return redirect()->route('platform_accounts.index')->with('success', '平台账号创建成功');
    }

    /**
     * 编辑平台账号
     */
    public function edit(PlatformAccount $platformAccount)
    {
        return view('platform-accounts.edit', compact('platformAccount'));
    }

    /**
     * 更新平台账号
     */
    public function update(Request $request, PlatformAccount $platformAccount)
    {
        $validated = $request->validate([
            'platform_code' => 'required|string|max:50',
            'platform_name' => 'required|string|max:100',
            'account_type' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'developer_id' => 'nullable|string|max:255',
            'app_secret' => 'nullable|string',
            'seller_id' => 'nullable|string|max:255',
            'seller_name' => 'nullable|string|max:255',
            'marketplace_id' => 'nullable|string|max:255',
            'marketplace_name' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|max:10',
            'country_name' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,inactive,suspended',
        ]);

        // 加密 AppSecret（留空则保持不变）
        if (!empty($validated['app_secret'])) {
            $validated['app_secret_encrypted'] = encrypt($validated['app_secret']);
        }
        unset($validated['app_secret']);

        $platformAccount->update($validated);

        return redirect()->route('platform_accounts.index')->with('success', '平台账号更新成功');
    }

    /**
     * 授权平台账号
     */
    public function authorize(PlatformAccount $platformAccount)
    {
        $platformAccount->update([
            'oauth_status' => 'authorized',
            'authorized_at' => now(),
        ]);

        return back()->with('success', '授权成功');
    }
}
