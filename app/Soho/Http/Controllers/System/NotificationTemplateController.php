<?php

namespace App\Soho\Http\Controllers\System;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 通知模板控制器
 * 管理系统各业务场景的通知消息模板
 * 支持邮件、短信、微信、系统消息等渠道
 */
class NotificationTemplateController extends Controller
{
    /**
     * 显示模板列表
     */
    public function index(Request $request)
    {
        $query = NotificationTemplate::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('template_code', 'like', "%{$search}%")
                  ->orWhere('template_name', 'like', "%{$search}%");
            });
        }

        if ($channel = $request->input('channel')) {
            $query->ofChannel($channel);
        }

        if ($businessType = $request->input('business_type')) {
            $query->where('business_type', $businessType);
        }

        $templates = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $channels = NotificationTemplate::getChannelLabels();
        $businessTypes = NotificationTemplate::getBusinessTypeLabels();

        return view('system.notifications.index', compact('templates', 'channels', 'businessTypes'));
    }

    /**
     * 显示创建表单
     */
    public function create()
    {
        $channels = NotificationTemplate::getChannelLabels();
        $businessTypes = NotificationTemplate::getBusinessTypeLabels();

        return view('system.notifications.create', compact('channels', 'businessTypes'));
    }

    /**
     * 存储新模板
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_code' => 'required|string|max:100|unique:soho_notification_templates,template_code',
            'template_name' => 'required|string|max:100',
            'channel' => 'required|string|in:email,sms,wechat,system',
            'business_type' => 'required|string|in:order,after_sale,inventory,finance,system,other',
            'subject' => 'nullable|string|max:200',
            'content' => 'required|string|max:5000',
            'available_variables' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['last_modified_by'] = Auth::id();

        // 解析可用变量（每行一个变量名）
        if (!empty($validated['available_variables'])) {
            $vars = array_filter(array_map('trim', explode("\n", $validated['available_variables'])));
            $validated['available_variables'] = $vars;
        } else {
            $validated['available_variables'] = null;
        }

        NotificationTemplate::create($validated);

        return redirect()
            ->route('system.notifications.index')
            ->with('success', "通知模板「{$validated['template_name']}」创建成功");
    }

    /**
     * 显示编辑表单
     */
    public function edit(NotificationTemplate $notification)
    {
        $channels = NotificationTemplate::getChannelLabels();
        $businessTypes = NotificationTemplate::getBusinessTypeLabels();

        return view('system.notifications.edit', compact('notification', 'channels', 'businessTypes'));
    }

    /**
     * 更新模板
     */
    public function update(Request $request, NotificationTemplate $notification)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:100',
            'channel' => 'required|string|in:email,sms,wechat,system',
            'business_type' => 'required|string|in:order,after_sale,inventory,finance,system,other',
            'subject' => 'nullable|string|max:200',
            'content' => 'required|string|max:5000',
            'available_variables' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['last_modified_by'] = Auth::id();

        if (!empty($validated['available_variables'])) {
            $vars = array_filter(array_map('trim', explode("\n", $validated['available_variables'])));
            $validated['available_variables'] = $vars;
        } else {
            $validated['available_variables'] = null;
        }

        $notification->update($validated);

        return redirect()
            ->route('system.notifications.index')
            ->with('success', "通知模板「{$validated['template_name']}」更新成功");
    }

    /**
     * 删除模板
     */
    public function destroy(NotificationTemplate $notification)
    {
        $name = $notification->template_name;
        $notification->delete();

        return redirect()
            ->route('system.notifications.index')
            ->with('success', "通知模板「{$name}」已删除");
    }

    /**
     * 预览模板渲染效果
     */
    public function preview(Request $request, NotificationTemplate $notification)
    {
        // 使用示例变量渲染模板
        $sampleVars = [];
        if ($notification->available_variables) {
            foreach ($notification->available_variables as $var) {
                $sampleVars[$var] = "示例值_{$var}";
            }
        }

        $renderedSubject = $notification->renderSubject($sampleVars);
        $renderedContent = $notification->renderContent($sampleVars);

        return response()->json([
            'subject' => $renderedSubject,
            'content' => $renderedContent,
        ]);
    }
}
