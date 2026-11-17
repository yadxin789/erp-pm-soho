<?php

/*
| PM 模块菜单（TC-08 由 docs/menu/baseline-menu.json 生成，勿手改）
| key / group / label / icon / route / permission / roles / order / source
| route 为合并后真实路由名；permission 已对照 RBAC 库校验；roles 为模块内角色白名单（null=不限）
*/

return array (
  0 => 
  array (
    'key' => 'pm.dashboard',
    'group' => '工作台',
    'label' => '首页',
    'icon' => 'fa-solid fa-home',
    'route' => 'pm.dashboard',
    'permission' => 'pm.dashboard.view',
    'roles' => NULL,
    'order' => 1,
    'source' => 'dashboard',
    'module' => 'pm',
    'sort' => 1,
    'children' => 
    array (
    ),
  ),
  1 => 
  array (
    'key' => 'pm.ai.settings',
    'group' => '智能助手',
    'label' => 'AI设置',
    'icon' => 'fa-solid fa-robot',
    'route' => 'pm.ai.settings',
    'permission' => 'pm.ai.settings',
    'roles' => NULL,
    'order' => 2,
    'source' => 'ai.settings',
    'module' => 'pm',
    'sort' => 2,
    'children' => 
    array (
    ),
  ),
  2 => 
  array (
    'key' => 'pm.ai.chat',
    'group' => '智能助手',
    'label' => 'AI智能助手',
    'icon' => 'fa-solid fa-robot',
    'route' => 'pm.ai.chat',
    'permission' => 'pm.ai.use',
    'roles' => NULL,
    'order' => 3,
    'source' => 'ai.chat',
    'module' => 'pm',
    'sort' => 3,
    'children' => 
    array (
    ),
  ),
  3 => 
  array (
    'key' => 'pm.ai-delegation.index',
    'group' => '智能助手',
    'label' => 'AI 授权中心',
    'icon' => 'fa-solid fa-key',
    'route' => 'pm.ai-delegation.index',
    'permission' => 'pm.ai.delegation',
    'roles' => NULL,
    'order' => 4,
    'source' => 'ai-delegation.index',
    'module' => 'pm',
    'sort' => 4,
    'children' => 
    array (
    ),
  ),
  4 => 
  array (
    'key' => 'pm.admin.users',
    'group' => '系统管理',
    'label' => '用户管理',
    'icon' => 'fa-solid fa-users',
    'route' => 'pm.admin.users',
    'permission' => 'pm.admin.users',
    'roles' => NULL,
    'order' => 5,
    'source' => 'admin.users',
    'module' => 'pm',
    'sort' => 5,
    'children' => 
    array (
    ),
  ),
  5 => 
  array (
    'key' => 'pm.admin.departments',
    'group' => '系统管理',
    'label' => '部门管理',
    'icon' => 'fa-solid fa-building',
    'route' => 'pm.admin.departments',
    'permission' => 'pm.admin.departments',
    'roles' => NULL,
    'order' => 6,
    'source' => 'admin.departments',
    'module' => 'pm',
    'sort' => 6,
    'children' => 
    array (
    ),
  ),
  6 => 
  array (
    'key' => 'pm.admin.roles',
    'group' => '系统管理',
    'label' => '角色管理',
    'icon' => 'fa-solid fa-key',
    'route' => 'pm.admin.roles',
    'permission' => 'pm.admin.roles',
    'roles' => NULL,
    'order' => 7,
    'source' => 'admin.roles',
    'module' => 'pm',
    'sort' => 7,
    'children' => 
    array (
    ),
  ),
  7 => 
  array (
    'key' => 'pm.admin.statuses',
    'group' => '系统管理',
    'label' => '状态管理',
    'icon' => 'fa-solid fa-toggle-on',
    'route' => 'pm.admin.statuses',
    'permission' => 'pm.admin.statuses',
    'roles' => NULL,
    'order' => 8,
    'source' => 'admin.statuses',
    'module' => 'pm',
    'sort' => 8,
    'children' => 
    array (
    ),
  ),
  8 => 
  array (
    'key' => 'pm.admin.task-types',
    'group' => '系统管理',
    'label' => '任务类型管理',
    'icon' => 'fa-solid fa-list-alt',
    'route' => 'pm.admin.task-types',
    'permission' => 'pm.admin.task_types',
    'roles' => NULL,
    'order' => 9,
    'source' => 'admin.task-types',
    'module' => 'pm',
    'sort' => 9,
    'children' => 
    array (
    ),
  ),
  9 => 
  array (
    'key' => 'pm.admin.chat-messages',
    'group' => '系统管理',
    'label' => '聊天记录管理',
    'icon' => 'fa-solid fa-comments',
    'route' => 'pm.admin.chat-messages',
    'permission' => 'pm.chat.view',
    'roles' => NULL,
    'order' => 10,
    'source' => 'admin.chat-messages',
    'module' => 'pm',
    'sort' => 10,
    'children' => 
    array (
    ),
  ),
  10 => 
  array (
    'key' => 'pm.admin.file-repository',
    'group' => '文件与知识',
    'label' => '文件仓库',
    'icon' => 'fa-solid fa-folder-open',
    'route' => 'pm.admin.file-repository',
    'permission' => 'pm.file.view',
    'roles' => NULL,
    'order' => 11,
    'source' => 'admin.file-repository',
    'module' => 'pm',
    'sort' => 11,
    'children' => 
    array (
    ),
  ),
  11 => 
  array (
    'key' => 'pm.admin.trash',
    'group' => '系统管理',
    'label' => '回收站',
    'icon' => 'fa-solid fa-trash',
    'route' => 'pm.admin.trash',
    'permission' => 'pm.trash.manage',
    'roles' => NULL,
    'order' => 12,
    'source' => 'admin.trash',
    'module' => 'pm',
    'sort' => 12,
    'children' => 
    array (
    ),
  ),
  12 => 
  array (
    'key' => 'pm.knowledge.index',
    'group' => '文件与知识',
    'label' => '知识库',
    'icon' => 'fa-solid fa-book',
    'route' => 'pm.knowledge.index',
    'permission' => 'pm.kb.view',
    'roles' => NULL,
    'order' => 13,
    'source' => 'knowledge.index',
    'module' => 'pm',
    'sort' => 13,
    'children' => 
    array (
    ),
  ),
  13 => 
  array (
    'key' => 'pm.admin.operation-logs',
    'group' => '系统管理',
    'label' => '操作日志',
    'icon' => 'fa-solid fa-file-text',
    'route' => 'pm.admin.operation-logs',
    'permission' => 'pm.log.view',
    'roles' => NULL,
    'order' => 14,
    'source' => 'admin.operation-logs',
    'module' => 'pm',
    'sort' => 14,
    'children' => 
    array (
    ),
  ),
  14 => 
  array (
    'key' => 'pm.admin.send-notification',
    'group' => '通知',
    'label' => '发送通知',
    'icon' => 'fa-solid fa-bell',
    'route' => 'pm.admin.send-notification',
    'permission' => 'pm.notice.send',
    'roles' => NULL,
    'order' => 15,
    'source' => 'admin.send-notification',
    'module' => 'pm',
    'sort' => 15,
    'children' => 
    array (
    ),
  ),
  15 => 
  array (
    'key' => 'pm.admin.services',
    'group' => '系统管理',
    'label' => '后台服务',
    'icon' => 'fa-solid fa-server',
    'route' => 'pm.admin.services',
    'permission' => 'pm.admin.services',
    'roles' => NULL,
    'order' => 16,
    'source' => 'admin.services',
    'module' => 'pm',
    'sort' => 16,
    'children' => 
    array (
    ),
  ),
  16 => 
  array (
    'key' => 'pm.admin.wecom.settings',
    'group' => '系统管理',
    'label' => '企业微信',
    'icon' => 'fa-solid fa-weixin',
    'route' => 'pm.admin.wecom.settings',
    'permission' => 'pm.admin.settings',
    'roles' => NULL,
    'order' => 17,
    'source' => 'admin.wecom.settings',
    'module' => 'pm',
    'sort' => 17,
    'children' => 
    array (
    ),
  ),
  17 => 
  array (
    'key' => 'pm.project-template.index',
    'group' => '项目',
    'label' => '项目模板',
    'icon' => 'fa-solid fa-clone',
    'route' => 'pm.project-template.index',
    'permission' => 'pm.project.view',
    'roles' => NULL,
    'order' => 18,
    'source' => 'project-template.index',
    'module' => 'pm',
    'sort' => 18,
    'children' => 
    array (
    ),
  ),
  18 => 
  array (
    'key' => 'pm.project.create',
    'group' => '项目',
    'label' => '创建项目',
    'icon' => 'fa-solid fa-plus-circle',
    'route' => 'pm.project.create',
    'permission' => 'pm.project.create',
    'roles' => NULL,
    'order' => 19,
    'source' => 'project.create',
    'module' => 'pm',
    'sort' => 19,
    'children' => 
    array (
    ),
  ),
  19 => 
  array (
    'key' => 'pm.project.my-projects',
    'group' => '项目',
    'label' => '我的项目列表',
    'icon' => 'fa-solid fa-folder-open',
    'route' => 'pm.project.my-projects',
    'permission' => 'pm.project.view',
    'roles' => NULL,
    'order' => 20,
    'source' => 'project.my-projects',
    'module' => 'pm',
    'sort' => 20,
    'children' => 
    array (
    ),
  ),
  20 => 
  array (
    'key' => 'pm.extension.index',
    'group' => '项目',
    'label' => '审核延期申请',
    'icon' => 'fa-solid fa-calendar',
    'route' => 'pm.extension.index',
    'permission' => 'pm.project.audit',
    'roles' => NULL,
    'order' => 21,
    'source' => 'extension.index',
    'module' => 'pm',
    'sort' => 21,
    'children' => 
    array (
    ),
  ),
  21 => 
  array (
    'key' => 'pm.file.resource-files',
    'group' => '文件与知识',
    'label' => '资料文件',
    'icon' => 'fa-solid fa-file',
    'route' => 'pm.file.resource-files',
    'permission' => 'pm.file.view',
    'roles' => NULL,
    'order' => 22,
    'source' => 'file.resource-files',
    'module' => 'pm',
    'sort' => 22,
    'children' => 
    array (
    ),
  ),
  22 => 
  array (
    'key' => 'pm.notification.index',
    'group' => '通知',
    'label' => '通知中心',
    'icon' => 'fa-solid fa-bell',
    'route' => 'pm.notification.index',
    'permission' => 'pm.notice.view',
    'roles' => NULL,
    'order' => 23,
    'source' => 'notification.index',
    'module' => 'pm',
    'sort' => 23,
    'children' => 
    array (
    ),
  ),
  23 => 
  array (
    'key' => 'pm.profile.wecom.index',
    'group' => '个人',
    'label' => '企业微信绑定',
    'icon' => 'fa-solid fa-weixin',
    'route' => 'pm.profile.wecom.index',
    'permission' => NULL,
    'roles' => NULL,
    'order' => 24,
    'source' => 'profile.wecom.index',
    'module' => 'pm',
    'sort' => 24,
    'children' => 
    array (
    ),
  ),
  24 => 
  array (
    'key' => 'pm.wecom.file.index',
    'group' => '文件与知识',
    'label' => '企微文件收件箱',
    'icon' => 'fa-solid fa-inbox',
    'route' => 'pm.wecom.file.index',
    'permission' => 'pm.file.view',
    'roles' => NULL,
    'order' => 25,
    'source' => 'wecom.file.index',
    'module' => 'pm',
    'sort' => 25,
    'children' => 
    array (
    ),
  ),
  25 => 
  array (
    'key' => 'pm.project.index',
    'group' => '项目',
    'label' => '全部项目',
    'icon' => 'fa-solid fa-eye',
    'route' => 'pm.project.index',
    'permission' => 'pm.project.view',
    'roles' => NULL,
    'order' => 26,
    'source' => 'project.index',
    'module' => 'pm',
    'sort' => 26,
    'children' => 
    array (
    ),
  ),
  26 => 
  array (
    'key' => 'pm.task.my-audit-tasks.pending-audit',
    'group' => '任务',
    'label' => '我的待审核任务列表',
    'icon' => 'fa-solid fa-tasks',
    'route' => 'pm.task.my-audit-tasks.pending-audit',
    'permission' => 'pm.task.approve',
    'roles' => NULL,
    'order' => 27,
    'source' => 'task.my-audit-tasks.pending-audit',
    'module' => 'pm',
    'sort' => 27,
    'children' => 
    array (
    ),
  ),
  27 => 
  array (
    'key' => 'pm.task.my-tasks.pending-execution',
    'group' => '任务',
    'label' => '我的待执行任务列表',
    'icon' => 'fa-solid fa-tasks',
    'route' => 'pm.task.my-tasks.pending-execution',
    'permission' => 'pm.task.view',
    'roles' => NULL,
    'order' => 28,
    'source' => 'task.my-tasks.pending-execution',
    'module' => 'pm',
    'sort' => 28,
    'children' => 
    array (
    ),
  ),
  28 => 
  array (
    'key' => 'pm.support.index',
    'group' => '文件与知识',
    'label' => '支援中心',
    'icon' => 'fa-solid fa-life-ring',
    'route' => 'pm.support.index',
    'permission' => 'pm.task.view',
    'roles' => NULL,
    'order' => 29,
    'source' => 'support.index',
    'module' => 'pm',
    'sort' => 29,
    'children' => 
    array (
    ),
  ),
  29 => 
  array (
    'key' => 'pm.file.manage-resource-files',
    'group' => '文件与知识',
    'label' => '资料文件管理',
    'icon' => 'fa-solid fa-file',
    'route' => 'pm.file.manage-resource-files',
    'permission' => 'pm.file.view',
    'roles' => NULL,
    'order' => 30,
    'source' => 'file.manage-resource-files',
    'module' => 'pm',
    'sort' => 30,
    'children' => 
    array (
    ),
  ),
  30 => 
  array (
    'key' => 'pm.my-trash',
    'group' => '其他',
    'label' => '我的回收站',
    'icon' => 'fa-solid fa-circle-dot',
    'route' => 'pm.my-trash',
    'permission' => 'pm.trash.view',
    'roles' => NULL,
    'order' => 31,
    'source' => 'my-trash',
    'module' => 'pm',
    'sort' => 31,
    'children' => 
    array (
    ),
  ),
);
