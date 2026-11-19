<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ERP 电子厂管理 路由（URL 前缀 /erp）
|--------------------------------------------------------------------------
| 由 D:\laravel12-erp\routes\web.php 全量机械迁入：
|   - 保留原 URL 后缀（原 /app/sales/orders → /erp/app/sales/orders）
|   - 保留原路由名（视图 route('app.sales.orders.index') 无需改一行）
|   - 模块专用中间件挂在本组内，禁止全局 append
| 说明：路由名跨模块重名风险由 B-02 TE-05 统一加前缀阶段处理。
*/

Route::middleware(['web', 'auth', 'module:erp', 'module.access:erp', 'role_scope'])
    ->prefix('erp')
    ->group(function () {

        Route::get('/', function () { return redirect()->route('core.home')->with('warning', 'ERP 模块业务菜单正在迁入（B-02/B-04），先回到统一门户。'); })->name('erp.home');



// 欢迎页面：仅展示统计数据图表/卡片与登录入口


// ============================================================
// 通知（所有已认证用户可访问）
// ============================================================
Route::middleware(['auth'])->group(function () {
    Route::get('notifications', [\App\Erp\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{id}/read', [\App\Erp\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('notifications/{id}/mark-read', [\App\Erp\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::delete('notifications/{id}', [\App\Erp\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('notifications/mark-all-read', [\App\Erp\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('notifications/unread-count', [\App\Erp\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // 附件管理
    Route::post('attachments/upload', [\App\Erp\Http\Controllers\System\AttachmentController::class, 'upload'])->name('attachments.upload');
    Route::get('attachments/{attachment}/download', [\App\Erp\Http\Controllers\System\AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('attachments/{attachment}', [\App\Erp\Http\Controllers\System\AttachmentController::class, 'destroy'])->name('attachments.destroy');
});

// ============================================================
// 公共仪表盘（所有已认证用户）
// ============================================================
Route::middleware(['auth'])->group(function () {
    Route::get('app/dashboard', [\App\Erp\Http\Controllers\App\DashboardController::class, 'index'])->name('app.dashboard');

    // 主数据只读：业务角色在侧边栏查看物料/客户/供应商/仓库/BOM/工艺
    // 写操作（创建/编辑/删除）仍然只走后台 admin.* 路由
    Route::prefix('app/masterdata')->name('app.masterdata.')->group(function () {
        Route::get('items', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'items'])->name('items.index');
        Route::get('customers', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'customers'])->name('customers.index');
        Route::get('suppliers', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'suppliers'])->name('suppliers.index');
        Route::get('warehouses', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'warehouses'])->name('warehouses.index');
        Route::get('boms', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'boms'])->name('boms.index');
        Route::get('routings', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'routings'])->name('routings.index');
    });

    // 主数据详情只读（路由名沿用 admin.*，视图无需改动即可对所有角色可用）
    Route::prefix('app/masterdata')->name('admin.')->group(function () {
        Route::get('items/{item}', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'show'])->name('items.show');
        // 物料规格书（仅 PDF）：上传 / 预览 / 下载 / 删除
        Route::get('items/{item}/spec-sheet', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'specSheet'])->name('items.spec-sheet');
        Route::post('items/{item}/spec-sheet', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'uploadSpecSheet'])->name('items.spec-sheet.upload');
        Route::get('items/{item}/spec-sheet/preview', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'previewSpecSheet'])->name('items.spec-sheet.preview');
        Route::get('items/{item}/spec-sheet/download', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'downloadSpecSheet'])->name('items.spec-sheet.download');
        Route::delete('items/{item}/spec-sheet', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'deleteSpecSheet'])->name('items.spec-sheet.destroy');
        Route::get('customers/{customer}', [\App\Erp\Http\Controllers\MasterData\CustomerController::class, 'show'])->name('customers.show');
        Route::get('suppliers/{supplier}', [\App\Erp\Http\Controllers\MasterData\SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('warehouses/{warehouse}', [\App\Erp\Http\Controllers\MasterData\WarehouseController::class, 'show'])->name('warehouses.show');
    });

    // AI 智能助手
    // 开通引导页：仅管理员可见（管理员负责开通与维护）；其它角色跳「AI 暂未开通」提示页
    Route::get('app/ai/welcome', function () {
        if (! auth()->user()?->hasRole('admin')) {
            return redirect()->route('app.ai.notice');
        }
        $current = \App\Erp\Models\System\AiSetting::current();
        $providers = \App\Erp\Models\System\AiSetting::providers();
        return view('app.ai.welcome', compact('current', 'providers'));
    })->name('app.ai.welcome');

    // AI 暂未开通提示页
    Route::get('app/ai/notice', [\App\Erp\Http\Controllers\App\AiChatController::class, 'notice'])->name('app.ai.notice');
    Route::get('app/ai/chat', [\App\Erp\Http\Controllers\App\AiChatController::class, 'index'])->name('app.ai.chat');
    Route::post('app/ai/chat/ask', [\App\Erp\Http\Controllers\App\AiChatController::class, 'ask'])->name('app.ai.chat.ask');

    // 会话管理
    Route::post('app/ai/chat/conversations', [\App\Erp\Http\Controllers\App\AiChatController::class, 'storeConversation'])->name('app.ai.chat.conversations.store');
    Route::put('app/ai/chat/conversations/{conversation}/rename', [\App\Erp\Http\Controllers\App\AiChatController::class, 'renameConversation'])->name('app.ai.chat.conversations.rename');
    Route::post('app/ai/chat/conversations/{conversation}/clear', [\App\Erp\Http\Controllers\App\AiChatController::class, 'clearConversation'])->name('app.ai.chat.conversations.clear');
    Route::delete('app/ai/chat/conversations/{conversation}', [\App\Erp\Http\Controllers\App\AiChatController::class, 'destroyConversation'])->name('app.ai.chat.conversations.destroy');
    Route::get('app/ai/chat/conversations/{conversation}/export', [\App\Erp\Http\Controllers\App\AiChatController::class, 'exportConversation'])->name('app.ai.chat.conversations.export');

    // 附件
    Route::get('app/ai/chat/attachments/{attachment}/preview', [\App\Erp\Http\Controllers\App\AiChatController::class, 'previewAttachment'])->name('app.ai.chat.attachments.preview');
    Route::get('app/ai/chat/attachments/{attachment}/download', [\App\Erp\Http\Controllers\App\AiChatController::class, 'downloadAttachment'])->name('app.ai.chat.attachments.download');
    Route::post('app/ai/approval-suggestion', [\App\Erp\Http\Controllers\App\AiChatController::class, 'approvalSuggestion'])->name('app.ai.approval-suggestion');
    Route::get('app/ai/invoice-ocr', [\App\Erp\Http\Controllers\App\InvoiceOcrController::class, 'index'])->name('app.ai.invoice-ocr');
    Route::post('app/ai/invoice-ocr', [\App\Erp\Http\Controllers\App\InvoiceOcrController::class, 'recognize'])->name('app.ai.invoice-ocr.recognize');

    // 异步识别（走队列）：提交后返回任务 ID，前端轮询结果
    Route::post('app/ai/ocr', [\App\Erp\Http\Controllers\App\InvoiceOcrController::class, 'store'])->name('app.ai.ocr.store');
    Route::get('app/ai/ocr/{ocr}', [\App\Erp\Http\Controllers\App\InvoiceOcrController::class, 'show'])->name('app.ai.ocr.show');

    // 财务管理
    Route::get('app/finance/invoices', [\App\Erp\Http\Controllers\App\FinanceController::class, 'invoices'])->name('app.finance.invoices');
    Route::get('app/finance/invoices/create', [\App\Erp\Http\Controllers\App\FinanceController::class, 'createInvoice'])->name('app.finance.invoices.create');
    Route::post('app/finance/invoices', [\App\Erp\Http\Controllers\App\FinanceController::class, 'storeInvoice'])->name('app.finance.invoices.store');
    Route::get('app/finance/bills', [\App\Erp\Http\Controllers\App\FinanceController::class, 'bills'])->name('app.finance.bills');
    Route::get('app/finance/bills/create', [\App\Erp\Http\Controllers\App\FinanceController::class, 'createBill'])->name('app.finance.bills.create');
    Route::post('app/finance/bills', [\App\Erp\Http\Controllers\App\FinanceController::class, 'storeBill'])->name('app.finance.bills.store');
    Route::get('app/finance/payments', [\App\Erp\Http\Controllers\App\FinanceController::class, 'payments'])->name('app.finance.payments');
    Route::get('app/finance/payments/create', [\App\Erp\Http\Controllers\App\FinanceController::class, 'createPayment'])->name('app.finance.payments.create');
    Route::post('app/finance/payments', [\App\Erp\Http\Controllers\App\FinanceController::class, 'storePayment'])->name('app.finance.payments.store');
    Route::get('app/finance/statements', [\App\Erp\Http\Controllers\App\FinanceController::class, 'statements'])->name('app.finance.statements');

    // CRM 客户关系管理
    Route::get('app/crm/leads', [\App\Erp\Http\Controllers\App\CrmController::class, 'leads'])->name('app.crm.leads');
    Route::get('app/crm/leads/create', [\App\Erp\Http\Controllers\App\CrmController::class, 'createLead'])->name('app.crm.leads.create');
    Route::post('app/crm/leads', [\App\Erp\Http\Controllers\App\CrmController::class, 'storeLead'])->name('app.crm.leads.store');
    Route::get('app/crm/leads/{lead}', [\App\Erp\Http\Controllers\App\CrmController::class, 'showLead'])->name('app.crm.leads.show');
    Route::post('app/crm/leads/{lead}/follow-up', [\App\Erp\Http\Controllers\App\CrmController::class, 'addFollowUp'])->name('app.crm.leads.follow-up');
    Route::get('app/crm/opportunities', [\App\Erp\Http\Controllers\App\CrmController::class, 'opportunities'])->name('app.crm.opportunities');
    Route::get('app/crm/opportunities/create', [\App\Erp\Http\Controllers\App\CrmController::class, 'createOpportunity'])->name('app.crm.opportunities.create');
    Route::post('app/crm/opportunities', [\App\Erp\Http\Controllers\App\CrmController::class, 'storeOpportunity'])->name('app.crm.opportunities.store');
    Route::get('app/crm/pipeline', [\App\Erp\Http\Controllers\App\CrmController::class, 'pipeline'])->name('app.crm.pipeline');

    // 我的审批待办
    Route::get('app/approvals/my-tasks', [\App\Erp\Http\Controllers\Admin\ApprovalFlowController::class, 'myTasks'])->name('app.approvals.my-tasks');
    Route::post('app/approvals/{task}/handle', [\App\Erp\Http\Controllers\Admin\ApprovalFlowController::class, 'handleTask'])->name('app.approvals.handle');

    // 报表中心
    Route::get('app/reports', [\App\Erp\Http\Controllers\App\ReportController::class, 'index'])->name('app.reports.index');
    Route::get('app/reports/sales', [\App\Erp\Http\Controllers\App\ReportController::class, 'sales'])->name('app.reports.sales');
    Route::get('app/reports/purchase', [\App\Erp\Http\Controllers\App\ReportController::class, 'purchase'])->name('app.reports.purchase');
    Route::get('app/reports/inventory', [\App\Erp\Http\Controllers\App\ReportController::class, 'inventory'])->name('app.reports.inventory');
    Route::get('app/reports/quality', [\App\Erp\Http\Controllers\App\ReportController::class, 'quality'])->name('app.reports.quality');

    // AI 智能分析中心
    Route::get('app/ai/analysis', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'index'])->name('app.ai.analysis-center');
    Route::get('app/ai/daily-report', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'dailyReport'])->name('app.ai.daily-report');
    Route::get('app/ai/stock-alerts', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'stockAlerts'])->name('app.ai.stock-alerts');
    Route::get('app/ai/customer-credit', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'customerCredit'])->name('app.ai.customer-credit');
    Route::get('app/ai/supplier-quality', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'supplierQuality'])->name('app.ai.supplier-quality');
    Route::get('app/ai/sales-forecast', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'salesForecast'])->name('app.ai.sales-forecast');
    Route::get('app/ai/production-cost', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'productionCost'])->name('app.ai.production-cost');
    Route::get('app/ai/anomaly-detection', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'anomalyDetection'])->name('app.ai.anomaly-detection');
    Route::get('app/ai/scheduling', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'scheduling'])->name('app.ai.scheduling');
    Route::get('app/ai/ecommerce-merge', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'ecommerceMerge'])->name('app.ai.ecommerce-merge');
    Route::match(['get','post'], 'app/ai/email-reply', [\App\Erp\Http\Controllers\App\AIAnalysisController::class, 'emailReply'])->name('app.ai.email-reply');

    // 即时消息：群聊 + 私聊（所有登录用户）
    // 个人资料与头像
    Route::get('app/profile', [\App\Erp\Http\Controllers\App\ProfileController::class, 'edit'])->name('app.profile.edit');
    Route::put('app/profile', [\App\Erp\Http\Controllers\App\ProfileController::class, 'update'])->name('app.profile.update');
    Route::get('app/profile/avatar', [\App\Erp\Http\Controllers\App\ProfileController::class, 'avatar'])->name('app.profile.avatar');
    Route::post('app/profile/avatar', [\App\Erp\Http\Controllers\App\ProfileController::class, 'uploadAvatar'])->name('app.profile.avatar.upload');
    Route::delete('app/profile/avatar', [\App\Erp\Http\Controllers\App\ProfileController::class, 'resetAvatar'])->name('app.profile.avatar.reset');

    Route::get('app/chat', [\App\Erp\Http\Controllers\App\ChatController::class, 'index'])->name('app.chat');
    Route::post('app/chat/send', [\App\Erp\Http\Controllers\App\ChatController::class, 'send'])->name('app.chat.send');
    Route::get('app/chat/messages', [\App\Erp\Http\Controllers\App\ChatController::class, 'messages'])->name('app.chat.messages');
    Route::post('app/chat/clear', [\App\Erp\Http\Controllers\App\ChatController::class, 'clearMine'])->name('app.chat.clear');
    Route::get('app/chat/files/{message}', [\App\Erp\Http\Controllers\App\ChatController::class, 'file'])->name('app.chat.file');
    Route::delete('app/chat/messages/{message}', [\App\Erp\Http\Controllers\App\ChatController::class, 'destroy'])->name('app.chat.destroy');

    Route::get('app/chat/members', [\App\Erp\Http\Controllers\App\PrivateChatController::class, 'members'])->name('app.chat.members');
    Route::get('app/chat/private/{user}', [\App\Erp\Http\Controllers\App\PrivateChatController::class, 'show'])->name('app.chat.private');
    Route::post('app/chat/private/{user}/send', [\App\Erp\Http\Controllers\App\PrivateChatController::class, 'send'])->name('app.chat.private.send');
    Route::get('app/chat/private/{user}/history', [\App\Erp\Http\Controllers\App\PrivateChatController::class, 'history'])->name('app.chat.private.history');
    Route::post('app/chat/private/{user}/clear', [\App\Erp\Http\Controllers\App\PrivateChatController::class, 'clearMine'])->name('app.chat.private.clear');

    // ============ 人力资源模块（人事专员 / 只读访客）============
    Route::middleware('role:hr_specialist|viewer')->prefix('app/hr')->name('app.hr.')->group(function () {
        Route::get('employees', [\App\Erp\Http\Controllers\App\HrController::class, 'employees'])->name('employees.index');
        Route::get('employees/create', [\App\Erp\Http\Controllers\App\HrController::class, 'create'])->name('employees.create');
        Route::post('employees', [\App\Erp\Http\Controllers\App\HrController::class, 'store'])->name('employees.store');
        Route::get('employees/{employee}', [\App\Erp\Http\Controllers\App\HrController::class, 'show'])->name('employees.show');
        Route::get('employees/{employee}/edit', [\App\Erp\Http\Controllers\App\HrController::class, 'edit'])->name('employees.edit');
        Route::put('employees/{employee}', [\App\Erp\Http\Controllers\App\HrController::class, 'update'])->name('employees.update');
        Route::post('employees/sync-roles', [\App\Erp\Http\Controllers\App\HrController::class, 'syncRoles'])->name('employees.sync-roles');
        Route::post('employees/{employee}/leave', [\App\Erp\Http\Controllers\App\HrController::class, 'leave'])->name('employees.leave');
        Route::delete('employees/{employee}', [\App\Erp\Http\Controllers\App\HrController::class, 'destroy'])->name('employees.destroy');

        Route::get('attendances', [\App\Erp\Http\Controllers\App\HrController::class, 'attendance'])->name('attendances.index');
        Route::get('attendances/create', [\App\Erp\Http\Controllers\App\HrController::class, 'attendanceCreate'])->name('attendances.create');
        Route::post('attendances', [\App\Erp\Http\Controllers\App\HrController::class, 'attendanceStore'])->name('attendances.store');
        Route::get('attendances/{attendance}/edit', [\App\Erp\Http\Controllers\App\HrController::class, 'attendanceEdit'])->name('attendances.edit');
        Route::put('attendances/{attendance}', [\App\Erp\Http\Controllers\App\HrController::class, 'attendanceUpdate'])->name('attendances.update');
        Route::delete('attendances/{attendance}', [\App\Erp\Http\Controllers\App\HrController::class, 'attendanceDestroy'])->name('attendances.destroy');

        // 薪资管理
        Route::get('payroll', [\App\Erp\Http\Controllers\App\HrController::class, 'payroll'])->name('payroll.index');
        Route::get('payroll/create', [\App\Erp\Http\Controllers\App\HrController::class, 'payrollCreate'])->name('payroll.create');
        Route::post('payroll', [\App\Erp\Http\Controllers\App\HrController::class, 'payrollStore'])->name('payroll.store');
        Route::get('payroll/{salary}/edit', [\App\Erp\Http\Controllers\App\HrController::class, 'payrollEdit'])->name('payroll.edit');
        Route::put('payroll/{salary}', [\App\Erp\Http\Controllers\App\HrController::class, 'payrollUpdate'])->name('payroll.update');
        Route::post('payroll/{salary}/status', [\App\Erp\Http\Controllers\App\HrController::class, 'payrollStatus'])->name('payroll.status');
        Route::delete('payroll/{salary}', [\App\Erp\Http\Controllers\App\HrController::class, 'payrollDestroy'])->name('payroll.destroy');

        // 绩效考核
        Route::get('performances', [\App\Erp\Http\Controllers\App\HrController::class, 'performance'])->name('performances.index');
        Route::get('performances/create', [\App\Erp\Http\Controllers\App\HrController::class, 'performanceCreate'])->name('performances.create');
        Route::post('performances', [\App\Erp\Http\Controllers\App\HrController::class, 'performanceStore'])->name('performances.store');
        Route::get('performances/{review}/edit', [\App\Erp\Http\Controllers\App\HrController::class, 'performanceEdit'])->name('performances.edit');
        Route::put('performances/{review}', [\App\Erp\Http\Controllers\App\HrController::class, 'performanceUpdate'])->name('performances.update');
        Route::delete('performances/{review}', [\App\Erp\Http\Controllers\App\HrController::class, 'performanceDestroy'])->name('performances.destroy');

        // 招聘需求
        Route::get('recruitments', [\App\Erp\Http\Controllers\App\HrController::class, 'recruitment'])->name('recruitments.index');
        Route::get('recruitments/create', [\App\Erp\Http\Controllers\App\HrController::class, 'recruitmentCreate'])->name('recruitments.create');
        Route::post('recruitments', [\App\Erp\Http\Controllers\App\HrController::class, 'recruitmentStore'])->name('recruitments.store');
        Route::get('recruitments/{recruitment}/edit', [\App\Erp\Http\Controllers\App\HrController::class, 'recruitmentEdit'])->name('recruitments.edit');
        Route::put('recruitments/{recruitment}', [\App\Erp\Http\Controllers\App\HrController::class, 'recruitmentUpdate'])->name('recruitments.update');
        Route::delete('recruitments/{recruitment}', [\App\Erp\Http\Controllers\App\HrController::class, 'recruitmentDestroy'])->name('recruitments.destroy');

        // 应聘者
        Route::get('applications', [\App\Erp\Http\Controllers\App\HrController::class, 'applications'])->name('applications.index');
        Route::get('applications/create', [\App\Erp\Http\Controllers\App\HrController::class, 'applicationCreate'])->name('applications.create');
        Route::post('applications', [\App\Erp\Http\Controllers\App\HrController::class, 'applicationStore'])->name('applications.store');
        Route::get('applications/{application}/edit', [\App\Erp\Http\Controllers\App\HrController::class, 'applicationEdit'])->name('applications.edit');
        Route::put('applications/{application}', [\App\Erp\Http\Controllers\App\HrController::class, 'applicationUpdate'])->name('applications.update');
        Route::post('applications/{application}/status', [\App\Erp\Http\Controllers\App\HrController::class, 'applicationStatus'])->name('applications.status');
        Route::post('applications/{application}/to-employee', [\App\Erp\Http\Controllers\App\HrController::class, 'applicationToEmployee'])->name('applications.to-employee');
        Route::delete('applications/{application}', [\App\Erp\Http\Controllers\App\HrController::class, 'applicationDestroy'])->name('applications.destroy');
    });


    // 文件中心
    Route::prefix('app/files')->name('app.files.')->group(function () {
        Route::get('/', [\App\Erp\Http\Controllers\App\FileController::class, 'index'])->name('index');
        Route::get('resource-files', [\App\Erp\Http\Controllers\App\FileController::class, 'resourceFiles'])->name('resource-files');
        Route::get('manage-resource-files', [\App\Erp\Http\Controllers\App\FileController::class, 'manageResourceFiles'])->name('manage-resource-files');
        Route::get('{file}/preview', [\App\Erp\Http\Controllers\App\FileController::class, 'preview'])->name('preview');
        Route::get('{file}/raw', [\App\Erp\Http\Controllers\App\FileController::class, 'raw'])->name('raw');
        Route::get('{file}/download', [\App\Erp\Http\Controllers\App\FileController::class, 'download'])->name('download');
        Route::get('upload-department', [\App\Erp\Http\Controllers\App\FileController::class, 'uploadDepartmentForm'])->name('upload-department');
        Route::post('upload-department', [\App\Erp\Http\Controllers\App\FileController::class, 'storeDepartmentFile'])->name('store-department');
        Route::get('department/{file}/download', [\App\Erp\Http\Controllers\App\FileController::class, 'downloadDepartmentFile'])->name('download-department');
        Route::post('department/{file}/update', [\App\Erp\Http\Controllers\App\FileController::class, 'updateDepartmentFile'])->name('update-department');
        Route::delete('department/{file}', [\App\Erp\Http\Controllers\App\FileController::class, 'deleteDepartmentFile'])->name('delete-department');
        Route::get('resource/{file}/download', [\App\Erp\Http\Controllers\App\FileController::class, 'downloadResourceFile'])->name('download-resource');
    });

    // 知识库
    Route::prefix('app/knowledge')->name('app.knowledge.')->group(function () {
        Route::get('/', [\App\Erp\Http\Controllers\App\KnowledgeController::class, 'index'])->name('index');
        Route::get('create', [\App\Erp\Http\Controllers\App\KnowledgeController::class, 'create'])->name('create');
        Route::post('/', [\App\Erp\Http\Controllers\App\KnowledgeController::class, 'store'])->name('store');
        Route::get('{knowledge}/edit', [\App\Erp\Http\Controllers\App\KnowledgeController::class, 'edit'])->name('edit');
        Route::put('{knowledge}', [\App\Erp\Http\Controllers\App\KnowledgeController::class, 'update'])->name('update');
        Route::delete('{knowledge}', [\App\Erp\Http\Controllers\App\KnowledgeController::class, 'destroy'])->name('destroy');
        Route::get('{knowledge}', [\App\Erp\Http\Controllers\App\KnowledgeController::class, 'show'])->name('show');
        Route::post('{knowledge}/docs', [\App\Erp\Http\Controllers\App\KnowledgeController::class, 'docStore'])->name('doc.store');
        Route::put('{knowledge}/docs/{doc}', [\App\Erp\Http\Controllers\App\KnowledgeController::class, 'docUpdate'])->name('doc.update');
        Route::delete('{knowledge}/docs/{doc}', [\App\Erp\Http\Controllers\App\KnowledgeController::class, 'docDestroy'])->name('doc.destroy');
    });

    // 部门支援
    Route::prefix('app/support')->name('app.support.')->group(function () {
        Route::get('/', [\App\Erp\Http\Controllers\App\SupportController::class, 'index'])->name('index');
        Route::get('department-files', [\App\Erp\Http\Controllers\App\SupportController::class, 'departmentFiles'])->name('department-files');
        Route::get('send-notification', [\App\Erp\Http\Controllers\App\SupportController::class, 'sendNotificationForm'])->name('send-notification');
        Route::post('send-notification', [\App\Erp\Http\Controllers\App\SupportController::class, 'sendNotification'])->name('notification.post');
        Route::get('upload-resource-file', [\App\Erp\Http\Controllers\App\SupportController::class, 'uploadResourceForm'])->name('upload-resource-file');
        Route::post('upload-resource-file', [\App\Erp\Http\Controllers\App\SupportController::class, 'storeResourceFile'])->name('store-resource-file');
    });

    // ============ 固定资产模块（资产管理员 / 只读访客）============
    Route::middleware('role:asset_manager|viewer')->prefix('app/assets')->name('app.assets.')->group(function () {
        Route::get('/', [\App\Erp\Http\Controllers\App\AssetController::class, 'index'])->name('index');
        Route::get('create', [\App\Erp\Http\Controllers\App\AssetController::class, 'create'])->name('create');
        Route::post('/', [\App\Erp\Http\Controllers\App\AssetController::class, 'store'])->name('store');
        Route::get('{asset}', [\App\Erp\Http\Controllers\App\AssetController::class, 'show'])->name('show');
        Route::get('{asset}/edit', [\App\Erp\Http\Controllers\App\AssetController::class, 'edit'])->name('edit');
        Route::put('{asset}', [\App\Erp\Http\Controllers\App\AssetController::class, 'update'])->name('update');
        Route::post('{asset}/status', [\App\Erp\Http\Controllers\App\AssetController::class, 'changeStatus'])->name('status');
        Route::delete('{asset}', [\App\Erp\Http\Controllers\App\AssetController::class, 'destroy'])->name('destroy');
    });

    // ============ 电商订单模块（电商运营 / 只读访客）============
    Route::middleware('role:ecommerce_operator|viewer')->prefix('app/ecommerce')->name('app.ecommerce.')->group(function () {
        Route::get('/', [\App\Erp\Http\Controllers\App\EcommerceController::class, 'index'])->name('index');
        Route::post('sync', [\App\Erp\Http\Controllers\App\EcommerceController::class, 'sync'])->name('sync');
        Route::get('create', [\App\Erp\Http\Controllers\App\EcommerceController::class, 'create'])->name('create');
        Route::post('/', [\App\Erp\Http\Controllers\App\EcommerceController::class, 'store'])->name('store');
        Route::get('{order}', [\App\Erp\Http\Controllers\App\EcommerceController::class, 'show'])->name('show');
        Route::get('{order}/edit', [\App\Erp\Http\Controllers\App\EcommerceController::class, 'edit'])->name('edit');
        Route::put('{order}', [\App\Erp\Http\Controllers\App\EcommerceController::class, 'update'])->name('update');
        Route::post('{order}/status', [\App\Erp\Http\Controllers\App\EcommerceController::class, 'changeStatus'])->name('status');
        Route::delete('{order}', [\App\Erp\Http\Controllers\App\EcommerceController::class, 'destroy'])->name('destroy');
    });
});

// ============================================================
// 系统管理 —— 仅 admin
// ============================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Erp\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // 系统监控：健康检查 / 告警 / 备份台账
    Route::get('system-health', [\App\Erp\Http\Controllers\Admin\SystemHealthController::class, 'index'])->name('system-health');
    Route::post('system-health/run', [\App\Erp\Http\Controllers\Admin\SystemHealthController::class, 'run'])->name('system-health.run');
    Route::post('system-health/alerts/{alert}/resolve', [\App\Erp\Http\Controllers\Admin\SystemHealthController::class, 'resolveAlert'])->name('system-health.alerts.resolve');

    // 运维监控：系统工作状态 / 定时任务 / 队列 / 自启动程序
    Route::prefix('monitor')->name('monitor.')->group(function () {
        $status = \App\Erp\Http\Controllers\Admin\SystemStatusController::class;
        Route::get('system-status', [$status, 'index'])->name('system-status');
        Route::post('system-status/run-health', [$status, 'runHealth'])->name('system-status.run-health');
        Route::post('system-status/clear-cache', [$status, 'clearCache'])->name('system-status.clear-cache');

        $tasks = \App\Erp\Http\Controllers\Admin\ScheduledTaskController::class;
        Route::get('scheduled-tasks', [$tasks, 'index'])->name('scheduled-tasks');
        Route::post('scheduled-tasks/run', [$tasks, 'run'])->name('scheduled-tasks.run');

        $queue = \App\Erp\Http\Controllers\Admin\QueueMonitorController::class;
        Route::get('queue', [$queue, 'index'])->name('queue');
        Route::post('queue/failures/retry-all', [$queue, 'retryAll'])->name('queue.retry-all');
        Route::post('queue/failures/flush', [$queue, 'flush'])->name('queue.flush');
        Route::post('queue/failures/{id}/retry', [$queue, 'retry'])->name('queue.retry');
        Route::post('queue/failures/{id}/forget', [$queue, 'forget'])->name('queue.forget');
        Route::post('queue/restart', [$queue, 'restart'])->name('queue.restart');

        Route::get('auto-start', [\App\Erp\Http\Controllers\Admin\AutoStartController::class, 'index'])->name('auto-start');
    });

    Route::resource('users', \App\Erp\Http\Controllers\Admin\UserController::class)->except(['show']);
    Route::resource('roles', \App\Erp\Http\Controllers\Admin\RoleController::class)->except(['show']);
    Route::resource('permissions', \App\Erp\Http\Controllers\Admin\PermissionController::class)->except(['show']);

    // 组织架构主数据（部门 / 岗位）—— 只描述组织归属，不参与授权
    Route::resource('departments', \App\Erp\Http\Controllers\Admin\DepartmentController::class)->except(['show']);
    Route::resource('positions', \App\Erp\Http\Controllers\Admin\PositionController::class)->except(['show']);

    // 主数据管理
    Route::get('items', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'items'])->name('items.index');
    Route::get('items/barcode-labels', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'barcodeLabels'])->name('items.barcode-labels');
    Route::get('items/create', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'create'])->name('items.create');
    Route::post('items', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'store'])->name('items.store');
    Route::get('items/{item}/barcode-label', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'barcodeLabel'])->name('items.barcode-label');
    Route::get('items/{item}/edit', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'edit'])->name('items.edit');
    Route::put('items/{item}', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'update'])->name('items.update');
    Route::delete('items/{item}', [\App\Erp\Http\Controllers\MasterData\ItemController::class, 'destroy'])->name('items.destroy');

    Route::get('customers', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'customers'])->name('customers.index');
    Route::get('customers/create', [\App\Erp\Http\Controllers\MasterData\CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [\App\Erp\Http\Controllers\MasterData\CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{customer}/edit', [\App\Erp\Http\Controllers\MasterData\CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [\App\Erp\Http\Controllers\MasterData\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [\App\Erp\Http\Controllers\MasterData\CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('suppliers', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'suppliers'])->name('suppliers.index');
    Route::get('suppliers/create', [\App\Erp\Http\Controllers\MasterData\SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('suppliers', [\App\Erp\Http\Controllers\MasterData\SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('suppliers/{supplier}/edit', [\App\Erp\Http\Controllers\MasterData\SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('suppliers/{supplier}', [\App\Erp\Http\Controllers\MasterData\SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('suppliers/{supplier}', [\App\Erp\Http\Controllers\MasterData\SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::get('warehouses', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'warehouses'])->name('warehouses.index');
    Route::get('warehouses/create', [\App\Erp\Http\Controllers\MasterData\WarehouseController::class, 'create'])->name('warehouses.create');
    Route::post('warehouses', [\App\Erp\Http\Controllers\MasterData\WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('warehouses/{warehouse}/edit', [\App\Erp\Http\Controllers\MasterData\WarehouseController::class, 'edit'])->name('warehouses.edit');
    Route::put('warehouses/{warehouse}', [\App\Erp\Http\Controllers\MasterData\WarehouseController::class, 'update'])->name('warehouses.update');
    Route::delete('warehouses/{warehouse}', [\App\Erp\Http\Controllers\MasterData\WarehouseController::class, 'destroy'])->name('warehouses.destroy');

    Route::get('boms', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'boms'])->name('boms.index');
    Route::get('routings', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'routings'])->name('routings.index');
    Route::get('routings/{routing}', [\App\Erp\Http\Controllers\Admin\MasterDataController::class, 'routingShow'])->name('routings.show');
    Route::get('settings', function () { return view('admin.settings.index'); })->name('settings.index');

    // AI 设置
    Route::get('ai-settings', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'index'])->name('ai-settings.index');
    Route::put('ai-settings', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'update'])->name('ai-settings.update');
    Route::post('ai-settings/test', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'test'])->name('ai-settings.test');

    Route::prefix('ai-settings/providers/{provider}')->name('ai-settings.providers.')->group(function () {
        Route::post('save', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'saveProvider'])->name('save');
        Route::post('toggle', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'toggleProvider'])->name('toggle');
        Route::post('default', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'setDefaultProvider'])->name('default');
        Route::post('test', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'testProvider'])->name('test');
        Route::post('sync', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'syncProviderModels'])->name('sync');
        Route::delete('remove', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'removeProvider'])->name('remove');
    });

    Route::prefix('ai-settings/models')->name('ai-settings.models.')->group(function () {
        Route::post('store', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'storeModel'])->name('store');
        Route::put('{model}', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'updateModel'])->name('update');
        Route::post('{model}/default', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'setDefaultModel'])->name('default');
        Route::delete('{model}', [\App\Erp\Http\Controllers\Admin\AiSettingController::class, 'destroyModel'])->name('destroy');
    });

    // 物流快递设置（通道开通引导 + 配置 + 运单）
    Route::prefix('logistics')->name('logistics.')->group(function () {
        Route::get('guide', [\App\Erp\Http\Controllers\Admin\LogisticsController::class, 'guide'])->name('guide');
        Route::get('settings', [\App\Erp\Http\Controllers\Admin\LogisticsController::class, 'settings'])->name('settings');
        Route::put('settings', [\App\Erp\Http\Controllers\Admin\LogisticsController::class, 'updateSettings'])->name('settings.update');
        Route::get('shipments', [\App\Erp\Http\Controllers\Admin\LogisticsController::class, 'shipments'])->name('shipments');
        Route::post('providers/{provider}/save', [\App\Erp\Http\Controllers\Admin\LogisticsController::class, 'saveProvider'])->name('providers.save');
        Route::post('providers/{provider}/toggle', [\App\Erp\Http\Controllers\Admin\LogisticsController::class, 'toggleProvider'])->name('providers.toggle');
        Route::post('providers/{provider}/default', [\App\Erp\Http\Controllers\Admin\LogisticsController::class, 'setDefaultProvider'])->name('providers.default');
        Route::post('providers/{provider}/test', [\App\Erp\Http\Controllers\Admin\LogisticsController::class, 'testProvider'])->name('providers.test');
        Route::post('providers/sync', [\App\Erp\Http\Controllers\Admin\LogisticsController::class, 'syncCatalog'])->name('providers.sync');
        Route::post('shipments/{shipment}/trace', [\App\Erp\Http\Controllers\Admin\LogisticsController::class, 'trace'])->name('shipments.trace');
    });

    // 审批流配置
    Route::get('approval-flows', [\App\Erp\Http\Controllers\Admin\ApprovalFlowController::class, 'index'])->name('approval-flows.index');
    Route::post('approval-flows', [\App\Erp\Http\Controllers\Admin\ApprovalFlowController::class, 'store'])->name('approval-flows.store');

    Route::get('audit-logs', function () {
        $logs = \App\Erp\Models\System\AuditLog::latest()->paginate(20);
        return view('admin.audit-logs.index', compact('logs'));
    })->name('audit-logs.index');
});

// ============================================================
// 销售管理
// 操作者: admin, sales_manager
// 只读:   accountant, viewer
// ============================================================
Route::middleware(['auth', 'role:admin|sales_manager|accountant|viewer'])->prefix('app/sales')->name('app.sales.')->group(function () {
    Route::resource('orders', \App\Erp\Http\Controllers\App\Sales\SalesOrderController::class)->except(['destroy']);
    Route::post('orders/{order}/submit', [\App\Erp\Http\Controllers\App\Sales\SalesOrderController::class, 'submit'])->name('orders.submit');
    Route::post('orders/{order}/approve', [\App\Erp\Http\Controllers\App\Sales\SalesOrderController::class, 'approve'])->name('orders.approve');
    Route::post('orders/{order}/cancel', [\App\Erp\Http\Controllers\App\Sales\SalesOrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('orders/{order}/close', [\App\Erp\Http\Controllers\App\Sales\SalesOrderController::class, 'close'])->name('orders.close');
    Route::resource('quotations', \App\Erp\Http\Controllers\App\Sales\QuotationController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('quotations/{quotation}/submit', [\App\Erp\Http\Controllers\App\Sales\QuotationController::class, 'submit'])->name('quotations.submit');
    Route::post('quotations/{quotation}/convert', [\App\Erp\Http\Controllers\App\Sales\QuotationController::class, 'convert'])->name('quotations.convert');
    Route::resource('shipments', \App\Erp\Http\Controllers\App\Sales\ShipmentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('shipments/{shipment}/confirm', [\App\Erp\Http\Controllers\App\Sales\ShipmentController::class, 'confirm'])->name('shipments.confirm');
    Route::resource('returns', \App\Erp\Http\Controllers\App\Sales\SalesReturnController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('returns/{return}/approve', [\App\Erp\Http\Controllers\App\Sales\SalesReturnController::class, 'approve'])->name('returns.approve');
});

// ============================================================
// 采购管理
// 操作者: admin, purchase_manager
// 跨模块: warehouse_manager (收货), quality_manager (来料检验参考)
// 只读:   accountant, viewer
// ============================================================
Route::middleware(['auth', 'role:admin|purchase_manager|warehouse_manager|quality_manager|accountant|viewer'])->prefix('app/purchase')->name('app.purchase.')->group(function () {
    Route::resource('orders', \App\Erp\Http\Controllers\App\Purchase\PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('orders/{order}/submit', [\App\Erp\Http\Controllers\App\Purchase\PurchaseOrderController::class, 'submit'])->name('orders.submit');
    Route::post('orders/{order}/approve', [\App\Erp\Http\Controllers\App\Purchase\PurchaseOrderController::class, 'approve'])->name('orders.approve');
    Route::post('orders/{order}/cancel', [\App\Erp\Http\Controllers\App\Purchase\PurchaseOrderController::class, 'cancel'])->name('orders.cancel');
    Route::resource('requisitions', \App\Erp\Http\Controllers\App\Purchase\PurchaseRequisitionController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('requisitions/{requisition}/submit', [\App\Erp\Http\Controllers\App\Purchase\PurchaseRequisitionController::class, 'submit'])->name('requisitions.submit');
    Route::post('requisitions/{requisition}/approve', [\App\Erp\Http\Controllers\App\Purchase\PurchaseRequisitionController::class, 'approve'])->name('requisitions.approve');
    Route::resource('receipts', \App\Erp\Http\Controllers\App\Purchase\ReceiptController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('receipts/{receipt}/confirm', [\App\Erp\Http\Controllers\App\Purchase\ReceiptController::class, 'confirm'])->name('receipts.confirm');
    Route::resource('returns', \App\Erp\Http\Controllers\App\Purchase\PurchaseReturnController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('returns/{return}/approve', [\App\Erp\Http\Controllers\App\Purchase\PurchaseReturnController::class, 'approve'])->name('returns.approve');
});

// ============================================================
// 生产管理
// 操作者: admin, production_manager
// 跨模块: warehouse_manager (发料出库, 成品入库确认)
//          quality_manager (过程检验/出货检验参考)
// 只读:   accountant, viewer
// ============================================================
Route::middleware(['auth', 'role:admin|production_manager|warehouse_manager|quality_manager|accountant|viewer'])->prefix('app/production')->name('app.production.')->group(function () {
    Route::resource('orders', \App\Erp\Http\Controllers\App\Production\ProductionOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('orders/{order}/release', [\App\Erp\Http\Controllers\App\Production\ProductionOrderController::class, 'release'])->name('orders.release');
    Route::post('orders/{order}/start', [\App\Erp\Http\Controllers\App\Production\ProductionOrderController::class, 'start'])->name('orders.start');
    Route::post('orders/{order}/report', [\App\Erp\Http\Controllers\App\Production\ProductionOrderController::class, 'report'])->name('orders.report');
    Route::post('orders/{order}/complete', [\App\Erp\Http\Controllers\App\Production\ProductionOrderController::class, 'complete'])->name('orders.complete');
    Route::post('orders/{order}/close', [\App\Erp\Http\Controllers\App\Production\ProductionOrderController::class, 'close'])->name('orders.close');
    Route::post('orders/{order}/cancel', [\App\Erp\Http\Controllers\App\Production\ProductionOrderController::class, 'cancel'])->name('orders.cancel');
    Route::resource('plans', \App\Erp\Http\Controllers\App\Production\ProductionPlanController::class);
    Route::post('plans/mrp', [\App\Erp\Http\Controllers\App\Production\ProductionPlanController::class, 'runMrp'])->name('plans.mrp');
    Route::post('plans/{plan}/approve', [\App\Erp\Http\Controllers\App\Production\ProductionPlanController::class, 'approve'])->name('plans.approve');
    Route::post('plans/{plan}/convert', [\App\Erp\Http\Controllers\App\Production\ProductionPlanController::class, 'convertToOrders'])->name('plans.convert');
    Route::resource('issues', \App\Erp\Http\Controllers\App\Production\MaterialIssueController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('issues/{issue}/confirm', [\App\Erp\Http\Controllers\App\Production\MaterialIssueController::class, 'confirm'])->name('issues.confirm');
    Route::resource('reports', \App\Erp\Http\Controllers\App\Production\ProductionReportController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('receipts', \App\Erp\Http\Controllers\App\Production\ProductionReceiptController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('receipts/{receipt}/confirm', [\App\Erp\Http\Controllers\App\Production\ProductionReceiptController::class, 'confirm'])->name('receipts.confirm');
});

// ============================================================
// 库存管理
// 操作者: admin, warehouse_manager
// 只读:   accountant, viewer
// ============================================================
// 只读：库存查询 / 流水 / 批次追溯 / 物流查询（生产、质量也需要查看）
Route::middleware(['auth', 'role:admin|warehouse_manager|production_manager|quality_manager|accountant|viewer'])->prefix('app/inventory')->name('app.inventory.')->group(function () {
    Route::get('/', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'index'])->name('index');
    Route::get('/trace', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'trace'])->name('trace');
    Route::post('/trace', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'searchTrace'])->name('trace.search');
    Route::get('/transactions', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'transactions'])->name('transactions');

    // 物流查询（仓库模块）
    Route::get('/logistics', [\App\Erp\Http\Controllers\App\Inventory\LogisticsQueryController::class, 'index'])->name('logistics');
    Route::post('/logistics/query', [\App\Erp\Http\Controllers\App\Inventory\LogisticsQueryController::class, 'query'])->name('logistics.query');
    Route::post('/logistics', [\App\Erp\Http\Controllers\App\Inventory\LogisticsQueryController::class, 'store'])->name('logistics.store');
    Route::get('/logistics/{shipment}', [\App\Erp\Http\Controllers\App\Inventory\LogisticsQueryController::class, 'show'])->name('logistics.show');
});

// 写操作：调拨 / 调整 / 冻结 / 盘点（仅仓库与管理员）
Route::middleware(['auth', 'role:admin|warehouse_manager'])->prefix('app/inventory')->name('app.inventory.')->group(function () {
    Route::get('/transfer', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'transfer'])->name('transfer');
    Route::post('/transfer', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'storeTransfer'])->name('transfer.store');
    Route::get('/adjust', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'adjust'])->name('adjust');
    Route::post('/adjust', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'storeAdjust'])->name('adjust.store');
    Route::get('/freeze', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'freeze'])->name('freeze');
    Route::post('/freeze', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'storeFreeze'])->name('freeze.store');
    Route::get('/stocktake', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'stocktake'])->name('stocktake');
    Route::post('/stocktake', [\App\Erp\Http\Controllers\App\Inventory\InventoryController::class, 'storeStocktake'])->name('stocktake.store');
});

// ============================================================
// 质检管理
// 操作者: admin, quality_manager
// 只读:   accountant, viewer
// ============================================================
Route::middleware(['auth', 'role:admin|quality_manager|accountant|viewer'])->prefix('app/quality')->name('app.quality.')->group(function () {
    Route::resource('inspections', \App\Erp\Http\Controllers\App\Quality\QualityController::class)->only(['index', 'create', 'store', 'show']);
    // 检验项明细与判定（P1 质检闭环）
    Route::post('inspections/{inspection}/lines', [\App\Erp\Http\Controllers\App\Quality\QualityController::class, 'addLine'])->name('inspections.lines.store');
    Route::post('inspections/{inspection}/judge', [\App\Erp\Http\Controllers\App\Quality\QualityController::class, 'judge'])->name('inspections.judge');
    // 不合格品台账与处置
    Route::get('defects', [\App\Erp\Http\Controllers\App\Quality\DefectRecordController::class, 'index'])->name('defects.index');
    Route::get('defects/create', [\App\Erp\Http\Controllers\App\Quality\DefectRecordController::class, 'create'])->name('defects.create');
    Route::post('defects', [\App\Erp\Http\Controllers\App\Quality\DefectRecordController::class, 'store'])->name('defects.store');
    Route::get('defects/{defect}', [\App\Erp\Http\Controllers\App\Quality\DefectRecordController::class, 'show'])->name('defects.show');
    Route::post('defects/{defect}/handle', [\App\Erp\Http\Controllers\App\Quality\DefectRecordController::class, 'handle'])->name('defects.handle');
    Route::get('reports', [\App\Erp\Http\Controllers\App\Quality\QualityController::class, 'reports'])->name('reports');
});




    });
