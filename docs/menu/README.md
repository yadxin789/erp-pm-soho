# 统一菜单基线（TC-08）

## 一、数据从哪来（全部真实抽取，无手工抄写）

| 模块 | 源文件 | 原始形态 | 抽取结果 |
|---|---|---|---|
| ERP | `D:\laravel12-erp\resources\views\partials\sidebar_main.blade.php`（256 行） | 手工 Blade 侧边栏：11 个分组标题 + 每项 `@if(\App\Support\Menu::can('app.x'))` 包裹 | **46 项 / 11 组** |
| PM | `E:\my-pm\pm-server-openEuler\resources\views\layouts\app.blade.php`（778 行） | 按角色拆成 5 个 `@if(isAdmin/isProjectManager/...)` 区块，同一入口在不同区块重复出现 | **69 条链接 → 去重 31 项** |
| SOHO | `E:\ddwl-soho\app\Support\MenuConfig.php`（2430 行 / 98.2 KB） | `getMenus()` 返回 232 条**扁平**数组（无 children），每项 `title/icon/route/permission/roles/order` | **232 项** |

合计 **309 项**。

## 二、归一化规则

- **路由名**：`erp.` / `pm.` / `soho.` + 原路由名。ERP 原 `app.sales.orders.index` → `erp.sales.orders.index`（去掉 ERP 自有的 `app.` 命名空间再套模块前缀，避免 `erp.app.*` 双前缀）。
- **统一字段**：`module / group / label / icon / route / permission / roles / sort / children / source`，落在 `resources/menu/{module}.php`。
- **图标**：PM 原 `fa fa-home me-2` 归一成 FA6 `fa-solid fa-home`；ERP 原 `fa-solid fa-gauge-high` 原样保留。
- **分组**：ERP 分组名来自源文件真实分组标题；PM 源文件没有分组（只有角色区块），按语义归入 工作台/智能助手/系统管理/项目/任务/文件与知识/通知/个人；SOHO 源文件只有 `order` 序号，按序号带归并为 13 组。**PM、SOHO 的分组是本次新增的呈现层，条目本身一条未少。**

## 三、可见性闸门（按原系统各自的机制，不强行统一成权限点）

- **ERP**：源系统侧边栏用 `Menu::can()` → 路由中间件 + `RoleScope::allows()`，**没有 permission 中间件**。因此 ERP 条目存 `roles`（由 `RoleScope::SCOPES/SHARED/PM_MODULES` 对 12 个角色逐个求值得到）。
  - 直接后果：`erp.admin` 在 ERP 业务菜单里只看到 1 项（个人资料）——与源系统一致（管理员只进 `admin.*` 后台，不进业务页）。
  - `projects/tasks/files/...` 等协作条目原系统还额外校验「是否参与项目」，该逻辑标 `note` 字段，随 ERP 迁入（B-02）接入。
- **PM**：用 `pm.*` 权限点（TC-07 新建的 45 个），菜单级映射表写在生成脚本里。
- **SOHO**：原 `MenuConfig` 是**双闸门**（`permission` + `roles` 白名单），两者都保留 → `soho.*` 权限 + `roles` 前缀化后的角色名。

## 四、真实发现（值得记下来的三件事）

1. **SOHO 有 19 条菜单引用了源权限表里根本不存在的权限名**（`pick.task.view`、`check.task.view`、`pack.task.view`、`shipment.view`、`procurement.approval`）。说明 `MenuConfig` 的编写早于 RBAC 权限表成型。处理方式：按语义就近映射到真实权限（拣货→`picking.*`、复核→`checking.*`、打包→`packing.*`、待发货→`logistics.view`、采购审批→`procurement.order.approve`），19 条映射逐条写进 `docs/menu/build-report.json` 的 `permission_aliases`，**不静默丢闸门**。
2. **PM 文档里“2 个角色”是错的**，源 `roles` 表实际 6 个（TC-07 已校正），菜单侧同样按 6 角色重算。
3. **ERP 段第一次抽取返回 0 项**：`menu_extract.php` 里的大正则因反斜杠转义在 PHP 单引号串与 PCRE 之间对不上，整段没命中。改行式扫描（按 `Menu::can` → 下一行 `<a>` → `<i>`+标签）后稳定得到 46 项。这是“静态正则一次没过就以为基线为空”的典型陷阱。

## 五、产物

- `docs/menu/baseline-menu.json` — 三源原始抽取（保留原路由名/原权限名/角色名单，便于回查）
- `docs/menu/build-report.json` — 条数、未解析权限（本轮 0）、19 条权限别名明细
- `resources/menu/{erp,pm,soho}.php` — 运行期数据（`MenuRepository` 直接 require）
- `app/Core/Menu/MenuRepository.php` — `all()/visible()/canSee()/grouped()/count()/routeExists()`
- `resources/views/core/partials/menu.blade.php` — 侧边栏（路由未注册时渲染为不可点，不抛 500）
- `app/Core/Http/Controllers/MenuPreviewController.php` + `resources/views/core/menu/preview.blade.php` → `GET /core/menu/{module?}`
- `database/scripts/{menu_extract,menu_build,menu_verify}.php`

## 六、验收结果（`php database/scripts/menu_verify.php`）

```
条数：erp 46/46 OK、pm 31/31 OK、soho 232/232 OK，合计 309/309
前缀违规 0；权限名违规 0（全部真实存在且带模块前缀）
权限过滤（真实账号实测）：
  admin            erp  1/46   （源系统本就如此：管理员不进业务菜单）
  pm_director      pm   20/31
  admin@soho.demo  soho 10/232 （tenant_admin 只管系统管理类，业务菜单本就不该给）
  multi_module     erp  21/46
路由可解析：erp 0/46、pm 0/31、soho 0/232 已注册 —— 模块业务路由尚未迁入，
  属 B-02/B-03/B-04 范围（菜单侧已做“未注册即灰显”降级，不产生 500）
```

**已知缺口（不粉饰）**：`route:list` 里目前只有 Core 路由，所以“菜单项 route 名全部可解析”这一条要等各模块迁入后复验；本轮给出的是“菜单结构与权限闸门全部就位 + 未解析项精确计数”。
