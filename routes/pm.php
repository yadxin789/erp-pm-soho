<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 研发项目管理 路由（URL 前缀 /pm，路由名前缀 pm.）
|--------------------------------------------------------------------------
| 迁入原项目路由时：
|   1) 保留原 URL 后缀（例如原 /app/sales/orders → /pm/app/sales/orders）
|   2) 所有 ->name() 必须加 "pm." 前缀（防跨模块重名）
|   3) 模块专用中间件挂在本组内，禁止全局 append
*/

Route::middleware(['web', 'auth', 'module:pm', 'module.access:pm'])
    ->prefix('pm')
    ->name('pm.')
    ->group(function () {

        Route::get('/', fn () => redirect()->route('pm.project.index'))->name('home');

        /* ---------------- 项目（对齐原 PM project 单数路由组） ---------------- */
        Route::get('project', [\App\Pm\Http\Controllers\ProjectController::class, 'index'])->name('project.index');
        Route::get('project/{project}', [\App\Pm\Http\Controllers\ProjectController::class, 'show'])->name('project.show');
        Route::get('project/{project}/gantt', [\App\Pm\Http\Controllers\ProjectController::class, 'gantt'])->name('project.gantt');
        Route::get('project/{project}/history', [\App\Pm\Http\Controllers\ProjectController::class, 'history'])->name('project.history');
        Route::get('project/{project}/logs', [\App\Pm\Http\Controllers\ProjectController::class, 'logs'])->name('project.logs');

        /* 协同办公 */
        Route::get('project/{project}/collab', [\App\Pm\Http\Controllers\CollabController::class, 'index'])->name('project.collab.index');
        Route::post('project/{project}/collab', [\App\Pm\Http\Controllers\CollabController::class, 'store'])->name('project.collab.store');
        Route::get('project/{project}/collab/{doc}/open', [\App\Pm\Http\Controllers\CollabController::class, 'open'])->name('project.collab.open');

        /* 项目聊天室（群聊 + 项目内私聊） */
        Route::get('project/{project}/messages', [\App\Pm\Http\Controllers\ProjectMessageController::class, 'index'])->name('project.messages.index');
        Route::post('project/{project}/messages', [\App\Pm\Http\Controllers\ProjectMessageController::class, 'store'])->name('project.messages.store');
        Route::post('project/{project}/messages/clear', [\App\Pm\Http\Controllers\ProjectMessageController::class, 'clearMine'])->name('project.messages.clear');
        Route::get('project/{project}/chat/{targetUserId}', [\App\Pm\Http\Controllers\ProjectMessageController::class, 'privateChat'])->name('project.messages.private');
        Route::get('project/{project}/messages/{message}/file', [\App\Pm\Http\Controllers\ProjectMessageController::class, 'attachment'])->name('project.messages.file');

        /* 电话会议 */
        Route::get('project/{project}/voice', [\App\Pm\Http\Controllers\ProjectVoiceController::class, 'index'])->name('project.voice.index');

    });