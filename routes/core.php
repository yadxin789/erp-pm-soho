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
});
