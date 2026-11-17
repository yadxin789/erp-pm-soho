<?php

use App\Core\Http\Controllers\Auth\LoginController;
use App\Core\Http\Controllers\HomeController;
use App\Core\Support\ModuleContext;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core 路由（无模块前缀）
|--------------------------------------------------------------------------
| 门户 /、统一登录 /login、登录后分流 /home、通知、个人中心、Core 后台等。
| 模块路由见 routes/erp.php、routes/pm.php、routes/soho.php。
*/

Route::middleware('web')->group(function () {

    // 未登录
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('core.login');
        Route::post('/login', [LoginController::class, 'login'])->name('core.login.attempt');
    });

    // 已登录
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('core.logout');

        // 登录后分流点：单模块直接进模块，多模块回门户（TC-04）
        Route::get('/home', [HomeController::class, 'index'])->name('core.home.redirect');

        // 个人中心（TC-06）
        Route::get('/profile', [\App\Core\Http\Controllers\ProfileController::class, 'index'])->name('core.profile');
        Route::put('/profile/basic', [\App\Core\Http\Controllers\ProfileController::class, 'updateBasic'])->name('core.profile.basic');
        Route::put('/profile/password', [\App\Core\Http\Controllers\ProfileController::class, 'updatePassword'])->name('core.profile.password');
        Route::put('/profile/notifications', [\App\Core\Http\Controllers\ProfileController::class, 'updateNotifications'])->name('core.profile.notifications');
        Route::delete('/profile/sessions', [\App\Core\Http\Controllers\ProfileController::class, 'destroyOtherSessions'])->name('core.profile.sessions');
    });

    // 门户：三张卡片（完整视觉在 TC-05 落地，此处为可点骨架）
    // 门户三卡片（TC-05）
    Route::get('/', [\App\Core\Http\Controllers\PortalController::class, 'index'])->name('core.home');

    // 骨架自检：确认中间件与模块上下文可用
    Route::get('/core/ping', fn () => response()->json([
        'app'     => config('app.name'),
        'laravel' => app()->version(),
        'module'  => ModuleContext::current() ?? 'core',
        'modules' => ModuleContext::all(),
    ]))->name('core.ping');

    // T0-10 组件示例页（前端基座自检）
    Route::get('/core/ui', fn () => view('core::ui'))->name('core.ui');

    // TC-10 聊天：三源合并（ERP 群聊/私聊 + PM 项目留言板）
    Route::middleware('auth')->group(function () {
        Route::get('/chat', [\App\Core\Http\Controllers\ChatController::class, 'index'])->name('core.chat.index');
        Route::get('/chat/messages', [\App\Core\Http\Controllers\ChatController::class, 'messages'])->name('core.chat.messages');
        Route::post('/chat/send', [\App\Core\Http\Controllers\ChatController::class, 'send'])->name('core.chat.send');
        Route::post('/chat/clear', [\App\Core\Http\Controllers\ChatController::class, 'clearMine'])->name('core.chat.clear');
        Route::delete('/chat/{id}', [\App\Core\Http\Controllers\ChatController::class, 'destroy'])->whereNumber('id')->name('core.chat.destroy');
        Route::get('/chat/{id}/file', [\App\Core\Http\Controllers\ChatController::class, 'file'])->whereNumber('id')->name('core.chat.file');
    });

    // TC-10 语音会议：母版取 ERP 的应用内信令（零外部依赖）
    Route::middleware('auth')->group(function () {
        Route::get('/voice', [\App\Core\Http\Controllers\VoiceController::class, 'index'])->name('core.voice.index');
        Route::post('/voice/token', [\App\Core\Http\Controllers\VoiceController::class, 'token'])->name('core.voice.token');
        Route::post('/voice/signal', [\App\Core\Http\Controllers\VoiceController::class, 'signal'])->name('core.voice.signal');
        Route::get('/voice/poll', [\App\Core\Http\Controllers\VoiceController::class, 'poll'])->name('core.voice.poll');
        Route::get('/voice/members', [\App\Core\Http\Controllers\VoiceController::class, 'members'])->name('core.voice.members');
        Route::post('/voice/end', [\App\Core\Http\Controllers\VoiceController::class, 'end'])->name('core.voice.end');
        Route::get('/voice/history', [\App\Core\Http\Controllers\VoiceController::class, 'history'])->name('core.voice.history');
        // PM 外部信令服务（Swoole/Janus）可选回调，写同一张表
        Route::post('/voice/hook/enter', [\App\Core\Http\Controllers\VoiceController::class, 'hookEnter'])->name('core.voice.hook.enter');
        Route::post('/voice/hook/leave', [\App\Core\Http\Controllers\VoiceController::class, 'hookLeave'])->name('core.voice.hook.leave');
    });

    // TC-08 菜单预览与自检：基线条数 / 当前角色可见条数 / 路由未迁入条数
    Route::get('/core/menu/{module?}', [\App\Core\Http\Controllers\MenuPreviewController::class, 'show'])
        ->middleware('auth')
        ->name('core.menu');

    // TC-09 站内通知中心（三源合并为一份，模块内入口统一跳这里）
    Route::middleware('auth')->group(function () {
        Route::get('/notifications', [\App\Core\Http\Controllers\NotificationController::class, 'index'])
            ->name('core.notifications.index');
        Route::get('/notifications/unread-count', [\App\Core\Http\Controllers\NotificationController::class, 'unreadCount'])
            ->name('core.notifications.unread-count');
        Route::post('/notifications/read-all', [\App\Core\Http\Controllers\NotificationController::class, 'readAll'])
            ->name('core.notifications.read-all');
        Route::post('/notifications/{id}/read', [\App\Core\Http\Controllers\NotificationController::class, 'read'])
            ->whereNumber('id')
            ->name('core.notifications.read');
        Route::delete('/notifications/{id}', [\App\Core\Http\Controllers\NotificationController::class, 'destroy'])
            ->whereNumber('id')
            ->name('core.notifications.destroy');
    });

    // TC-11 审批引擎：ERP 订单审批 + SOHO 8 条 FLOW-* 共用一套流/步骤/实例/待办
    Route::middleware('auth')->group(function () {
        Route::get('/approvals', [\App\Core\Http\Controllers\ApprovalController::class, 'index'])
            ->name('core.approvals.index');
        Route::get('/approvals/create', [\App\Core\Http\Controllers\ApprovalController::class, 'create'])
            ->name('core.approvals.create');
        Route::post('/approvals', [\App\Core\Http\Controllers\ApprovalController::class, 'store'])
            ->name('core.approvals.store');
        Route::get('/approvals/{approval}', [\App\Core\Http\Controllers\ApprovalController::class, 'show'])
            ->whereNumber('approval')
            ->name('core.approvals.show');
        Route::post('/approvals/{approval}/tasks/{task}/approve', [\App\Core\Http\Controllers\ApprovalController::class, 'approve'])
            ->whereNumber('approval')->whereNumber('task')
            ->name('core.approvals.approve');
        Route::post('/approvals/{approval}/tasks/{task}/reject', [\App\Core\Http\Controllers\ApprovalController::class, 'reject'])
            ->whereNumber('approval')->whereNumber('task')
            ->name('core.approvals.reject');
        Route::post('/approvals/{approval}/cancel', [\App\Core\Http\Controllers\ApprovalController::class, 'cancel'])
            ->whereNumber('approval')
            ->name('core.approvals.cancel');
    });
});
