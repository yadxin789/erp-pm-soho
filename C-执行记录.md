
---

## §二十一 第 2 条：PM 项目页与项目内协作（2026-11-17）

### 21.1 结论

第 2 条要求「PM 项目内聊天 / 协同办公 / 电话会议」已落地：新增 6 张表、6 个模型、4 个控制器、9 个视图，`/pm/project` 单数路由组 12 条与参考同形（`project/{project}`、`project/{project}/collab`、`project/{project}/messages`、`project/{project}/chat/{targetUserId}`、`project/{project}/voice`）。演示项目 `GUI-01 / G20260928173306` 与参考基线同一条：37 个任务、7 名成员、状态「进行中」，概览 6 格统计 `37/0/37/0/0/0` 与参考截图逐格一致。

### 21.2 表与代码

| 表 | 行数（演示） | 说明 |
| --- | --- | --- |
| `projects` | 1 | name/code/version/product_name/product_model/creator_id/start_date/end_date/status/progress/paused_*，软删除 + deleted_by |
| `tasks` | 37 | project_id/department_id/executor_id/auditor_id/status(pending/for_audit/completed/rejected/overdue)/task_type_id/is_overdue |
| `task_types` | 4 | 需求分析 / 方案设计 / 评审 / 打样验证 |
| `collab_documents` | 0 | 项目协作文档记录（doc_key、version、expires_at） |
| `project_messages` | 0 | 项目群聊 + 项目内私聊（is_private + receiver_id） |
| `project_logs` | 1 | project.create 日志 |

代码：`app/Pm/Models/{Project,Task,TaskType,CollabDocument,ProjectMessage,ProjectLog}.php`、`app/Pm/Http/Controllers/{ProjectController,CollabController,ProjectMessageController,ProjectVoiceController}.php`、`database/migrations/2026_11_17_110001_create_pm_project_tables.php`、`database/seeders/ProjectDemoSeeder.php`。

视图：`resources/views/pm/{layouts/app,partials/topstrip,project/{index,show,gantt,logs,history,collab,collab-open,messages,voice,partials/tabs}}.blade.php`、样式 `public/assets/pm/css/pm.css`。

### 21.3 与参考的对照

- 项目页：左侧 PM 深藏青导航（三行品牌）+ 7 个 Tab（概览/甘特/履历/日志/协同办公/聊天室/电话会议）+ 黄色「进行中」徽标 + 4 张信息卡一行排列 + 成员滚动列表 + 时间信息表 + 6 格彩色统计，与 `docs/reference-shots/pm-project-show.png` 同构。
- 协同办公：蓝色 26px「协同办公」标题 + 右上蓝色「+ 新建文档」+ 空状态文件图标与同文案；参考同屏。
- 聊天室：左「项目群聊/全体成员」选中态 + 「项目成员」7 人圆形字母头像 + 离线；右标题「GUI-01 - 聊天室」+ 红色描边「清空我的留言板」+ 空状态「暂无聊天记录，快开始聊天吧！」+ 😊表情/📎文件 + 蓝发送，与 `pm-project-chat.png` 同构。
- 电话会议：项目建房（`module=pm, room_type=project, room={id}`），成员网格 + 「加入项目会议」；非成员**不渲染 Tab 与入口**，直接访问会 302 回项目页并提示，而不是 403。

### 21.4 关键实现坑

- 坑 51：路由参数名与控制器变量不一致会静默失败。参考路由写 `project/{projectId}/messages`，若控制器签名为 `Project $project` 则隐式绑定不生效，拿到的是空模型 → `route('pm.project.show')` 抛 `Missing required parameter`。统一把 messages/chat/file 路由参数改名 `{project}` 后恢复。
- 坑 52：FA5 精简包缺 `fa-bar-chart`/`fa-comment-dots`/`fa-user-friends`/`fa-phone-slash`（都是 FA4/6 名），页面出现空图标。已统一换 `fa-chart-bar`/`fa-comments`/`fa-users`/`fa-phone`。
- 坑 53：`/pm`、`/erp`、`/soho` 三个模块根此前返回占位 JSON，登录后会直接看到 JSON 文本页。`/pm` 改 302 → 项目列表；`/erp`、`/soho` 暂 302 → 统一门户并提示「业务菜单正在迁入」。
- 坑 54：`VoiceController::resolveModule` 原本 `abort_unless(...,403)`，与「无 403」冲突；改为收敛到本人有权模块 + `flash('warning')`。

### 21.5 未做与边界

- 项目「新建/变更/暂停/恢复/删除」与任务 CRUD、任务审核流（for_audit→completed/rejected）尚未迁入，页面上「变更项目/暂停项目」按钮暂置灰。
- 甘特图用任务起止日期画条（同色按状态），没有参考版的拖拽改期与依赖线。
- 协作文档只落记录，OnlyOffice 未配置时明确提示；在线编辑、版本、`callback` 待接（原 PM 走 `collab.open` + Document Server）。
- 电话会议是 Core WebRTC 信令实现（`voice_rooms/voice_signals`），**不是**原 PM 的 Janus；会议界面目前是「成员网格 + 加入语音房」，加入后跳 Core 语音房页面。
- 项目内私聊页面复用同一聊天室视图（左栏切换 peer），未做参考的「未读/清空单聊」细分。
## 2026-11-19 收尾修复（A-ERP 对齐）
- `warehouse_manager` 缺 22 条采购/生产权限（数据缺口，非代码）→ 已按参考补齐，`role_has_permissions` 只补不删。
- 授权失败回落由 `admin.dashboard` 改为 `RoleScope::home($user)`（业务角色回 `app.dashboard`）。
- `AppServiceProvider` 补 `Paginator::useBootstrapFive()` + `pagination.custom`，恢复「共 N 条…」分页行。
- 人事/项目/文件/岗位/聊天页的部门、岗位、账号下拉按 `ModuleContext` 收敛到本模块（人事新建 46.8%→93.5%，项目新建 56.5%→97.4%）。
- `positions` 导入 16 条（电商部岗位按已删规则跳过）；修正 `App\Support\RoleCatalog` → `App\Erp\Support\RoleCatalog` 残留引用。
- 155 页正文比对：hr/asset/ec 152/155，project 150/155，warehouse/quality/accounting 仅剩「参考 403 vs 合并 302」刻意偏差。SOHO/PM 全量回归无变化（0×500 / 0×403）。
