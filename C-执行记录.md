
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