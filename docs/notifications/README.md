# TC-09 站内通知中心

三套系统的通知（ERP `notifications` 49 行、PM `notifications` 17 行、SOHO sqlite `notifications` 5 行）在合并后**只有一份表、一套代码**，模块内的通知入口统一改为跳 Core 路由，三模块各自的通知不再各写一套。

## 1. 数据

- `notifications` 17 列（并集）：`id / user_id / sender_id / module / type / title / content / data(JSON) / link / priority / is_read / read_at / related_type / related_id / tenant_id / created_at / updated_at`
  - `module` 取 `core|erp|pm|soho`，是**过滤维度**而不是分表维度；`data` 里放各源自己的附加字段（ERP 的 `data`、SOHO 的 `data_json` 都并进来）。
  - 索引 `(user_id, is_read)` 支撑角标查询；FK `user_id → users` cascade。
- `notification_templates` 10 列：`module / code / title / content / type / variables(JSON) / enabled`，`code` 唯一——模板随模块建，但存在同一张表里（后续 TC 做发送编排时直接用）。

## 2. 代码

| 位置 | 职责 |
|---|---|
| `app/Core/Models/Notification.php` | `casts`（data=array / is_read=bool / read_at=datetime）、`scopeUnread`、`scopeForModule`、`markAsRead`、`user()`/`sender()` |
| `app/Core/Services/NotificationService.php` | `send`（module 非法抛 `InvalidArgumentException`）、`unreadCount`、`unreadByModule`、`totalByModule`、`queryFor`、`markRead`、`markAllRead`、`delete`；**所有读操作都按 user 过滤**，越权返回 null/false |
| `app/Core/Http/Controllers/NotificationController.php` | `index`（`?module=`、`?status=unread\|read`）、`read`、`readAll`、`destroy`、`unreadCount`（JSON） |
| `resources/views/core/notifications/index.blade.php` | 列表 + 模块 tab（计数=该模块总条数，与列表长度一致）+ 未读/已读筛选 + 单条/全部标已读 + 删除 |
| `resources/views/core/partials/topbar.blade.php` | 顶栏：站点名 / 铃铛（未读角标）/ 显示名 / 退出；已接进通知页与个人中心 |

路由（`routes/core.php`，均在 `auth` 内）：

```
GET     /notifications                  core.notifications.index
GET     /notifications/unread-count     core.notifications.unread-count   → {"total":n,"by_module":{...}}
POST    /notifications/read-all         core.notifications.read-all       （可带 module 只清某模块）
POST    /notifications/{id}/read        core.notifications.read
DELETE  /notifications/{id}             core.notifications.destroy
```

`read` 只把站外链接（`http…`）拦回 `/notifications`，站内相对链接直接跳——避免 `data` 里带外链把用户带走。

## 3. 演示数据

`NotificationDemoSeeder` 播 9 条，覆盖 4 个演示账号、3 个模块、两种已读状态（每账号第 2 条标已读），`data.demo=true` 便于识别与清理：

| 账号 | 条数 | 模块/类型 |
|---|---|---|
| `admin`（ERP） | 3 | erp approval / inventory / sales |
| `pm_director`（PM） | 2 | pm task / project |
| `admin@soho.demo`（SOHO） | 2 | soho order / refund |
| `multi_module`（ERP+SOHO） | 2 | erp sales + soho inventory |

## 4. 历史通知迁移（脚本已就绪，真写库待 B-05）

`database/scripts/notif_migrate.php`

- 默认**干跑**，只出报告；`--apply` 才写库；`--name-match` 才启用姓名弱匹配。
- 匹配策略：`email` → `username` 算强匹配；**姓名默认不参与**（三源同名不同人常见），且即便开启也只认「同名在合并库唯一」。
- 字段映射：ERP `message→content`、`data→data`、`read_at`；PM `content`、`is_read`、`related_type/related_id`；SOHO `content`、`data_json→data`、`priority`、`tenant_id`（只迁 `user_type in (admin,user)` 的 2 条，`shop_manager/staff` 是店铺子账号语义，需等 SOHO 店铺角色迁入后再定）。
- 幂等键 `data.source = "erp:12"`，重复跑不会重复插入。

真实干跑（`docs/notifications/migration-report.json`）：

```
ERP  源 49 条 → 强匹配 0
PM   源 17 条 → 强匹配 0
SOHO 源  2 条 → 强匹配 0
合计 68 条，可挂 0 条
```

原因：合并库只有 5 个演示账号（`admin@erp.demo` / `pm_director` / 空 username 的 `admin@soho.demo` / `multi_module` / `disabled_user`），与三源账号（ERP `admin@admin.com`、PM 的 user 3/5/7…、SOHO `admin@ddwl-soho.com`）**零交集**。开启 `--name-match` 时能挂 28 条（ERP 27 + SOHO 1，全部落在合并库 `admin` 名上），这属弱匹配，**没有写库**——若先按姓名灌进去，B-05 真用户迁入后会因幂等键已被占用而永久挂错人。

结论：历史通知迁移的前置依赖是 **B-05 用户迁移**，本轮的交付是「表 + 代码 + 脚本 + 报告 + 可验证的演示数据」，不假装历史已迁完。

## 5. 验收

`php database/scripts/notif_verify.php` —— 全部 OK：

```
表内分布：erp 4（未读 3）/ pm 2（未读 1）/ soho 3（未读 1），合计 9
未读数：聚合 unread 与直查 SQL 一致（admin 2 / pm_director 1 / multi_module 1 / admin@soho.demo 1）
       分模块之和 = 总数；tab 计数之和 = 该账号全部通知
模块过滤：service 与 db 一致（admin erp 3 / pm 0 / soho 0 / core 0；不过滤 3）
已读流转：新通知→未读；标已读后未读数 -1 且 read_at 落库；临时通知已删除
越权保护：不能标他人通知为已读、不能删他人通知
```

界面取证 `docs/screenshots-tc09/`（967×883，`with_cursor=False`）：

| 图 | 内容 |
|---|---|
| `01-notifications-all.png` | SOHO 账号全部列表，铃铛角标 2、SOHO tab 2、未读高亮/已读置灰 |
| `02-notifications-module-soho.png` | `?module=soho` 过滤生效，tab 选中态 |
| `03-notifications-unread.png` | `?status=unread` 只剩未读 |
| `04-notifications-read-all.png` | 点「全部标为已读」→ 顶部提示「已将 2 条通知标记为已读」、**角标消失**、未读 0 |
| `05-notifications-admin-erp.png` | ERP 管理员只看得到 erp 3 条，badge=3 |
| `06-guest-redirect-login.png` | 游客直接访问 `/notifications` → 302 到 `/login` |
| `07-notifications-multimodule.png` | 跨模块账号同页看到 erp + soho 两条，`module` tab 计数各自 1 |

角标接口在金标会话内实测：`GET /notifications/unread-count` → `200 {"total":0,"by_module":{...}}`；未登录访问该地址返回登录页（`auth` 生效）。

## 6. 已知边界

- 角标是「进入页面时渲染 + 页面内 AJAX 刷新」，没有做长连接/轮询推送；跨标签页实时性要等 TC-13 统一收口时决定是否上 Echo。
- 顶栏目前接在通知页与个人中心；模块内页面接入需在模块布局迁入时（B-02~B-04）插同一个 partial。
- 模块内旧通知入口（`erp.notifications.*` 等）在模块迁入时改为 `redirect()->route('core.notifications.index')`，源表数据按上面脚本导入。
