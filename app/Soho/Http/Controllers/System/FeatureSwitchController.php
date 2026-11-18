<?php

namespace App\Soho\Http\Controllers\System;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\FeatureSwitch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 功能开关控制器
 * 控制系统各功能模块的启用/禁用状态
 * 仅超级管理员可访问
 */
class FeatureSwitchController extends Controller
{
    /**
     * 显示功能开关列表（支持搜索和模块筛选）
     */
    public function index(Request $request)
    {
        $query = FeatureSwitch::query();

        // 关键词搜索
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('feature_key', 'like', "%{$search}%")
                  ->orWhere('feature_name', 'like', "%{$search}%");
            });
        }

        // 模块筛选
        if ($module = $request->input('module')) {
            $query->ofModule($module);
        }

        // 状态筛选
        if ($request->has('is_enabled') && $request->input('is_enabled') !== '') {
            $query->where('is_enabled', $request->boolean('is_enabled'));
        }

        $features = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $modules = FeatureSwitch::getModuleLabels();

        return view('system.features.index', compact('features', 'modules'));
    }

    /**
     * 显示创建功能开关表单
     */
    public function create()
    {
        $modules = FeatureSwitch::getModuleLabels();

        return view('system.features.create', compact('modules'));
    }

    /**
     * 存储新功能开关
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'feature_key' => 'required|string|max:100|unique:soho_feature_switches,feature_key',
            'feature_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'module' => 'required|string|in:order,product,inventory,logistics,finance,system,other',
            'is_enabled' => 'boolean',
            'config' => 'nullable|json',
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled', false);
        $validated['last_modified_by'] = Auth::id();

        // 如果启用，记录启用人和启用时间
        if ($validated['is_enabled']) {
            $validated['enabled_by'] = Auth::id();
            $validated['enabled_at'] = now();
        }

        // 处理JSON配置
        if (!empty($validated['config'])) {
            $validated['config'] = json_decode($validated['config'], true);
        }

        FeatureSwitch::create($validated);

        return redirect()
            ->route('system.features.index')
            ->with('success', "功能开关「{$validated['feature_name']}」创建成功");
    }

    /**
     * 显示编辑表单
     */
    public function edit(FeatureSwitch $feature)
    {
        $modules = FeatureSwitch::getModuleLabels();

        return view('system.features.edit', compact('feature', 'modules'));
    }

    /**
     * 更新功能开关
     */
    public function update(Request $request, FeatureSwitch $feature)
    {
        $validated = $request->validate([
            'feature_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'module' => 'required|string|in:order,product,inventory,logistics,finance,system,other',
            'is_enabled' => 'boolean',
            'config' => 'nullable|json',
        ]);

        $wasEnabled = $feature->is_enabled;
        $validated['is_enabled'] = $request->boolean('is_enabled', false);
        $validated['last_modified_by'] = Auth::id();

        // 如果从禁用变为启用，记录启用人和启用时间
        if ($validated['is_enabled'] && !$wasEnabled) {
            $validated['enabled_by'] = Auth::id();
            $validated['enabled_at'] = now();
        }
        // 如果从启用变为禁用，清空启用信息
        elseif (!$validated['is_enabled'] && $wasEnabled) {
            $validated['enabled_by'] = null;
            $validated['enabled_at'] = null;
        }

        // 处理JSON配置
        if (!empty($validated['config'])) {
            $validated['config'] = json_decode($validated['config'], true);
        }

        $feature->update($validated);

        return redirect()
            ->route('system.features.index')
            ->with('success', "功能开关「{$validated['feature_name']}」更新成功");
    }

    /**
     * 删除功能开关
     */
    public function destroy(FeatureSwitch $feature)
    {
        $featureName = $feature->feature_name;
        $feature->delete();

        return redirect()
            ->route('system.features.index')
            ->with('success', "功能开关「{$featureName}」已删除");
    }

    /**
     * 快速切换启用/禁用状态
     */
    public function toggle(FeatureSwitch $feature)
    {
        $newStatus = !$feature->is_enabled;
        $updateData = [
            'is_enabled' => $newStatus,
            'last_modified_by' => Auth::id(),
        ];

        if ($newStatus) {
            $updateData['enabled_by'] = Auth::id();
            $updateData['enabled_at'] = now();
        } else {
            $updateData['enabled_by'] = null;
            $updateData['enabled_at'] = null;
        }

        $feature->update($updateData);

        $status = $newStatus ? '已启用' : '已禁用';

        return redirect()
            ->back()
            ->with('success', "功能「{$feature->feature_name}」{$status}");
    }
}
