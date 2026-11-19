<?php

use App\Pm\Http\Controllers\AuthController;
use App\Pm\Http\Controllers\AdminController;
use App\Pm\Http\Controllers\DashboardController;
use App\Pm\Http\Controllers\WelcomeController;
use App\Pm\Http\Controllers\ProjectController;
use App\Pm\Http\Controllers\TaskController;
use App\Pm\Http\Controllers\FileController;
use App\Pm\Http\Controllers\NotificationController;
use App\Pm\Http\Controllers\TodoController;
use App\Pm\Http\Controllers\SupportController;
use App\Pm\Http\Controllers\ProjectTemplateController;
use App\Pm\Http\Controllers\HistoryController;
use App\Pm\Http\Controllers\MessageController;
use App\Pm\Http\Controllers\PrivateMessageController;
use App\Pm\Http\Controllers\ProfileController;
use App\Pm\Http\Controllers\ExtensionController;
use App\Pm\Http\Controllers\WecomAuthController;
use App\Pm\Http\Controllers\WecomSettingsController;
use App\Pm\Http\Controllers\WecomCallbackController;
use App\Pm\Http\Controllers\WecomFileController;
use App\Pm\Http\Controllers\UserWecomController;
use App\Pm\Http\Controllers\KnowledgeController;
use App\Pm\Http\Controllers\CollabDocumentController;
use App\Pm\Http\Controllers\AdminTrashController;
use App\Pm\Http\Controllers\MyTrashController;
use App\Pm\Http\Controllers\VoiceController;
use App\Pm\Http\Controllers\AiController;
use App\Pm\Http\Controllers\AiDelegationController;
use Illuminate\Support\Facades\Route;

/*
| PM 路由：原 E:/my-pm/pm-server-openEuler/routes/web.php 机械迁入。
| URL 前缀 /pm，路由名前缀 pm.；登录/注册/登出由 Core 统一提供。
*/

Route::middleware(['web','auth','module:pm','module.access:pm'])
    ->prefix('pm')
    ->name('pm.')
    ->group(function () {


Route::get('/', [WelcomeController::class, 'index'])->name('home');


Route::prefix('wecom')->name('wecom.')->group(function () {
    Route::post('/callback/event', [WecomCallbackController::class, 'handle'])->name('event-callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/ai', [AiController::class, 'welcome'])->name('ai.welcome');
    Route::get('/ai/notice', [AiController::class, 'notice'])->name('ai.notice');
    Route::get('/ai/chat', [AiController::class, 'chat'])->name('ai.chat');
    Route::post('/ai/ask', [AiController::class, 'ask'])->name('ai.ask')->middleware('throttle:ai');
    Route::post('/ai/clear', [AiController::class, 'clear'])->name('ai.clear');

    Route::get('/ai-delegation', [AiDelegationController::class, 'index'])->name('ai-delegation.index');
    Route::post('/ai-delegation/tokens', [AiDelegationController::class, 'storeToken'])->name('ai-delegation.tokens.store');
    Route::delete('/ai-delegation/tokens/{token}', [AiDelegationController::class, 'revokeToken'])->name('ai-delegation.tokens.revoke');
    Route::post('/ai-delegation/proposals/{proposal}/confirm', [AiDelegationController::class, 'confirmProposal'])->name('ai-delegation.proposals.confirm');
    Route::post('/ai-delegation/proposals/{proposal}/reject', [AiDelegationController::class, 'rejectProposal'])->name('ai-delegation.proposals.reject');

    Route::post('/ai/conversations', [AiController::class, 'storeConversation'])->name('ai.conversations.store');
    Route::patch('/ai/conversations/{conversation}', [AiController::class, 'renameConversation'])->name('ai.conversations.rename')->whereNumber('conversation');
    Route::delete('/ai/conversations/{conversation}', [AiController::class, 'destroyConversation'])->name('ai.conversations.destroy')->whereNumber('conversation');
    Route::post('/ai/conversations/{conversation}/clear', [AiController::class, 'clearConversation'])->name('ai.conversations.clear')->whereNumber('conversation');
    Route::get('/ai/conversations/{conversation}/export', [AiController::class, 'exportConversation'])->name('ai.conversations.export')->whereNumber('conversation');

    Route::get('/ai/attachments/{attachment}/preview', [AiController::class, 'previewAttachment'])->name('ai.attachments.preview')->whereNumber('attachment');
    Route::get('/ai/attachments/{attachment}/download', [AiController::class, 'downloadAttachment'])->name('ai.attachments.download')->whereNumber('attachment');
    Route::get('/ai/settings', [AiController::class, 'settings'])->name('ai.settings');
    Route::put('/ai/settings', [AiController::class, 'updateSettings'])->name('ai.settings.update');
    Route::get('/ai/settings/test', [AiController::class, 'test'])->name('ai.settings.test');

    Route::post('/ai/settings/providers/{provider}', [AiController::class, 'saveProvider'])->name('ai.providers.save');
    Route::post('/ai/settings/providers/{provider}/toggle', [AiController::class, 'toggleProvider'])->name('ai.providers.toggle');
    Route::post('/ai/settings/providers/{provider}/default', [AiController::class, 'setDefaultProvider'])->name('ai.providers.default');
    Route::post('/ai/settings/providers/{provider}/test', [AiController::class, 'testProvider'])->name('ai.providers.test');
    Route::post('/ai/settings/providers/{provider}/sync-models', [AiController::class, 'syncProviderModels'])->name('ai.providers.sync');
    Route::delete('/ai/settings/providers/{provider}', [AiController::class, 'removeProvider'])->name('ai.providers.remove');

    Route::post('/ai/settings/models', [AiController::class, 'storeModel'])->name('ai.models.store');
    Route::put('/ai/settings/models/{model}', [AiController::class, 'updateModel'])->name('ai.models.update')->whereNumber('model');
    Route::post('/ai/settings/models/{model}/default', [AiController::class, 'setDefaultModel'])->name('ai.models.default')->whereNumber('model');
    Route::delete('/ai/settings/models/{model}', [AiController::class, 'destroyModel'])->name('ai.models.destroy')->whereNumber('model');
    Route::get('/ai/docs', [AiController::class, 'docs'])->name('ai.docs');
    Route::post('/ai/docs/generate', [AiController::class, 'generateDoc'])->name('ai.docs.generate')->middleware('throttle:ai');

    Route::get('/my-trash', [MyTrashController::class, 'index'])->name('my-trash');
    Route::post('/my-trash/{type}/{id}/restore', [MyTrashController::class, 'restore'])->name('my-trash.restore')->whereNumber('id');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        
        Route::get('/users/deleted', [AdminController::class, 'deletedUsers'])->name('users.deleted');
        Route::post('/users/restore/{user}', [AdminController::class, 'restoreUser'])->name('users.restore');
        Route::delete('/users/force-delete/{user}', [AdminController::class, 'forceDeleteUser'])->name('users.force-delete');

        Route::get('/collab-trash', [AdminTrashController::class, 'index'])->name('trash');
        Route::post('/collab-trash/{type}/restore-batch', [AdminTrashController::class, 'restoreBatch'])->name('trash.restore-batch');
        Route::delete('/collab-trash/{type}/force-delete-batch', [AdminTrashController::class, 'forceDeleteBatch'])->name('trash.force-delete-batch');
        Route::post('/collab-trash/{type}/{id}/restore', [AdminTrashController::class, 'restore'])->name('trash.restore')->whereNumber('id');
        Route::delete('/collab-trash/{type}/{id}', [AdminTrashController::class, 'forceDelete'])->name('trash.force-delete')->whereNumber('id');
        
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/import', [AdminController::class, 'importUsersForm'])->name('users.import-form');
        Route::post('/users/import', [AdminController::class, 'importUsers'])->name('users.import');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');

        Route::resource('departments', AdminController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::get('/departments', [AdminController::class, 'departments'])->name('departments');
        Route::get('/departments/create', [AdminController::class, 'createDepartment'])->name('departments.create');
        Route::post('/departments', [AdminController::class, 'storeDepartment'])->name('departments.store');
        Route::get('/departments/{department}/edit', [AdminController::class, 'editDepartment'])->name('departments.edit');
        Route::put('/departments/{department}', [AdminController::class, 'updateDepartment'])->name('departments.update');
        Route::delete('/departments/{department}', [AdminController::class, 'deleteDepartment'])->name('departments.destroy');

        Route::resource('roles', AdminController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::get('/roles', [AdminController::class, 'roles'])->name('roles');
        Route::get('/roles/create', [AdminController::class, 'createRole'])->name('roles.create');
        Route::post('/roles', [AdminController::class, 'storeRole'])->name('roles.store');
        Route::get('/roles/{role}/edit', [AdminController::class, 'editRole'])->name('roles.edit');
        Route::put('/roles/{role}', [AdminController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{role}', [AdminController::class, 'deleteRole'])->name('roles.destroy');

        Route::get('/statuses', [AdminController::class, 'statuses'])->name('statuses');
        Route::get('/statuses/{status}/edit', [AdminController::class, 'editStatus'])->name('statuses.edit');
        Route::put('/statuses/{status}', [AdminController::class, 'updateStatus'])->name('statuses.update');

        Route::get('/task-types', [AdminController::class, 'taskTypes'])->name('task-types');
        Route::get('/task-types/create', [AdminController::class, 'createTaskType'])->name('task-types.create');
        Route::post('/task-types', [AdminController::class, 'storeTaskType'])->name('task-types.store');
        Route::get('/task-types/{taskType}/edit', [AdminController::class, 'editTaskType'])->name('task-types.edit');
        Route::put('/task-types/{taskType}', [AdminController::class, 'updateTaskType'])->name('task-types.update');
        Route::delete('/task-types/{taskType}', [AdminController::class, 'deleteTaskType'])->name('task-types.destroy');

        Route::get('/file-repository', [AdminController::class, 'fileRepository'])->name('file-repository');
        Route::get('/operation-logs', [AdminController::class, 'operationLogs'])->name('operation-logs');
        Route::get('/send-notification', [AdminController::class, 'sendNotification'])->name('send-notification');
        Route::post('/send-notification', [AdminController::class, 'postNotification'])->name('notification.post');
        Route::get('/services', [AdminController::class, 'services'])->name('services');
        Route::post('/backup', [AdminController::class, 'backupDatabase'])->name('backup');
        Route::get('/todos', [AdminController::class, 'todos'])->name('todos');

        Route::get('/chat-messages', [AdminController::class, 'chatMessages'])->name('chat-messages');
        Route::post('/chat-messages/delete', [AdminController::class, 'deleteChatMessages'])->name('chat-messages.delete');
        Route::post('/chat-messages/clear-project', [AdminController::class, 'clearProjectChat'])->name('chat-messages.clear-project');

        Route::prefix('wecom')->name('wecom.')->group(function () {
            Route::get('/settings', [WecomSettingsController::class, 'index'])->name('settings');
            Route::post('/settings', [WecomSettingsController::class, 'save'])->name('settings.save');
            Route::get('/test', [WecomSettingsController::class, 'testConnection'])->name('test');
            Route::get('/message-logs', [WecomSettingsController::class, 'messageLogs'])->name('message-logs');
        });
    });

    Route::prefix('project')->name('project.')->middleware('pm.project')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('my-projects');
        Route::get('/my-projects/overdue', [ProjectController::class, 'myOverdueProjects'])->name('my-projects.overdue');
        Route::get('/my-projects/in-progress', [ProjectController::class, 'myInProgressProjects'])->name('my-projects.in-progress');
        Route::get('/my-projects/completed', [ProjectController::class, 'myCompletedProjects'])->name('my-projects.completed');
        Route::get('/my-projects/suspended', [ProjectController::class, 'mySuspendedProjects'])->name('my-projects.suspended');
        Route::get('/create', [ProjectController::class, 'create'])->name('create');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'delete'])->name('destroy');
        Route::get('/{project}/logs', [ProjectController::class, 'logs'])->name('logs');
        Route::get('/{project}/change', [ProjectController::class, 'change'])->name('change');
        Route::put('/{project}/change', [ProjectController::class, 'updateChange'])->name('update-change');
        Route::get('/{project}/gantt', [ProjectController::class, 'gantt'])->name('gantt');
        
        Route::post('/{project}/pause', [ProjectController::class, 'pause'])->name('pause');
        Route::post('/{project}/resume', [ProjectController::class, 'resume'])->name('resume');
        
        Route::get('/get-designers/{departmentId}', [ProjectController::class, 'getDesigners'])->name('get-designers');
        Route::get('/get-managers/{departmentId}', [ProjectController::class, 'getManagers'])->name('get-managers');
        Route::get('/get-template/{templateId}', [ProjectController::class, 'getTemplateConfig'])->name('get-template');

        Route::get('/{project}/collab', [CollabDocumentController::class, 'index'])->name('collab.index');
        Route::get('/{project}/collab/create', [CollabDocumentController::class, 'create'])->name('collab.create');
        Route::post('/{project}/collab', [CollabDocumentController::class, 'store'])->name('collab.store');
        Route::get('/{project}/collab/{doc}/open', [CollabDocumentController::class, 'open'])->name('collab.open');
        Route::delete('/{project}/collab/{doc}', [CollabDocumentController::class, 'destroy'])->name('collab.destroy');

        Route::get('/{project}/voice', [VoiceController::class, 'index'])->name('voice.index');
        Route::get('/{project}/voice/token', [VoiceController::class, 'issueToken'])->name('voice.token');
        Route::get('/{project}/voice/members', [VoiceController::class, 'members'])->name('voice.members');
        Route::get('/{project}/voice/doc-config/{doc}', [VoiceController::class, 'docConfig'])->name('voice.doc-config');
        Route::post('/{project}/voice/upload-demo', [VoiceController::class, 'uploadDemoFile'])->name('voice.upload-demo');
        Route::get('/{project}/voice/history', [VoiceController::class, 'history'])->name('voice.history');
        // 站内信令（去 Janus/Swoole 依赖）
        Route::post('/{project}/voice/signal', [VoiceController::class, 'signal'])->name('voice.signal');
        Route::get('/{project}/voice/poll', [VoiceController::class, 'poll'])->name('voice.poll');
        Route::post('/{project}/voice/end', [VoiceController::class, 'end'])->name('voice.end');
    });

    Route::post('voice/hooks/enter', [VoiceController::class, 'hookEnter'])->name('voice.hooks.enter');
    Route::post('voice/hooks/leave', [VoiceController::class, 'hookLeave'])->name('voice.hooks.leave');
    
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('/project/{project}', [HistoryController::class, 'projectHistory'])->name('project.history')->middleware('pm.project');
    });
    
    Route::get('project/{projectId}/messages', [MessageController::class, 'index'])->name('messages.index')->middleware('pm.project');
    Route::post('project/{projectId}/messages', [MessageController::class, 'store'])->name('messages.store')->middleware('pm.project');
    Route::post('project/{projectId}/messages/clear', [MessageController::class, 'clearMine'])->name('messages.clear-mine')->middleware('pm.project');
    Route::delete('project/{projectId}/messages/{id}', [MessageController::class, 'destroy'])->name('messages.destroy')->middleware('pm.project');
    Route::get('messages/{id}/file', [MessageController::class, 'file'])->name('messages.file');
    
    Route::get('project/{projectId}/chat/{targetUserId}', [PrivateMessageController::class, 'show'])->name('messages.private')->middleware('pm.project');
    Route::post('project/{projectId}/chat/{targetUserId}/clear', [PrivateMessageController::class, 'clearMine'])->name('messages.private.clear-mine')->middleware('pm.project');
    Route::get('api/project/{projectId}/chat/members', [PrivateMessageController::class, 'members'])->name('api.chat.members')->middleware('pm.project');
    Route::get('api/project/{projectId}/chat/history/{targetUserId}', [PrivateMessageController::class, 'history'])->name('api.chat.history')->middleware('pm.project');
    Route::post('api/project/{projectId}/chat/send', [PrivateMessageController::class, 'send'])->name('api.chat.send')->middleware('pm.project');

    Route::get('profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::post('profile/avatar/upload', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar.upload');

    Route::get('profile/edit', [ProfileController::class, 'editPersonal'])->name('profile.edit');
    Route::post('profile/update', [ProfileController::class, 'updatePersonal'])->name('profile.update');
    Route::get('profile/password', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
    Route::post('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::prefix('profile/wecom')->name('profile.wecom.')->group(function () {
        Route::get('/', [UserWecomController::class, 'index'])->name('index');
        Route::post('/unbind', [UserWecomController::class, 'unbind'])->name('unbind');
        Route::post('/settings', [UserWecomController::class, 'updateSettings'])->name('settings.update');
    });

    Route::prefix('task')->name('task.')->middleware('pm.project')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('my-tasks');
        Route::get('/my-tasks/pending-execution', [TaskController::class, 'myPendingExecutionTasks'])->name('my-tasks.pending-execution');
        Route::get('/my-tasks/overdue', [TaskController::class, 'myOverdueTasks'])->name('my-tasks.overdue');
        Route::get('/my-tasks/in-progress', [TaskController::class, 'myInProgressTasks'])->name('my-tasks.in-progress');
        Route::get('/my-tasks/completed', [TaskController::class, 'myCompletedTasks'])->name('my-tasks.completed');
        Route::get('/my-tasks/suspended', [TaskController::class, 'mySuspendedTasks'])->name('my-tasks.suspended');
        Route::get('/my-audit-tasks', [TaskController::class, 'myAuditTasks'])->name('my-audit-tasks');
        Route::get('/my-audit-tasks/pending-audit', [TaskController::class, 'myPendingAuditTasks'])->name('my-audit-tasks.pending-audit');
        Route::get('/my-audit-tasks/overdue', [TaskController::class, 'myOverdueAuditTasks'])->name('my-audit-tasks.overdue');
        Route::get('/my-audit-tasks/in-progress', [TaskController::class, 'myInProgressAuditTasks'])->name('my-audit-tasks.in-progress');
        Route::get('/my-audit-tasks/completed', [TaskController::class, 'myCompletedAuditTasks'])->name('my-audit-tasks.completed');
        Route::get('/my-audit-tasks/suspended', [TaskController::class, 'mySuspendedAuditTasks'])->name('my-audit-tasks.suspended');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::get('/{task}/upload', [TaskController::class, 'uploadFile'])->name('upload');
        Route::post('/{task}/upload', [TaskController::class, 'storeFile'])->name('store-file');
        Route::get('/{task}/audit', [TaskController::class, 'audit'])->name('audit');
        Route::post('/{task}/approve', [TaskController::class, 'approve'])->name('approve');
        Route::post('/{task}/reject', [TaskController::class, 'reject'])->name('reject');
        Route::get('/{task}/apply-extension', [TaskController::class, 'applyExtension'])->name('apply-extension');
        Route::post('/{task}/apply-extension', [TaskController::class, 'storeExtension'])->name('store-extension');
        Route::get('/{task}/update', [TaskController::class, 'updateFile'])->name('update-file');
        Route::post('/{task}/update', [TaskController::class, 'storeUpdate'])->name('store-update');
    });

    Route::prefix('file')->name('file.')->group(function () {
        Route::get('/', [FileController::class, 'index'])->name('index');
        Route::get('/resource-files', [FileController::class, 'resourceFiles'])->name('resource-files');
        Route::get('/manage-resource-files', [FileController::class, 'manageResourceFiles'])->name('manage-resource-files');
        Route::get('/{file}/preview', [FileController::class, 'preview'])->name('preview');
        Route::get('/{file}/preview-content', [FileController::class, 'previewFileContent'])->name('preview-content');
        Route::get('/{file}/download', [FileController::class, 'download'])->name('download');
        Route::get('/upload-department', [FileController::class, 'uploadDepartmentFile'])->name('upload-department');
        Route::post('/upload-department', [FileController::class, 'storeDepartmentFile'])->name('store-department');
        Route::get('/department/{file}/preview', [FileController::class, 'previewDepartmentFile'])->name('preview-department');
        Route::get('/department/{file}/download', [FileController::class, 'downloadDepartmentFile'])->name('download-department');
        Route::post('/department/{file}/update', [FileController::class, 'updateDepartmentFile'])->name('update-department');
        Route::delete('/department/{file}', [FileController::class, 'deleteDepartmentFile'])->name('delete-department');
    });

    Route::prefix('notification')->name('notification.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('wecom-file')->name('wecom.file.')->group(function () {
        Route::get('/', [WecomFileController::class, 'index'])->name('index');
        Route::get('/{file}/preview', [WecomFileController::class, 'preview'])->name('preview');
        Route::get('/{file}/download', [WecomFileController::class, 'download'])->name('download');
        Route::post('/{file}/archive', [WecomFileController::class, 'archive'])->name('archive');
        Route::delete('/{file}', [WecomFileController::class, 'destroy'])->name('destroy');
        Route::post('/send-to-wecom', [WecomFileController::class, 'sendToWecom'])->name('send');
        Route::get('/api/bound-users', [WecomFileController::class, 'boundUsers'])->name('bound-users');
    });

    Route::prefix('todo')->name('todo.')->group(function () {
        Route::get('/', [TodoController::class, 'index'])->name('index');
        Route::post('/{todo}/complete', [TodoController::class, 'complete'])->name('complete');
        Route::post('/extension/{application}/approve', [TodoController::class, 'approveExtension'])->name('extension.approve');
        Route::post('/extension/{application}/reject', [TodoController::class, 'rejectExtension'])->name('extension.reject');
    });

    Route::prefix('extension')->name('extension.')->group(function () {
        Route::get('/', [ExtensionController::class, 'index'])->name('index');
        Route::post('/{application}/approve', [ExtensionController::class, 'approve'])->name('approve');
        Route::post('/{application}/reject', [ExtensionController::class, 'reject'])->name('reject');
    });

    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::get('/department-files', [SupportController::class, 'departmentFiles'])->name('department-files');
        Route::get('/send-notification', [SupportController::class, 'sendNotification'])->name('send-notification');
        Route::post('/send-notification', [SupportController::class, 'postNotification'])->name('notification.post');
        Route::get('/upload-resource-file', [SupportController::class, 'uploadResourceFile'])->name('upload-resource-file');
        Route::post('/upload-resource-file', [SupportController::class, 'storeResourceFile'])->name('store-resource-file');
    });

    Route::prefix('project-template')->name('project-template.')->group(function () {
        Route::get('/', [ProjectTemplateController::class, 'index'])->name('index');
        Route::get('/create', [ProjectTemplateController::class, 'create'])->name('create');
        Route::post('/', [ProjectTemplateController::class, 'store'])->name('store');
        Route::get('/{projectTemplate}/edit', [ProjectTemplateController::class, 'edit'])->name('edit');
        Route::put('/{projectTemplate}', [ProjectTemplateController::class, 'update'])->name('update');
        Route::delete('/{projectTemplate}', [ProjectTemplateController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('knowledge')->name('knowledge.')->group(function () {
        Route::get('/', [KnowledgeController::class, 'index'])->name('index');
        Route::get('/create', [KnowledgeController::class, 'create'])->name('create');
        Route::post('/', [KnowledgeController::class, 'store'])->name('store');
        Route::get('/{knowledge}/edit', [KnowledgeController::class, 'edit'])->name('edit');
        Route::put('/{knowledge}', [KnowledgeController::class, 'update'])->name('update');
        Route::delete('/{knowledge}', [KnowledgeController::class, 'destroy'])->name('destroy');
        Route::get('/{knowledge}', [KnowledgeController::class, 'show'])->name('show');
        Route::post('/{knowledge}/docs', [KnowledgeController::class, 'docStore'])->name('doc.store');
        Route::post('/{knowledge}/docs/{doc}', [KnowledgeController::class, 'docUpdate'])->name('doc.update');
        Route::delete('/{knowledge}/docs/{doc}', [KnowledgeController::class, 'docDestroy'])->name('doc.destroy');
        Route::get('/{knowledge}/docs/{doc}/read', [KnowledgeController::class, 'docRead'])->name('doc.read');
    });
});

Route::get('collab/download/{doc}', [CollabDocumentController::class, 'download'])->name('collab.download');
Route::post('collab/callback/{doc}', [CollabDocumentController::class, 'callback'])->name('collab.callback');
});

