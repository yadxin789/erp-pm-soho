<?php

namespace App\Soho\Http\Controllers\Security;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\IpWhitelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * IP 白名单控制器
 * 管理 IP 访问控制规则
 */
class IpWhitelistController extends Controller
{
    /**
     * 显示 IP 白名单列表
     */
    public function index(Request $request)
    {
        $query = IpWhitelist::query()->with('creator:id,name');

        // 类型筛选
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // 范围筛选
        if ($scope = $request->input('scope')) {
            $query->where('scope', $scope);
        }

        $items = $query->orderByDesc('id')->get();

        $typeLabels = IpWhitelist::getTypeLabels();
        $typeColors = IpWhitelist::getTypeColors();
        $scopeLabels = IpWhitelist::getScopeLabels();

        return view('security.ip-whitelist.index', compact('items', 'typeLabels', 'typeColors', 'scopeLabels'));
    }

    /**
     * 显示创建表单
     */
    public function create()
    {
        $typeLabels = IpWhitelist::getTypeLabels();
        $scopeLabels = IpWhitelist::getScopeLabels();

        return view('security.ip-whitelist.create', compact('typeLabels', 'scopeLabels'));
    }

    /**
     * 存储新规则
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:allow,block',
            'scope' => 'required|string|max:30',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = Auth::id();

        IpWhitelist::create($validated);

        return redirect()
            ->route('security.ip-whitelist')
            ->with('success', "IP 规则「{$validated['ip_address']}」添加成功");
    }

    /**
     * 显示编辑表单
     */
    public function edit(IpWhitelist $ipWhitelist)
    {
        $typeLabels = IpWhitelist::getTypeLabels();
        $scopeLabels = IpWhitelist::getScopeLabels();

        return view('security.ip-whitelist.edit', compact('ipWhitelist', 'typeLabels', 'scopeLabels'));
    }

    /**
     * 更新规则
     */
    public function update(Request $request, IpWhitelist $ipWhitelist)
    {
        $validated = $request->validate([
            'ip_address' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:allow,block',
            'scope' => 'required|string|max:30',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $ipWhitelist->update($validated);

        return redirect()
            ->route('security.ip-whitelist')
            ->with('success', "IP 规则「{$validated['ip_address']}」更新成功");
    }

    /**
     * 删除规则
     */
    public function destroy(IpWhitelist $ipWhitelist)
    {
        $ip = $ipWhitelist->ip_address;
        $ipWhitelist->delete();

        return back()->with('success', "IP 规则「{$ip}」已删除");
    }

    /**
     * 切换启用状态
     */
    public function toggle(IpWhitelist $ipWhitelist)
    {
        $ipWhitelist->update(['is_active' => !$ipWhitelist->is_active]);

        return back()->with('success', '状态已切换');
    }
}