<?php

use App\Core\Support\ModuleContext;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core 路由（无模块前缀）
|--------------------------------------------------------------------------
| 门户 /、统一登录 /login、通知、个人中心、Core 后台等。
| 模块路由见 routes/erp.php、routes/pm.php、routes/soho.php。
*/

Route::middleware('web')->group(function () {

    // 门户：三张卡片（完整视觉在 TC-05 落地，此处为可点骨架）
    Route::get('/', fn () => view('core::portal'))->name('core.home');

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
