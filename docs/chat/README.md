# TC-10 聊天与语音

三源的聊天/语音合并为 **一套表 + 一套服务 + 一个入口**：ERP 的群聊/私聊、PM 的项目留言板、以及两侧的语音会议，模块内不再各写一套。

## 1. 统一房间模型

一条消息落在哪，由三元组决定（`chat_messages` 13 列）：

| 源 | 原表达 | Core 归一 |
|---|---|---|
| ERP 群聊 | `is_private=false` + `receiver_id=null` | `module=erp`、`room_type=group`、`room_id=0` |
| ERP 私聊 | `is_private=true` + `receiver_id` | `module=erp`、`room_type=private`、`room_id=对方 user id`（双向取并） |
| PM 项目留言板 | 消息表挂 `project_id` | `module=pm`、`room_type=project`、`room_id=项目 id` |

`module` 是隔离维度：erp 群里看不到 pm/soho 的消息（验收脚本有断言）。附件不落单独列，统一进 `attachments`(JSON)：`[{index,path,name,size,mime}]`，文件实体在 `storage/app/public/chat/{module}/{日期}/`。

「清空我看到的记录」用 `chat_clear_states`（user_id + module + room_key），只影响本人视图，不删真实数据——与 ERP `ChatClearState`、PM 的 `chat_clear_states` 语义一致。

## 2. 代码

| 位置 | 职责 |
|---|---|
| `Core\Models\ChatMessage` | 房间 scope（`forRoom`/`between`/`afterClear`）、`toFrontend()`（视图与 JSON 共用）、软删 |
| `Core\Models\ChatClearState` | `keyFor(roomType,roomId)`、`markCleared()` |
| `Core\Services\ChatService` | `listFor`（按本人清空标记裁剪）、`send`（附件白名单/20MB/最多 5 个）、`deleteOwn`、`clearMine`、`download`、`canView` |
| `Core\Http\Controllers\ChatController` | `index`、`messages`（增量轮询 `?after=`）、`send`、`destroy`、`clearMine`、`file` |
| `resources/views/core/chat/index.blade.php` | 三栏：房间切换/私聊选择/语音入口 + 消息流 + 表情/附件/发送；5 秒增量轮询，`aria-live` 播报新消息 |

路由（`auth` 内）：`core.chat.index`、`core.chat.messages`、`core.chat.send`、`core.chat.clear`、`core.chat.destroy`、`core.chat.file`。

## 3. 语音：母版取 ERP 的理由

- ERP 是 **应用内信令中转**（`voice_signals` + presence 心跳），浏览器 WebRTC mesh 直连，不依赖任何外部服务 —— 内网/断外网可用。
- PM 原本依赖 Swoole/Janus 信令服务（`config/voice.signal_ws`/`janus_ws`）+ OnlyOffice 才能开会，合并后这套外部依赖不能成为"合并后必须部署"的前提。
- 所以 A 文档的「语音取 ERP」被落实：Core 用 ERP 方案；PM 的 `hookEnter/hookLeave` 保留成 `POST /voice/hook/{enter,leave}`，等外部信令服务具备时仍可回写同一张表（不丢能力）。

表：`voice_rooms`(10) / `voice_participants`(8) / `voice_signals`(9)。在线判定用 `voice_participants.updated_at` 当心跳（30 秒内视为在会），省掉 ERP 单独建的 `voice_presence` 表。

| 位置 | 职责 |
|---|---|
| `Core\Services\VoiceService` | `ensureRoom`（按 module+type+name 复用，关过的房可再开）、`join/heartbeat/leave`、`signal`、`poll`（每信令只投递一次，`consumed_at`）、`members`、`close`（清未消费信令 + 闭合在会记录）、`token`（`HMAC(room|user|ts, app.key)`）、`history` |
| `Core\Http\Controllers\VoiceController` | 页面 + `token`/`signal`/`poll`/`members`/`end`/`history`，外加两个兼容 hook |
| `resources/views/core/voice/index.blade.php` | 加入/静音/离开/结束、在会成员、历史；WebRTC mesh：新入会者向在会成员逐个 offer，2 秒轮询信令，`ontrack` 挂 `<audio>` |

**参数约定（踩过的坑）**：房间上下文用 `room_type`（project/group/adhoc），信令类型用 `type`（offer/answer/candidate/leave/heartbeat）。两者曾共用 `type` 导致「加入会议时房间类型被写成 offer」，已在 Controller 与视图里分开。

## 4. 演示数据

`ChatDemoSeeder`：erp 群聊 4 条（admin / multi_module 对话）、pm 项目 #101 留言 3 条、erp 私聊 3 条（admin ↔ multi_module 双向）、soho 群聊 1 条，共 11 条。

## 5. 验收

`php database/scripts/chat_verify.php` —— 全部通过：

```
房间分布：erp 7 / pm 3 / soho 1，合计 11（group / project#101 / private 三种房都有数据）
模块隔离：erp 群聊仅 erp 消息、pm 留言板仅 pm 消息、erp 表下无 project 房（不串）
私聊双向：multi_module 与 admin 看到的条数与消息集合完全一致
清空我的记录：admin 视角 4→4 不变，multi_module 视角 4→0
发消息：after 参数只返回新增；本人可撤回（软删保留）；不能撤回他人消息
语音：建房 open → 2 人在会 → offer 只投递 1 次 → 第二次拉取 0 条 → 发信人不收自己的信令
     → 离会后剩 1 人 → 结束写 ended_at、闭合在会记录、清理未消费信令 → 令牌签名 64 字符（HMAC-SHA256）
```

界面取证 `docs/screenshots-tc10/`（967×883）：

| 图 | 内容 |
|---|---|
| `01-chat-erp-group.png` | ERP 模块群聊，左侧房间/私聊/语音入口，右侧消息流与表情栏 |
| `02-chat-erp-send.png` | **从页面发送框真实发出一条**（AJAX 落库，未刷新即追加，时间 15:43）|
| `03-chat-pm-project.png` | PM 账号看项目 #101 留言板（3 条，含跨来源历史口径） |
| `04-chat-private.png` | admin ↔ multi_module 私聊双向可见 |
| `05-chat-cross-403.png` | ERP 管理员访问 `pm` 聊天 → 统一 403（准入链 D15 生效） |
| `06-voice-room.png` | 语音房页面：加入/静音/离开/结束、参会须知、本模块会议历史 |

会话内实测语音接口：`POST /voice/token` → 200 带 `signature`(64 hex)、`POST /voice/signal`（heartbeat）→ 200、`GET /voice/poll` → 200 返回成员与房间状态。

## 6. 已知边界

- 项目留言板的**项目级权限**（PM 原 `assertProjectMember`）暂未在 Core 收紧，现为「同模块登录用户可见」；等 B-03 项目域迁入后由项目模块按 `participants()` 复验。
- 语音只做 mesh（2~6 人小会）。多人会议需要 SFU（Janus/LiveKit），属外部依赖，若用户要求再评估。
- 附件下载的权限目前按 `canView` 判定（私有房仅双方；其它房同模块可见），未做文件级审计。
- ERP 的 `PrivateChatController`/`PrivateMessageController` 与 PM 的 `messages.*` 路由在模块迁入时改为跳 `core.chat.*`，源表数据按 room 三元组导入（脚本待 B-05 用户迁移后一并做，理由同 TC-09）。
