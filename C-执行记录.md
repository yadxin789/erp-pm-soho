
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

---

## §二十二 P0 四项收口（2026-11-19）

> 完整逐批记录见交付目录 `C-执行记录.md` §38~§55；此处为仓库内摘要。

### 22.1 P0-1 列表计数与空态（`b244299`）

48 个模板的「计数 + 分页」由 `hasPages()` 改为 `total() > 0`；`pagination/custom.blade.php` 单页只渲染计数行；空态统一为「图标 + 一句说明」。

### 22.2 P0-2 SOHO 占位页清零（`b244299` → `7bf8baa`，16 个批次）

| 批次 | 页面数 | 控制器 |
| --- | --- | --- |
| SKU / 商品版本 / 订单拆分合并缺货 | 5 | `Order\OrderOperationController` |
| 订单待履约 / 履约单 / 分仓 / 导出 | 4 | 同上 |
| 售后（申请/补充/进度/沟通/日志） | 5 | `Aftersale\AfterSaleBoardController` |
| 客户标签 CRUD | 1 | `Customer\CustomerTagController` |
| 仓储 12 页（库区/库位/预警/入出库/盘点/调整/单据/退货/库龄/周转） | 12 | `Warehouse\WarehouseBoardController` |
| 财务 8 页（退款/结算/成本分摊/对账/结算审计/成本审计/差异审批） | 8 | `Finance\FinanceBoardController` |
| 商品 8 页（导入/审核/变更/SPU/待改/退回/异常） | 8 | `Product\ProductBoardController` |
| 平台 5 页（Listing/一键上架/任务/类目映射/属性） | 5 | `Platform\PlatformBoardController` |
| 集成同步 5 页（连接状态/API 健康/限流/失败/重试） | 5 | `Integration\IntegrationBoardController` |
| 日志事件 3 页（Webhook/失败/事件重放） | 3 | `Log\WebhookLogController` |
| 促销营销 4 页（内部促销/分析/报名/规则） | 4 | `Promotion\PromotionBoardController` |
| 价格报表 2 页（调价历史/采购报表） | 2 | `Price\PriceReportController` |
| 客户隐私（方案①建表全流程） | 1 | `Customer\CustomerPrivacyController` |

结果：`routes/soho.php` 中 `view('placeholder')` 计数 = **0**；全量 548 页 0×500 / 0×404 / 0×403。

**新增表**：`soho_customer_privacy_requests`（迁移 `2026_11_19_200000_*`，已执行）。

### 22.3 P0-3 表单防护（`f1540f7`）

`layouts/partials/form-guard.blade.php`（提交锁 + 原生化校验放行 + 提示条 6 秒收起）接入 5 套布局；flash 补 `warning` / `info` 两档。

### 22.4 P0-4 响应式（`6599e54`）

`public/assets/core/css/responsive.css` + `layouts/partials/responsive.blade.php`：裸表格自动补 `.table-responsive`、窄屏注入汉堡按钮。踩坑：Core 侧栏类名 `.dw-nav`、表格类名 `dw-table`。

### 22.5 验收基线（`aa74f10`）

| 模块 | 200 | 302 | 500/404/403 |
| --- | --- | --- | --- |
| core | 15 | 2 | 0 |
| erp | 155 | 2 | 0 |
| pm | 68 | 5 | 0 |
| soho | 288 | 13 | 0 |

### 22.6 下一步

P1 业务深化：SOHO 高价值页补深度闭环（履约执行、退款闭环、调拨审批链）；ERP 只读模块补写操作；PM 项目内电话会议去除 Janus 外部依赖。

---

## §二十三 P1 业务深化：SOHO 履约与退款闭环（2026-11-19）

### 23.1 P1-1 履约单执行闭环（`463a39a`）

状态机 `pending → picked → packed → shipped`，任意未发货状态可 `cancelled`。

| 动作 | 路由 | 权限 |
| --- | --- | --- |
| 详情 | `GET soho/order/fulfillment/{id}` | order.view |
| 指派拣货人 | `POST .../assign` | order.edit |
| 拣货完成 | `POST .../pick` | order.edit |
| 打包（生成包裹） | `POST .../pack` | order.edit |
| 发货（扣库存） | `POST .../ship` | order.edit |
| 取消（释放预留） | `POST .../cancel` | order.edit |

发货在一个事务内完成：订单 `order_status=shipped` + `ship_time/waybill_no/express_company`；明细 `fulfillment_status=shipped`；包裹 `status=shipped/tracking_number`；`soho_inventories.actual_quantity` 扣减（`version+1`）、`soho_inventory_batches` 按 `COALESCE(expiry_date,production_date)` FIFO 扣 `available_quantity`、写 `soho_inventory_transactions(outbound)` 与 `soho_inventory_flows(out)`；写 `soho_order_status_logs`。

E2E（订单 18 / 仓 1）：21 项断言全 PASS，含库存逐批次数量 `836→834 / 404→400` 与 6 项回滚校验（订单状态、批次、库存主表、事务/流水/日志/包裹计数全部还原）。

### 23.2 P1-2 退款执行闭环（`6f9e822`）

状态机 `pending → processing → completed`，`pending/processing → failed`。

- 台账 `GET soho/refund/records`（`after_sale.refund`）：统计 5 档 + 已退款金额、按状态/类型/关键词筛选、由售后单生成退款单（`refund.request`，自动识别 `return` → `return_refund`）。
- 详情 `GET soho/refund/records/{id}`：售后明细、订单/支付、退款流水、三步进度条。
- 审核 `refund.approve`、驳回/标记失败（必填原因）、执行 `refund.execute`。
- 执行退款写入 `soho_refund_flows(approved)` + 平台退款单号回写 + 售后单 `completed`（`handle_time/handle_result`）+ 售后明细 `refund_status=completed` + 订单明细 `refund_amount` 分摊累计 + `soho_operation_logs` 留痕（`model_type=Refund`）。
- 幂等保护：同一售后单仅可生成一张退款单；同一退款单仅可执行一次（流水存在即拦截）。

E2E：19 项断言 PASS，覆盖生成→重复生成拦截→审核→执行→重复执行拦截→明细/流水/日志回写，测试夹具与副作用全部清理；`soho_refund_records/refund_flows/operation_logs` 与订单明细退款额均还原。

### 23.3 回归

`soho` 全模块 303 条路由：200=299 / 302=4 / 0×500 / 0×404 / 0×403。

### 23.4 下一步候选

1. SOHO 库存调拨 / 盘点审批链（`soho_inventory_documents` + `soho_inventory_adjust_approvals` 目前只读）。
2. ERP 只读模块补写操作（采购/生产/质检审批链）。
3. PM 项目内电话会议去除 Janus 外部依赖（本地 WebRTC 或录音纪要降级）。

## 二十四、P1-3 库存单据执行闭环 + 调整审批（2026-11-19）

### 24.1 目标与范围

SOHO 库存单据（`soho_inventory_documents`）此前只有只读台账，无法提交、审批、执行；库存调整申请（`soho_inventory_adjust_approvals`）只有待审批列表，无审批动作与落账路径。本轮补齐「单据 → 审批 → 执行 → 库存变动」与「调整申请 → 审批 → 库存变动」两条闭环，不改动原表既有列语义。

### 24.2 表结构

- 迁移 `2026_11_19_210000_create_soho_inventory_document_items_table`：
  - `soho_inventory_documents` 新增 `target_warehouse_id`（调拨目标仓，可空）。
  - 新建 `soho_inventory_document_items`（`tenant_id/document_id/sku_id/product_name/quantity/unit_cost/batch_no/remark/timestamps`），作为执行库存变动的明细来源。

### 24.3 代码

- 新增 `App\Soho\Http\Controllers\Warehouse\InventoryDocumentFlowController`：`show / addItem / removeItem / submit / approve / execute / cancel / approveAdjust / rejectAdjust`。
- 新增视图 `soho/warehouse/inventory-doc-flow.blade.php`：四步状态条、明细表、追加明细、操作区、单据信息、本单库存流水。
- `soho/warehouse/inventory-doc.blade.php`：单据表加「详情」列（`colspan` 8→9），调整审批队列加「通过/驳回」列（`colspan` 7→8）。
- `routes/soho.php` warehouse 组新增 9 条路由（`view/create/approve/adjust/adjust.approve` 权限分别挂载）。

状态机：`draft → pending → approved → completed`，非 `completed` 可 `cancelled`。
执行分支：`inbound` 加库存（建/累加批次）、`outbound` 减库存（批次 FIFO）、`transfer` 源仓减目标仓加、`stocktake` 以明细数量为实盘数按差异走 `adjustment`。
写账：`soho_inventories`（`actual_quantity` + `version+1`）、`soho_inventory_batches`、`soho_inventory_transactions`、`soho_inventory_flows`（`flow_no = FLW-{单号}-{仓}-{SKU}`）。

### 24.4 E2E

- `E:\_tmp_iv_e2e.php`：26 项断言 PASS —— 登录、详情 200、追加明细与合计同步、提交/审批（记审批人）、执行入库（主表 427→437、`version+1`、事务 inbound +1、流水 in +1、详情显示「已执行完成」）；调拨执行（源仓 437→432、目标仓 115→120、事务 +2、流水 +2）；取消链（已取消不可执行、已执行无追加表单）；7 项回滚校验全部还原。
- `E:\_tmp_adj_e2e.php`：13 项断言 PASS —— 调整审批通过（库存 427→430、事务 `adjustment` +1、流水 +1）、驳回不落账、重复审批被拦截；4 项回滚校验全部还原。

### 24.5 回归

`soho` 全模块 303 条路由：200=299 / 302=4 / 0×500 / 0×404 / 0×403。
## 二十五、P1-4 ERP 质检闭环（不合格品台账 + 检验项判定）

### 25.1 缺口

`app/quality` 下只有检验单的列表/新建/详情与只读的「缺陷代码 + 最近缺陷记录」（且列表用路由闭包实现），不合格品没有登记入口、没有处置动作，检验项明细表（`erp_quality_inspection_lines`）从未写入。

### 25.2 代码

- 新增 `App\Erp\Http\Controllers\App\Quality\DefectRecordController`：`index / create / store / show / handle` + `nextRecordNo()`（`DR-{年}-{6位序号}`，冲突顺延）。
- `QualityController` 增 `addLine`（检验项明细追加，行号顺延）、`judge`（判定：结果/合格数/不合格数/备注 + `approved_by/approved_at` + 状态 completed）；`store` 与 `judge` 判不合格时自动登记缺陷记录（`disposition=pending`，数量取不合格数）。
- 视图：新增 `app/quality/defects/{create,show}.blade.php`；`defects/index` 重写为「统计卡 + 不合格品记录（按类型/处置/关键字筛选，含详情入口）+ 缺陷代码字典」；`inspections/show` 增「检验项明细（列表 + 追加表单）」与「检验判定」区。
- 路由：`defects` 由闭包改为控制器（index/create/store/show/handle 5 条），新增 `inspections/{inspection}/lines`、`inspections/{inspection}/judge`，共 7 条。

业务规则：存在判「不合格」的检验项时，判定不能选合格/豁免接收；处置为返工/退货/让步接收/报废时必须填纠正措施，否则拦截。

### 25.3 E2E

`E:\_tmp_q_e2e.php` 22 项断言 PASS：缺陷台账/登记页 200、登记生成 `DR-2026-000004`、详情处置表单可用、缺纠正措施被拦截、处置 → rework 且措施落库；检验单详情 200、检验项追加、fail 明细阻挡判合格、判定保存（审批人 + 数量）、不合格自动生成缺陷记录（数量=2、pending）、审计日志写入；3 项回滚校验（缺陷数、明细数、检验单字段）全部还原。

### 25.4 回归

ERP 全模块 159 条路由：200=53 / 302=106 / 0×500 / 0×404 / 0×403（302 为销售角色权限跳转，符合预期）。
## 二十六、P1-5 PM 项目电话会议去 Janus / Swoole 外部依赖

### 26.1 原实现问题

`pm/project/{project}/voice` 走 PM 原链路：前端 `new WebSocket(config('voice.signal_ws'))` → 外部 Swoole 信令服务（`wss://127.0.0.1:3001`）→ Janus AudioBridge（`ws://127.0.0.1:8188` + Admin REST `:7088`）。部署必须额外起 Swoole + Janus 两个进程并开放端口，内网/断外网即不可用；页面还绑 OnlyOffice iframe 做文档演示，同样依赖外部服务。

### 26.2 改造

- `App\Pm\Http\Controllers\VoiceController` 重写：`index / issueToken / signal / poll / end / members / history` 全部改走 `App\Core\Services\VoiceService`（`voice_rooms` + `voice_participants` + `voice_signals`，2 秒轮询，每信令只投递一次）。房间命名统一 `project-{id}`，`history` 按房间归集；`hookEnter/hookLeave` 保留为兼容入口。
- 路由新增 `pm/project/{project}/voice/signal|poll|end` 3 条（`pm.project.voice.signal|poll|end`）。
- 视图 `pm/project/voice.blade.php` 重写：保留项目 Tab 与原左成员栏 + 右侧会议舞台布局；去掉 WebSocket/OnlyOffice iframe，改为「加入/静音/离开 + 在会成员磁贴 + 会议日志 + 本项目会议历史 + 会议资料（下载 + 上传）」；全部 JS 走站内接口。
- `config/voice.php` 精简：删除 `signal_ws / janus_ws / janus_admin / janus_admin_secret / signal_listen / signal_port / pm_internal_url`，仅留 `stun/turn/token_ttl`。
- 删除死代码 `App\Pm\Http\Controllers\ProjectVoiceController`（无路由引用，且调用了不存在的 `Project::isMember()`，属潜在 500）。
- `pm/project/partials/project-tabs.blade.php`：非项目参与者隐藏「电话会议」Tab（后端仍 404 兜底），符合「无权限隐藏入口」。

### 26.3 E2E

`E:\_tmp_v_e2e.php` 20 项断言 PASS：会议页 200、页面无 `new WebSocket` / 无 `signal_ws` / 无 janus 关键字、含加入会议/轮询/历史/资料区、Core 房间已创建、token 含 HMAC 签名且无 janus 字段、members/poll/history 结构正确、信令落库、poll 返回 open、结束会议 → 房间 closed 且未消费信令清零、再入页面自动重开房；3 项回滚校验（信令数、房间状态、房间数）全部还原。

### 26.4 回归

PM 全模块 73 条静态 GET 路由：200=49 / 302=3 / 404=21 / **0×500**（404 均为 `pm/admin/*`、`pm/ai/settings` 等管理员专属页对 `pm.project_creator` 的权限兜底，非本轮引入）。
## 二十七、P1-6 库存单据创建 / 盘点批量实盘录入 / 调整申请提交

### 27.1 补齐的缺口

P1-3 只做了「已有单据」的明细、提交、审批、执行，**没有创建入口**：`soho_inventory_documents` 仅由种子数据提供 12 行，盘点/入库/出库/调拨单据无法在界面产生；`soho_inventory_adjust_approvals` 只有 approve/reject，无提交入口（表长期 0 行）；盘点只能逐行追加明细，无法按 SKU 列表批量录入实盘数。

### 27.2 新增能力

- **新建单据**：`GET warehouse/inventory-doc/create`（`inventory.document.create`）+ `POST warehouse/inventory-doc`。类型 inbound/outbound/transfer/stocktake；调拨强制目标仓且不得与来源仓相同；单号沿用原格式 `INV-{INB|OUT|TRA|STO}-{YYYYMM}-{3 位顺延}`。
- **盘点批量实盘录入**：`GET/POST warehouse/inventory-doc/{id}/count-sheet`。列全部启用 SKU（左连本仓库存显示账面数/锁定量），只填写实际盘到的数量，**留空 = 本次不盘点**；保存时覆盖本单明细（非累加）并同步合计；前端实时算差异、支持「全部按账面数填充 / 清空」。
- **调整申请提交**：`GET warehouse/adjust/create` + `POST warehouse/adjust`（`inventory.adjust`）。写 `status=pending`、`submitted_by/submitted_at`、`product_name`、单号 `ADJ-{YYYYMMDD}-{3 位}`；负数申请先校验可用库存，不足直接拦回。

### 27.3 同时修正的两个真问题

1. **盘点差异口径错误**：原 `execute` 用「可用量（actual − locked）」与实盘数比较，锁定量会被算成盘盈（本次 E2E 实测 427 → 441，多补 14 件）。改为与实际库存比较（新增 `actualQuantity()`），实测 427 → 428。
2. **产权限判定只认裸名**：`CheckPermission` 原用 `$user->can($permission)`，而 SOHO 角色同时存在 `inventory.view` 与 `soho.inventory.view` 两套命名，且 `Gate::check(array)` 是「全部通过」语义。新增 Core helper `permission_candidates()` / `can_perm()`：逐候选判定、任一命中即放行（super_admin 直通），`CheckPermission` 同步改造。视图入口（新建单据 / 提交调整申请 / 批量录入 / 追加明细 / 提交/审批/执行/取消 / 通过/驳回）全部改为 `can_perm()` 门控——无权限即不渲染，符合「无 403、隐藏入口」。

### 27.4 验证

- `E:\_tmp_s_e2e.php` 34 项断言 PASS：新建入库单（单号格式 + 跳详情）、新建盘点单、实盘页 200、批量录入生成 2 行、空值 SKU 不生成、合计同步、重复保存覆盖、提交→审批→执行、盘点差异落账（427→428）、生成流水；调整申请提交（单号/提交人/商品名）、审批落账（428→426）、写事务+流水、重复审批不重复落账、驳回不落账、超可用量提交被拦截；6 项回滚（单据/明细/申请/流水/事务/库存实际数）全部还原。
- 角色验收：`warehouse_mgr@ddwl-soho.com`（`warehouse_manager`）访问列表页 200、`新建单据`+`提交调整申请` 均可见、无「无权」提示；`tenant_admin`（无 warehouse 角色）被角色中间件收回工作台，页面内也不再渲染这些入口。
- 三模块回归：SOHO 305 路由 200=301/302=4、ERP 159 路由 200=53/302=106、PM 73 路由 200=49/302=3/404=21（管理员页权限兜底），全部 **0×500 / 0×403**。
## 二十八、基线重跑与收尾决策（D22 / D23）

- **路由基线**：`route:list --json` 重生成（1393 条路由）→ core 17 / erp 158 / pm 73 / soho 299 = **547 条 GET 无参页面**。
- **状态基线**：分模块账号真实探测（约 6 分钟）→ core 200=15/302=2、erp 200=156/302=2、pm 200=68/302=5、soho 200=286/302=13；**404=0 / 500=0 / 403=0**，缺口 0。
- **文档同步**：`功能基线清单.md` 重生成（19,028 B）；`docs/baseline/README.md` 新增 §7「合并站基线（当前唯一验收口径）」并声明 §1~§6 仅作 T0-12 历史证据；`P0-决策与基线.md` §十二数字同步为 547/525/22。
- **D22**：`soho.super_admin` 不提升为租户内全域管理，仍按权限点判定（租户管理员看不到仓库菜单属预期，不出 403、只隐藏入口）。
- **D23**：新增页面的增量截图（PM 电话会议、SOHO 新建单据 / 盘点实盘录入 / 调整申请提交）**待部署后一次性补拍**，本期以状态基线为准。
- **提交**：`391eb2c`（docs/baseline 重跑 + README 口径）。
## 二十九、P2-1 权限收口（角色 × 页面 × 写操作）

### 29.1 查出的三类真实缺陷

1. **角色白名单比权限模型更窄**：路由上 `soho_role:warehouse_manager` 之类白名单与权限点重复裁决，导致「权限已授予、角色不在白名单」时被 302（picker 进不去库存、finance_manager 进不去审计报表、procurement_manager 进不去审批页、auditor 进不去日志页等 8 处）。
2. **两套角色命名并存但只认裸名**：库中同时存在 `warehouse_manager` 与 `soho.warehouse_manager`（权限点也分裸名/前缀名两套），中间件只比对裸名，前缀角色永远进不去。
3. **写接口只受 `*.view` 守卫**：228 条 SOHO 写路由里 158 条沿用了「整组 view 权限」，只读账号 `read_only` 可直接打开新建表单并 POST 提交（实测 `/soho/product/create` 200、`/soho/brand` POST 成功）。35 条写路由完全无权限守卫。
4. **8 个权限点无任何角色持有**（历史上只建未授）：`pick.task.view`、`pack.task.view`、`check.task.view`、`shipment.view`、`shipment.push`、`procurement.approval`、`dispute.handle`、`replacement.create`（另 `tax_rate.view` 仅超管），对应页面只有超管能进。

### 29.2 修法

- `SohoRoleMiddleware`：角色名同时接受裸名与 `soho.*` 前缀名；若目标路由已挂权限点校验，则以权限点为唯一裁决，角色白名单不再二次拦截（保留「只有角色门、没有权限门」路由的原行为）。
- 新增 `App\Soho\Http\Middleware\EnsureWritePermission`（别名 `write.guard`，挂在 SOHO 路由组）：按「控制器动作方法名 + 路由域」反推操作级权限（`store→create`、`update→edit`、`destroy→delete`、`approve/reject→approve`、`execute/push/import/export/reset` 对应后缀），命中库中真实存在的 `{域}.{操作}` 或 `soho.{域}.{操作}` 即要求持有；权限点不存在则放行（不臆造权限）。GET 仅对 `create`/`edit` 表单页生效，避免把只读角色放进表单。无权限仍 302 回工作台，不出 403。
- 迁移 `2026_11_19_230000_grant_orphan_permissions_to_roles`：按岗位语义补授 9 个权限点，裸名与前缀名角色同步，幂等可回滚；补授后**孤立权限点归零**。

### 29.3 验收

- 逐角色（15 个岗位真实账号）域内页面全部 200：picker（拣货/复核/库存）、packer（打包/包裹/快递）、checker（复核/扫码/流水）、logistics（运费单/时效/快递）、finance_manager（审计报表/对账/币种）、aftersale_manager（售后/纠纷/换货）、procurement_manager（批次/调价审批/审批实例）、product_admin（商品/品牌）、order_manager（订单/履约/快递）、security_admin（审计日志/登录日志）、shop_auth_admin（平台账号/店铺连通/回调日志）、marketing_staff（优惠券/活动规则/运营看板）、integration_ops（接口健康/异常/事件）、auditor（审计/日志/事件）、read_only（库存/订单/售后）。
- 越权回归：`read_only` 打开 `/soho/product/create`、`/soho/brand/create` 均 302；`POST /soho/brand` 302（此前 200/写入成功）。
- 写权限不影响正常角色：`product_admin` 新建页 200；`warehouse_manager` 库存单据与新建单据 200。
- 全量回归：SOHO 305（200=301/302=4）、ERP 159（200=53/302=106）、PM 73（200=49/302=3/404=21，管理员页权限兜底），**0×500 / 0×403**；`_tmp_s_e2e.php` 34 项断言仍 PASS。
- 权限矩阵落盘：`docs/权限矩阵-SOHO.md`（26 个角色 × 380 个 SOHO 页面 × 权限点覆盖，孤立权限点 0）。
## 三十、P2-2 审批与通知统一

### 30.1 通知：SOHO 改读 Core 单一数据源

- 现状：`notifications`（Core，47 行）与 `soho_notifications`（0 行）两套并存，SOHO 页读自己的表，看不到站内全局通知。
- 改法（只改 SOHO 控制器，视图与 URL 不动）：`app\Soho\Http\Controllers\NotificationController.php` 整文件改为读写 `App\Core\Models\Notification`，`baseQuery()` 统一 `user_id` + `module in (soho, core)`；`index/unreadCount/markAsRead/markAllAsRead/deleteRead/batchAction/destroy` 全部按 Core 字段（`is_read`/`read_at`）；JSON 契约（`{success,message}` / `{count}`）保持原样。`soho_notifications` 表保留但已不再是数据源。
- 验收：`admin@soho.demo`/`123456`（core `notifications` 2 条、未读 1）→ `GET /soho/notifications` 200，页面显示「共 2 条数据，未读 1」；`GET /soho/notifications/unread-count` 返回 `{"count":1}`。`admin@ddwl-soho.com`（0 条）200，显示 0 条。

### 30.2 我的待办：Core 跨模块只读聚合

- 新增 `app\Core\Services\TodoCenterService.php`：按模块分组聚合四源（`core` 审批任务/我发起、`erp` 审批任务、`pm` 我执行/我审核任务、`soho` 审批实例 + 6 张域内审批队列表），每条约 50 条上限，单条统一 `{module,category,title,meta,url,at}`。
- 新增 `app\Core\Http\Controllers\TodoController.php` + 视图 `resources\views\core\todos\index.blade.php`（沿用 `dw-page/dw-panel/dw-chip/dw-table/dw-empty`），路由 `GET /todos -> core.todos.index`（`routes\core.php` 的 `auth` 组），Core 侧栏「协作」组新增「我的待办」。
- **入口收敛（关键）**：模块分组按 D15 准入链（`users.module → module_access → department.module`，超管全放行）决定是否出现——用户进不去的模块连分组都不出现；每条「前往」链接再按目标路由上挂的权限中间件（`permission:` / `CheckPermission:`）判定一次可达性，不可达的队列整队不出。因此不会出现「看得到点不进去」的 302/403。
- 回归中修掉的两处偏差：初版把 SOHO 8 条审批队列无差别展示给所有登录用户（ERP 管理员、PM 管理员也能看到），已按上述两层收敛；采购审批队列对无 `procurement.approval` 的账号整队隐藏。

### 30.3 验收

- `admin@soho.demo`：`/todos` 200，「全部 9 / SOHO 8」，条目链接 `/approvals/6`、`/soho/approval-instances`、`/soho/approval/finance-diff`、`/soho/approval/refund` 逐个实测 200；`?module=soho`、`?module=core` 均 200。
- `procurement_mgr@ddwl-soho.com`：待办含 `/soho/procurement/approvals`（有权限），实测该页 200。
- `viewer@ddwl-soho.com`（read_only）：`/todos` 200 且为空态，无任何越权链接，**0×403**。
- `admin@erp.demo`（module=erp）、`admin@admin.com`（module 空、pm.admin）：`/todos` 200 空态，仅出现各自可进入的模块分组。
- 侧栏：`/todos` 页导航含「我的待办」（`/todos`）、审批、文件、聊天、语音会议、通知、个人中心。
- 三模块全量回归与 P2-1 一致、无新增缺口：SOHO 305（200=301/302=4）、ERP 159（200=53/302=106）、PM 73（200=49/302=3/404=21），**0×500 / 0×403**。
- **提交**：`8985926`。

## 三十一、P2-3 日志归一（soho_operation_logs → Core operation_logs）

### 31.1 现状

- Core `operation_logs`（144 行，全部 `module='soho'`，字段 `module/user_id/user_name/action/target_type/target_id/old_values/new_values/payload/description/ip_address/user_agent/tenant_id`）。
- SOHO `soho_operation_logs`（532 行，字段 `tenant_id/user_id/action/model_type/model_id/description/changes/ip_address/user_agent`），SOHO 审计/日志页与模型都读它，日志被切成两套。

### 31.2 改法（不动页面与读 SQL）

1. **写入口唯一**：`App\Soho\Models\OperationLog` 物理表改为 `operation_logs`，固定 `module='soho'`（全局作用域 + `creating` 钩子自动补 `module` 与 `user_name`），并对外保留旧字段名映射——`model_type→target_type`、`model_id→target_id`、`changes→payload`（`setAttribute/getAttribute` 双向映射，`changes` 读取时优先 `payload`，否则由 `old_values/new_values` 还原）。因此 `LogsActivity` trait、5 个监听器、`LogUserActivity` 中间件等全部调用点**零改动**即写入 Core 表。
2. **两处裸 SQL 插入**改为 `DB::table('operation_logs')` 并补 `module='soho'`、`target_type/target_id`：`AfterSaleBoardController::log()`、`RefundFlowController::execute()` 留痕。
3. **历史数据迁移**：`database\migrations\2026_11_19_240000_unify_soho_operation_logs.php` 把 532 行按字段映射导入 `operation_logs`，按 `(module,user_id,action,target_id,created_at,description)` 去重（幂等）；导入 381 行，跳过 151 行（其中 144 行为 Core 已有同签名行、7 行为旧表内重复行）。迁移后 `operation_logs` 中 `module='soho'` 共 525 行（= 去重后的唯一行数）。
4. **旧表改为只读视图**：`soho_operation_logs` 由物理表改为 `SELECT … target_type AS model_type, target_id AS model_id, payload AS changes … WHERE module='soho'` 的视图，因此 7 处历史读 SQL（售后看板 3 处、审计报表 2 处、退款/审计页）**无需改写**即可看到新日志；回滚脚本可还原为物理表并回灌数据。

### 31.3 验收

- 写入口自检：`OperationLog::create(model_type/model_id/changes)` → `operation_logs` 525→526，落库 `module=soho / target_type=App\Soho\Models\Product / target_id=999 / payload={"a":1} / user_name=admin`；同一行在视图 `soho_operation_logs` 可见；模型读回 `model_type/model_id/changes` 与写入一致；自检行已清理。
- 页面：`/soho/audit/reports` 200、`/soho/aftersale/operation-log` 200（数据来自视图/新表）。
- 全量回归与 P2-1/P2-2 一致：SOHO 305（200=301/302=4）、ERP 159（200=53/302=106）、PM 73（200=49/302=3/404=21），**0×500 / 0×403**。

## 三十二、P2-4 遗留清理（ERP 审批流归一 + PM 语音死代码下线）

### 32.1 ERP 审批流配置归一到 Core

- 现状：`erp_approval_flows`(2) / `erp_approval_flow_steps`(4) 是 ERP 管理页写的那一份，而 Core `approval_flows`(10，其中 `module='erp'` 的两条就是同一批数据的早期导入) 才是审批引擎实际读取的那一份——两套并存，ERP 管理页改了 Core 引擎不生效。
- 改法（模型层对齐，控制器与视图零改动）：
  - `App\Erp\Models\System\ApprovalFlow` → 表 `approval_flows`，全局作用域 `module='erp'`，`creating` 自动补 `module`/`created_by`。
  - `App\Erp\Models\System\ApprovalFlowStep` → 表 `approval_steps`，外键改回 `approval_flow_id`。
  - `Admin\ApprovalFlowController`（列表/新建）与视图不动，写入选中的就是 Core 表。
- 数据核对：Core `approval_steps` 已有 flow#1/#2 各 2 步，内容与 ERP 旧表一致（差异仅在角色名已规范为 `erp.sales_manager`/`erp.purchase_manager`/`erp.admin` 前缀形式，属早期归一的有意结果，保留 Core 版本）。旧表保留为历史副本，不再写入。
- 验收：`admin@erp.demo` → `GET /erp/admin/approval-flows` 200，页面含「销售订单审批流」与步骤「销售经理审批」；`/erp/app/approvals/my-tasks` 200。

### 32.2 PM 语音死代码下线

- 删除 `app\Pm\Models\VoiceRoom.php`、`VoiceSession.php`、`VoiceParticipant.php`、`app\Pm\Services\VoiceTokenService.php`（全仓 grep 无任何引用，P1-5 已把项目内会议改走 `Core\Services\VoiceService`）。
- 验收：`_tmp_v_e2e.php` 20 项断言仍全 PASS（入会 / 轮询信令 / 成员 / 结束会议 / 再入自动重开 / 数据回滚）。

### 32.3 回归

- SOHO 305（200=301/302=4）、ERP 159（200=53/302=106）、PM 73（200=49/302=3/404=21），**0×500 / 0×403**，与 P2-1/P2-2/P2-3 基线一致。

## 三十三、P3 数据迁移与切换（V1~V8 校验 + PM 组织补齐）

### 33.1 发现

- V1 源比对发现真实缺口：`pm_db` 58 个用户，合并站仅 9 个（8 个 `module=pm` + admin 复用）；`pm_db.departments` 13 个，合并站仅 5 个 PM 部门，缺 软件设计部/样品制作部/品质验证部/包装设计部/物料管理部/专利管理部/认证管理部 共 7 个。ERP 源 12 用户为 0 缺失。
- SOHO 源不可比：`E:\ddwl-soho` 的 `DB_CONNECTION=sqlite`，但 `database/` 下无任何 sqlite/.db 文件；全盘候选 sqlite（`E:\ddwl-shop`、`E:\soho\mysoho`、`pm-server-openEuler/database/database.sqlite`、`ddwl-zm/ecmp`）与合并站 SOHO 账号不匹配，`ddwl-zm`(23 users, `@ecmp.local`) 为另一工程。按 D21 以合并站自身 + 基线文档为准。

### 33.2 修复

- 新增 `app/Console/Commands/P3MigratePmOrg.php`（`php artisan p3:migrate-pm-org [--apply]`，幂等）：部门按名称去重补建（`module=pm`、`code=PM-<源code大写>`），用户按 `username` 去重补建（`module=pm`、部门按名称映射、密码/头像/bio/企微字段原样继承、`status=is_active`），角色按源 `role_id` → `pm.admin/pm.project_creator/pm.project_manager/pm.task_executor/pm.task_auditor/pm.support` 写入 `model_has_roles`。
- 写库前备份：`storage/backups/p3-org-before.sql`（users/departments/roles/permissions/model_has_roles/role_has_permissions/user_module_access，148 KB）。
- 执行：dry-run 评审（部门 7 新建 / 用户 49 新建）→ `--apply`。

### 33.3 复验

- 数据：`users` 74→123、`departments` 20→27、`model_has_roles` 132→181；PM 源用户缺失 0、ERP 源用户缺失 0。
- V2：username/email/phone 重复 0；V3：users→departments、model_has_roles→users/roles、tasks/projects/departments 指向用户 全部 0 孤儿；V5：123 账号全 `$2y$`（bcrypt）。
- V4：随机 20 个启用账号统一登录 20/20 成功，`username` 通道（`admin`）302→/home；新迁 5 个 PM 账号（pm_manager_2/product_clerk_1/software_manager_1/packaging_designer_2/certification_clerk_2）登录 5/5 落地 `/pm` 200。
- V6：裸名 127（ERP 源原文）与 `erp.*` 127 双命名并存，均被真实授权引用（454/127 与 581/127），`permission_candidates()` 任一命中放行 → 减项 0；`soho.*` 209、`pm.*` 45、`core.*` 8。
- V7：`resolveModules()` 逐人解析 erp 13 / pm 58 / soho 52 / core 123；空 `module` 仅 #3（部门=运营部/soho）、#13（`user_module_access`=pm）；仅 core 的只有禁用账号 `disabled_user`。
- V8：`notifications` 47（erp 41 / pm 2 / soho 3 / 空 1 私信）；`chat_messages` 无 `room_key`，口径改为 `(module, room_type, room_id)`：erp/group 5、erp/private 3、pm/project 3、soho/group 1；`chat_rooms` 表不存在（房间由 type+id 表达）。
- 关键页巡检（erp/soho/pm 三个身份各 10 页：`/home`、`/erp/admin/dashboard|users`、`/soho/dashboard|admin/users`、`/pm`、`/pm/admin/users`、`/todos`、`/chat`、`/notifications`）：全部 200，0×500，0×403。
- 命令幂等复跑：部门 0 新建、用户 0 新建。

### 33.4 报告与遗留

- 报告落盘 `docs/数据校验报告.md`（V1~V8 结论、口径、限制、改动、复跑方式）。
- 遗留：`soho_platform_skus` 的 `(shop_id, platform_sku)` 3 组各 12 行重复，逐行核对 12 行的 `system_goods_id/platform_goods_id/价格/库存` 全不同，属演示种子把同一平台 SKU 复用到 12 个商品，非合并错误，是否改唯一待拍板；PM 49 个新迁用户源无 email，当前留空（如需邮箱登录按 `<username>@pm.local` 补造）。

### 33.5 收口（用户拍板项）

- 用户决定：① 平台 SKU 重复要改唯一；② 全员邮箱或用户名均可登录。
- 邮箱：新增 `p3:fix-uniqueness`（幂等，含 --apply），把 50 个无邮箱账号（均为 PM）按 `<username>@pm.local` 补造（冲突自动 `-N`）。现 `users.email` 缺失 0、重复 0，`@pm.local` 共 50 个。
- 平台 SKU：同命令按 `(shop_id, platform_sku)` 分组，保留首行、其余加 `-02…-12` 后缀，改写 44 行；新增迁移 `2026_11_19_250000_unique_soho_platform_sku.php` 建唯一索引 `ux_platform_sku_shop(shop_id, platform_sku)`。现重复组 0，索引已生效。
- 备份：`storage/backups/p3-uniqueness-before.sql`（users + soho_platform_skus，50 KB）。
- 验收：邮箱通道 `pm_manager_2@pm.local` / `pm_director@pm.local` 与用户名通道 `pm_manager_2` 登录均 200 落地 `/pm`；`/soho/platform-sku`、`/soho/goods`、`/soho/dashboard` 200；PM 侧 `/pm`、`/pm/dashboard` 200，0×500 / 0×403。

### 33.6 P3 收尾（校验固化为命令 + ID 对照 + 切换手册）

- 新增 `app/Console/Commands/P3Verify.php`（`p3:verify`）：一次跑完 V1~V8 并输出表格与 `storage/app/p3-verify-*.json` 快照；`--base` 走真实 HTTP 登录抽样（本次 10/10 成功）；`--export-map` 导出 `docs/数据映射表-源ID对照.csv`。实测 V1~V8 全 ✓。
- ID 对照表 139 行：user 70 全映射；department 21 同名映射 + `项目部`→`项目管理部`（D16）+ `电商部` 不保留（D16 人员已删）；role 18 全映射；业务主数据 26 映射、2 条 ERP 演示项目未迁（`projects` 表缺 `description/budget/customer_id`，登记为遗留）。
- 新增 `docs/P3-切换与回滚.md`：环境基线、首次部署、数据补齐（幂等命令）、切换 9 步、验收 11 项、代码/数据回滚、切换前必读遗留（含 ERP 演示项目、projects/tasks 无 module 区分、SOHO 源缺失、D23 截图延后）。
- 结论：P3 出口达成；剩余为部署后动作（真机验证、截图基线）。
