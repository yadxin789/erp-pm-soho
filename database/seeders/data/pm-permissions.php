<?php

/*
|--------------------------------------------------------------------------
| PM 模块权限目录（TC-07 新建）
|--------------------------------------------------------------------------
| 背景：pm 源库 permissions 表 0 行，鉴权靠 User::isAdmin()/isProjectManager()
| 与控制器内 role 判断。合并后统一走 spatie 权限，故必须先定义权限点。
| 命名约定：pm.{resource}.{action}，与 erp/soho 一致。
| 说明：本目录按 PM 现有路由（projects / tasks / collab / voice / chat /
|       file-repository / operation-logs / send-notification / ai / admin）
|       与六个角色的语义映射，不新增 PM 没有的能力。
*/

return [
    // 权限目录：group => [action => 中文名]
    'catalog' => [
        'dashboard' => ['view' => '查看工作台'],
        'project'   => [
            'view'   => '查看项目',
            'create' => '创建项目',
            'update' => '编辑项目',
            'delete' => '删除项目',
            'pause'  => '暂停/恢复项目',
            'gantt'  => '查看甘特图',
            'audit'  => '审核项目变更',
        ],
        'task' => [
            'view'    => '查看任务',
            'create'  => '创建任务',
            'update'  => '更新任务',
            'upload'  => '上传任务文件',
            'approve' => '审核任务',
        ],
        'collab' => [
            'view'   => '查看协同文档',
            'create' => '新建协同文档',
            'open'   => 'OnlyOffice 打开文档',
            'delete' => '删除协同文档',
        ],
        'voice' => [
            'join'   => '进入语音房',
            'manage' => '管理语音房',
        ],
        'chat' => [
            'view'   => '查看聊天',
            'send'   => '发送消息',
            'delete' => '删除消息',
            'clear'  => '清空会话',
        ],
        'file' => [
            'view'     => '查看文件库',
            'upload'   => '上传文件',
            'download' => '下载文件',
            'delete'   => '删除文件',
        ],
        'kb' => [
            'view'   => '查看知识库',
            'manage' => '维护知识库',
        ],
        'ai' => [
            'use'        => '使用 AI 助手',
            'settings'   => 'AI 设置',
            'delegation' => 'AI 委托令牌与变更提案',
        ],
        'notice' => [
            'view' => '查看通知',
            'send' => '发送系统通知',
        ],
        'trash' => [
            'view'   => '查看回收站',
            'manage' => '恢复/彻底删除',
        ],
        'log' => ['view' => '查看操作日志'],
        'admin' => [
            'users'       => '用户管理',
            'departments' => '部门管理',
            'roles'       => '角色管理',
            'task_types'  => '任务类型管理',
            'statuses'    => '状态管理',
            'settings'    => '系统设置',
            'backup'      => '数据备份',
            'services'    => '服务状态',
        ],
    ],

    // 角色映射：源角色 code => 权限名数组（'@all' 表示全部 pm.*）
    'roles' => [
        'admin' => ['@all'],

        // 项目创建人（原项目经理）
        'project_creator' => [
            'dashboard.view', 'project.view', 'project.create', 'project.update', 'project.delete',
            'project.pause', 'project.gantt',
            'task.view', 'task.create', 'task.update',
            'collab.view', 'collab.create', 'collab.open',
            'voice.join', 'chat.view', 'chat.send', 'chat.delete',
            'file.view', 'file.upload', 'file.download',
            'kb.view', 'kb.manage', 'ai.use', 'notice.view', 'trash.view',
        ],

        // 项目监督人（原项目管理部总经理）：可管理全部项目，但不能建/删
        'project_manager' => [
            'dashboard.view', 'project.view', 'project.update', 'project.pause', 'project.gantt', 'project.audit',
            'task.view', 'task.update', 'task.approve',
            'collab.view', 'collab.open',
            'voice.join', 'voice.manage', 'chat.view', 'chat.send',
            'file.view', 'file.download', 'kb.view', 'ai.use',
            'notice.view', 'notice.send', 'log.view', 'trash.view',
        ],

        // 任务执行人（原设计师）
        'task_executor' => [
            'dashboard.view', 'project.view',
            'task.view', 'task.update', 'task.upload',
            'collab.view', 'collab.open',
            'chat.view', 'chat.send',
            'file.view', 'file.upload', 'file.download',
            'kb.view', 'ai.use', 'trash.view',
        ],

        // 任务审核人（原主管）
        'task_auditor' => [
            'dashboard.view', 'project.view',
            'task.view', 'task.approve',
            'collab.view', 'collab.open',
            'chat.view', 'file.view', 'file.download', 'kb.view', 'trash.view',
        ],

        // 支援人员（原文员）
        'support' => [
            'dashboard.view', 'project.view', 'task.view',
            'file.view', 'file.upload', 'file.download',
            'kb.view', 'chat.view',
        ],
    ],
];
