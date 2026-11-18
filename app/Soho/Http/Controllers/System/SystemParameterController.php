<?php

namespace App\Soho\Http\Controllers\System;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\SystemParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 系统参数控制器
 * 管理外部平台连接所需的API Key、密钥、回调地址等配置参数
 * 仅超级管理员可访问
 */
class SystemParameterController extends Controller
{
    /**
     * 显示系统参数列表（支持搜索和分类筛选）
     */
    public function index(Request $request)
    {
        $query = SystemParameter::query();

        // 关键词搜索（按参数键或名称）
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('param_key', 'like', "%{$search}%")
                  ->orWhere('param_name', 'like', "%{$search}%");
            });
        }

        // 分类筛选
        if ($category = $request->input('category')) {
            $query->ofCategory($category);
        }

        // 状态筛选
        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // 分页查询，每页15条
        $parameters = $query->orderByDesc('id')->paginate(15)->withQueryString();

        // 分类标签（用于筛选下拉框）
        $categories = SystemParameter::getCategoryLabels();

        return view('system.parameters.index', compact('parameters', 'categories'));
    }

    /**
     * 显示创建参数表单
     */
    public function create()
    {
        $categories = SystemParameter::getCategoryLabels();
        $valueTypes = SystemParameter::getValueTypeLabels();

        return view('system.parameters.create', compact('categories', 'valueTypes'));
    }

    /**
     * 存储新参数
     * 如果标记为加密，则自动加密参数值
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'param_key' => 'required|string|max:100|unique:soho_system_parameters,param_key',
            'param_name' => 'required|string|max:100',
            'param_value' => 'nullable|string|max:2000',
            'category' => 'required|string|in:platform_api,callback_url,secret_key,other',
            'value_type' => 'required|string|in:string,boolean,integer,json',
            'description' => 'nullable|string|max:255',
            'is_encrypted' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // 设置当前用户为最后修改人
        $validated['last_modified_by'] = Auth::id();
        $validated['is_encrypted'] = $request->boolean('is_encrypted');
        $validated['is_active'] = $request->boolean('is_active', true);

        // 创建参数（模型会自动处理加密）
        SystemParameter::create($validated);

        return redirect()
            ->route('soho.system.parameters.index')
            ->with('success', "系统参数「{$validated['param_name']}」创建成功");
    }

    /**
     * 显示编辑参数表单
     */
    public function edit(SystemParameter $parameter)
    {
        $categories = SystemParameter::getCategoryLabels();
        $valueTypes = SystemParameter::getValueTypeLabels();

        return view('system.parameters.edit', compact('parameter', 'categories', 'valueTypes'));
    }

    /**
     * 更新参数
     * 注意：param_key 不允许修改（作为唯一标识）
     */
    public function update(Request $request, SystemParameter $parameter)
    {
        $validated = $request->validate([
            'param_name' => 'required|string|max:100',
            'param_value' => 'nullable|string|max:2000',
            'category' => 'required|string|in:platform_api,callback_url,secret_key,other',
            'value_type' => 'required|string|in:string,boolean,integer,json',
            'description' => 'nullable|string|max:255',
            'is_encrypted' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['last_modified_by'] = Auth::id();
        $validated['is_encrypted'] = $request->boolean('is_encrypted');
        $validated['is_active'] = $request->boolean('is_active', true);

        // 如果加密状态发生变化，需要重新处理值
        // 先清除原值（避免模型 accessor 干扰），再设置新值
        $parameter->setRawAttributes($parameter->getRawOriginal());
        $parameter->is_encrypted = $validated['is_encrypted'];
        $parameter->fill($validated);
        $parameter->save();

        return redirect()
            ->route('soho.system.parameters.index')
            ->with('success', "系统参数「{$validated['param_name']}」更新成功");
    }

    /**
     * 删除参数
     */
    public function destroy(SystemParameter $parameter)
    {
        $paramName = $parameter->param_name;
        $parameter->delete();

        return redirect()
            ->route('soho.system.parameters.index')
            ->with('success', "系统参数「{$paramName}」已删除");
    }

    /**
     * 快速切换启用/禁用状态
     */
    public function toggleActive(SystemParameter $parameter)
    {
        $parameter->update([
            'is_active' => !$parameter->is_active,
            'last_modified_by' => Auth::id(),
        ]);

        $status = $parameter->is_active ? '已启用' : '已禁用';

        return redirect()
            ->back()
            ->with('success', "参数「{$parameter->param_name}」{$status}");
    }
}
