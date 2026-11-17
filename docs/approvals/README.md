# TC-11 审批引擎

ERP 的「订单审批」与 SOHO 的 8 条 FLOW-*，原来各写一套（ERP 用步骤表 + 待办表，SOHO 把节点塞进 `flow_config` JSON 且只能一次性终审）。合并后是**一张流表、一张步骤表、一套引擎、一个审批中心**。

## 1. 四层结构

| Core 表 | 列数 | 职责 |
|---|---|---|
| `approval_flows` | 9 | 流定义：`module` + `document_type` + `name` + `enabled` |
| `approval_steps` | 8 | 步骤：`step_order` + `name` + `approver_type` + `approver_value` |
| `approval_instances` | 14 | 一次报批：`document_type`+`document_id`+`title`+`applicant_id`+`current_step`+`status`+`payload` |
| `approval_tasks` | 13 | 待办：一步一条/人，`approver_id` + `status`（pending/approved/rejected/skipped/cancelled）+ `comment` |

`approver_type` 支持四种：`role`（带模块前缀的角色名，如 `erp.sales_manager`、`soho.operation_manager`）、`user`（指定人）、`department`（部门全员）、`applicant`（回给发起人）。SOHO 的 `flow_config.nodes[]` 与 ERP 的步骤行都摊平成 `approval_steps`：节点 `node` → `step_order`，节点 `name` → 步骤名，`approvers[0]` 补 `soho.` 前缀 → `approver_value`；原 `mode=any`（或签）正好等同引擎的「同一步任一人同意即推进」。

## 2. 引擎规则（`Core\Services\ApprovalService`）

- `start(flow, applicant, documentType, documentId, title, payload)`：校验流启用、步骤非空、同单据无进行中实例；建实例 + 第一步待办（角色命中多人 → 每人一条）。
- `approve(task, user, comment)`：校验任务属于该人且仍 pending → 标 approved → 同步骤其余 pending 置 `skipped` → 有下一步则 `current_step+1` 并开新待办，否则整单 `approved` 且写 `finished_at`。
- `reject`：任务标 rejected，其余 pending 置 cancelled，整单 `rejected`。
- `cancel(instance, user)`：仅发起人（或管理员）可撤，已结束不可撤。
- `pendingFor(user, module)` / `submittedBy(user, module)` / `forDocument(module, type, id)`。

## 3. 迁移（`database/scripts/approval_migrate.php`）

```
dry-run：php database/scripts/approval_migrate.php
写入  ：php database/scripts/approval_migrate.php --apply
```

结果：**ERP 2 条流 / 4 步 + SOHO 8 条流 / 16 步 = 10 条流 / 20 步**，全部入库。

| 源 | 文档类型 | 步骤（迁移后） |
|---|---|---|
| ERP #1 销售订单审批流 | `sales_order` | 1.销售经理审批 `erp.sales_manager` → 2.总经理审批 `erp.admin` |
| ERP #2 采购订单审批流 | `purchase_order` | 1.采购经理审批 `erp.purchase_manager` → 2.财务审批 `erp.admin` |
| SOHO FLOW-PRODUCT-PUBLISH | `product_publish` | 1.初审 `soho.operation_manager` → 2.复审 `soho.super_admin` |
| …（价格变更/订单取消/库存调整/退款/账单差异/数据导出/数据删除同构） | `price_change` / `order_cancel` / `stock_adjust` / `refund` / `bill_diff` / `data_export` / `data_delete` | 同上两节点 |

- SOHO 的 `FLOW-DATA-DELETE` 源库 `status=disabled`，迁入后 `enabled=false`（启用前不允许发起，脚本断言覆盖）。
- SOHO 15 条历史实例**未迁**：`submitted_by`/`biz_id` 是源库小整数，需 B-05 用户映射后才可信；报告里记为待办。
- 报告：`docs/approvals/migration-report.json`。

## 4. 界面

| 位置 | 内容 |
|---|---|
| `GET /approvals` | 待我审批 / 我发起的 两个页签 + 本模块最近完成的审批；按 `active_module` 收窄 |
| `GET /approvals/create` | 选本模块启用的流（选项里带步骤链）、单据号、标题、说明 |
| `GET /approvals/{id}` | 进度时间线（每步状态 + 每人意见与时间）+ 待我处理表单（同意/驳回）+ 单据摘要 |
| 顶栏 | 新增「审批」入口 |

越权规则：实例必须属于当前 `active_module`（管理员除外），且当前账号是发起人或某一步审批人；否则 403。

## 5. 验收

`php artisan soho:test-approval-flow`（命令名沿用 SOHO 原名，实现换成引擎；`--module=erp|soho` 可只跑一侧）：

```
erp · 销售订单审批流：发起 → 第1步 销售经理(5) 同意 → 第2步 总经理(1) 同意 → approved ✔
                      驳回分支 ✔  撤销分支 ✔
soho · 商品发布审批流：发起 → 第1步 初审(5) 同意 → 第2步 复审(3) 同意 → approved ✔
                      驳回分支 ✔  撤销分支 ✔
全部通过
```

`php database/scripts/approval_verify.php`：**23 项全通过**，覆盖流定义（10 条/20 步/角色前缀/停用流）、顺序推进（步 1→2、无残留 pending、末步结束写时间）、同单据重复发起被拦、驳回清零、撤销与二次撤销被拒、越权处理被拒、同一步多人任一人同意、`forDocument`。

界面取证 `docs/screenshots-tc11/`（967×883）：

| 图 | 内容 |
|---|---|
| `01-erp-todo.png` | ERP 管理员视角：待我审批 1 条（采购订单付款，第 1 步采购经理），下方已完成单 |
| `02-erp-detail.png` | 详情页：第 1 步已通过（含意见与时间）、第 2 步进行中、表单轮到我处理 |
| `03-erp-approved.png` | 两步均同意后整单「已通过」，两步意见与时间齐全 |
| `04-soho-mine.png` | SOHO 电商管理员「我发起的」：商品发布审核 第 1/2 步 · 审批中 |
| `05-approval-create.png` | 发起审批页：SOHO 7 条启用流（数据删除流停用未列出），选项内显示步骤链 |

**界面实测链路**（不只是脚本）：admin 登录 → 审批中心看到采购订单待办 → 详情页填意见点「同意」→ 第 1 步转已通过、第 2 步出现表单 → 再点「同意」→ 整单已通过。全程无刷新脚本介入。

## 6. 已知边界与遗留

- 本项目期内实现的是**线性多级 + 同一步或签**（两源实际形态）；SOHO `flow_config` 里可能出现的会签(`all`)/并行/金额阈值/超时升级**未实现**，迁移脚本遇到非 `any` 会跳过并写进报告（现网 8 条全是 `any`，无一条被跳过）。
- 业务单据（`document_id`）目前由发起页手填：ERP/SOHO 业务模块尚未迁入，迁入后改由业务侧调 `ApprovalService::start()` 带入，`forDocument()` 供业务判断单据是否被锁。
- `department` 步骤因 Core `departments` 无 leader 字段，走部门全员（任一人同意即推进）。
- SOHO 10 个业务审批控制器（退款/价格变更/库存调整/订单取消/商品发布/采购/账单差异…）在模块迁入时改为调用本引擎，各自视图保留。
- SOHO 15 条历史实例 + ERP 0 条历史待办的搬迁依赖 B-05 用户映射。
