# T0-12 真实基线（route:list + 界面截图）

采集时间：2026-11-17　PHP：8.3.36　采集方式：**真实运行时**（三套应用各自 `artisan serve` 起服务 + 浏览器逐页截图），无任何静态解析。

## 1. 路由基线（真实 `php artisan route:list --json`）

| 模块 | 服务端口 | 总路由 | 命名路由 | 文件 |
|---|---|---|---|---|
| ERP（D:\laravel12-erp） | 8021 | 523 | 497 | `routes-erp.json` |
| PM（E:\my-pm\pm-server-openEuler） | 8022 | 271 | 268 | `routes-pm.json` |
| SOHO（E:\_tmp_soho_baseline 降级副本） | 8023 | 576 | 572 | `routes-soho.json` |
| 合计 | | **1370** | 1337 | |

> 早前静态解析得到的 soho 399 处 `Route::` 严重偏低（真实 576 条，差异主要来自 Filament 资源路由、闭包路由与 `Route::resource` 展开），已按用户要求删除 `static-routes.csv`，不保留兜底。

## 2. 界面截图基线（24 张，967×883）

清单见 `screenshots-manifest.csv`。覆盖每模块：登录页 → 首页 → 2~8 个代表性业务页（含列表、配置、看板、聊天）。

- ERP：登录、系统概览、客户管理、物料管理、用户管理、审批流配置、操作日志
- PM：登录、管理员首页、用户管理、部门管理、项目管理、知识库、AI 设置
- SOHO：登录、管理员首页、用户列表、商品 SPU 列表、订单列表、平台 SKU 映射、售后列表、审批实例列表、聊天室、审计报表

## 3. 三套应用怎么起（复现步骤）

```powershell
$php = "C:\tools\php83\php.exe"
# ERP
$env:APP_URL="http://127.0.0.1:8021"; & $php artisan serve --host=127.0.0.1 --port=8021   # cwd: D:\laravel12-erp
# PM
$env:APP_URL="http://127.0.0.1:8022"; & $php artisan serve --host=127.0.0.1 --port=8022   # cwd: E:\my-pm\pm-server-openEuler
# SOHO（降级副本）
$env:APP_URL="http://127.0.0.1:8023"; & $php artisan serve --host=127.0.0.1 --port=8023   # cwd: E:\_tmp_soho_baseline
```

`APP_URL` 用进程环境变量覆盖 `.env`（Laravel 的 Dotenv 不覆盖已存在的环境变量），因此**未改动三个源项目的 `.env`**。

## 4. SOHO 如何降到 PHP 8.3（原项目零改动）

`E:\ddwl-soho` 的 `composer.lock` 由 PHP 8.4 环境生成，其中 7 个包硬性要求 ≥8.4：

| 包 | 原版本 | 要求 | 降到 |
|---|---|---|---|
| symfony/clock、css-selector、string | v8.1.0 | `>=8.4.1` | v7.4.x |
| symfony/event-dispatcher、translation、yaml | v8.1.1 | `>=8.4.1` | v7.4.x |
| spatie/laravel-model-states | 2.14.2 | `^8.4` | 2.12.1 |

（另 3 个包 `cron-expression`、`htmlpurifier`、`openspout` 的约束里包含 8.3，本不拦路。）

做法：把整个项目复制到 `E:\_tmp_soho_baseline`（robocopy，排除 `.git`/`node_modules`），在副本里：

```powershell
$php="C:\tools\php83\php.exe"; $env:COMPOSER_HOME="E:\_tmp_composer_home"
cd E:\_tmp_soho_baseline
& $php C:\tools\php83\composer.phar config platform.php 8.3.36
& $php C:\tools\php83\composer.phar require "spatie/laravel-model-states:^2.12" --no-update
& $php C:\tools\php83\composer.phar update symfony/clock symfony/css-selector symfony/event-dispatcher symfony/string symfony/translation symfony/yaml spatie/laravel-model-states --with-all-dependencies --no-scripts
```

结果：`vendor/composer/platform_check.php` 从 `PHP_VERSION_ID >= 80401` 变成 `>= 80300`，`php artisan --version` = `Laravel Framework 12.72.0`，业务代码 **0 行改动**。

副本还需一次数据库初始化（原项目用 sqlite 且无数据库文件）：

```powershell
& $php artisan migrate:fresh --seed --force    # 交互问题"是否导入演示数据"答 yes
```

> 注意：`migrate:fresh --seed` 会停在交互确认上，脚本里必须把答案喂进去（`"yes" | & $php artisan ...`），否则挂死。

## 5. 截图用的账号

| 模块 | 登录字段 | 账号 | 来源 |
|---|---|---|---|
| ERP | 用户名 / 邮箱 | admin@admin.com / 123456 | `database/seeders/DemoDataSeeder.php` |
| PM | 用户名 | admin / 123456 | `database/seeders/UserSeeder.php` |
| SOHO | 邮箱 | admin@ddwl-soho.com / admin123456 | `database/seeders/RoleAndPermissionSeeder.php` |

（SOHO 另建了 24 个演示账号，密码统一 `demo123456`，部门覆盖总经办/技术部/审计部/财务部/运营部等。）

## 6. 采集过程中发现的真实差异（写进后续迁移注意事项）

1. **SOHO 有 405 陷阱**：`/products`、`/orders`、`/shops` 是 `Route::delete` 的 RESTful 单数资源路径的复数误用，真实列表页是 `/product`、`/order`、`/platform-sku`。迁移时不能按"复数=列表"的直觉拼 URL。
2. **SOHO `/platform-accounts` 对超级管理员返回 403**（`USER DOES NOT HAVE THE RIGHT ROLES.`）——说明该菜单的权限点比 `super_admin` 角色更细，迁移权限时是重点核对项。
3. **PM 无 `/projects` 路由**，列表页是 `/project`（单数）；早期静态清单里猜的 `/projects` 会 404。
4. SOHO 首页标题是「超级管理员操作页面」，非固定品牌名（按角色动态），G3 视觉比对时不能用标题做断言。
## 7. 合并站基线（当前唯一验收口径，2026-11-19 重跑）

以上 §1~§6 为 **T0-12 参考目录采集记录**，仅作历史证据保留。合并站 `E:\erp-pm-soho` 自 2026-11-19 起以本节为准：

| 文件 | 生成方式 | 当前数值 |
|---|---|---|
| `routes-baseline.{csv,json}` | `php artisan route:list --json` → `_tmp_baseline_routes.php` | 547 条 GET 无参路由（core 17 / erp 158 / pm 73 / soho 299） |
| `status-baseline.{csv,json}` | 分模块账号真实 HTTP 探测（`_tmp_baseline_probe.php`） | core 15·2 / erp 156·2 / pm 68·5 / soho 286·13；**500=0、404=0、403=0** |
| `功能基线清单.md` | `_tmp_baseline_doc.php` | 547 页清单，缺口 0 |

复现命令（合并站，PHP 8.3）：

```powershell
cd E:\erp-pm-soho
& "C:\tools\php83\php.exe" artisan route:clear
& "C:\tools\php83\php.exe" artisan route:list --json | Set-Content "E:\_tmp_routes_merged.json" -Encoding UTF8
& "C:\tools\php83\php.exe" E:\_tmp_baseline_routes.php
& "C:\tools\php83\php.exe" artisan cache:clear
& "C:\tools\php83\php.exe" E:\_tmp_baseline_probe.php      # 全量约 5~6 分钟
& "C:\tools\php83\php.exe" E:\_tmp_baseline_doc.php
```

探测账号（合并站）：erp `admin@erp.demo` / `sales@demo.com`、pm `admin@admin.com`、soho+core `admin@ddwl-soho.com`。

> 增量截图基线（P1-5 PM 电话会议、P1-6 新建单据 / 盘点实盘录入 / 调整申请 共 4 页）尚未落入 `docs\screenshots-visual\`：这些页面要求 `warehouse_manager` 或 `super_admin` 登录态，当前浏览器会话为 `tenant_admin`，按 D23 **待部署到服务器后一次性补拍**，本期不再逐页采集；功能验收以 功能基线清单.md 状态行为准。
