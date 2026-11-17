# TC-12 文件与知识库（Core 文件中心）

三模块共用一套文件库：ERP 的附件、PM 的协同文档/回收站、SOHO 的素材上传，统一落到 Core 的
`files` / `file_versions` 两张表，按 `module` 隔离可见范围。**没有移植各自的控制器与视图**，
只保留业务语义（上传/版本/审核/回收站/在线编辑/正文抽取），页面统一走 `core::files.*`。

## 表与字段

`files`（22 列，TC-02 已建）：`module / department_id / task_id / uploader_id / auditor_id /
name / original_name / disk / path / size / mime_type / category / visibility / version / status /
audit_time / audit_reason / deleted_by / created_at / updated_at / deleted_at`。

`file_versions`（14 列）：`file_id / task_id / version / disk / path / uploader_id / auditor_id /
status / audit_time / audit_reason / snapshot / created_at / updated_at`。

- **`snapshot` 是 JSON 列**，不是字符串；存 `{"path": "files/…/v1.docx"}`。直接塞字符串会报
  `SQLSTATE[22032] 3140 Invalid JSON text`（本轮实测踩到，已改为数组 + 模型 cast）。
- `path` 里只有 UUID，**中文原名只进 `original_name`**，磁盘不受编码影响（实测「供应商送货单-宁波华强电子-20261117.txt」落成
  `files/erp/20261117/f83f844b-….txt`）。

## 代码分布

| 文件 | 作用 |
|---|---|
| `app/Core/Models/File.php` | 22 列 fillable、`STATUS_*` / `VISIBILITY_*` 常量、`uploader/auditor/department/versions` 关系、`scopeForModule`、`extension()`、`statusLabel()`、SoftDeletes |
| `app/Core/Models/FileVersion.php` | 14 列 fillable、`file()` / `uploader()`、`snapshot => 'array'` cast |
| `app/Core/Services/FileService.php` | `store` / `newVersion` / `moveToTrash` / `restore` / `purge` / `visibleTo` / `trashFor` / `canPreview` / `formatSize` / `textOf` / `resolveMime` / `normalizeName` |
| `app/Core/Services/DocumentTextExtractor.php` | txt/csv/log/md 直读；pdf 走 `smalot/pdfparser`；docx/odt 解 zip 读 `word/document.xml`、`content.xml`；pptx 逐 slide；**xlsx 自解析** `xl/sharedStrings.xml` + `xl/worksheets/sheet*.xml`（不额外引依赖） |
| `app/Core/Services/OnlyOfficeService.php` | `enabled` / `keyFor` / `configFor` / `handleCallback` |
| `app/Core/Http/Controllers/FileController.php` | index / store / newVersion / download / preview / raw / office / callback / destroy / restore / purge + `authorizeAccess` + `respondWith` |
| `resources/views/core/files/index.blade.php` | 上传表单 + 在用列表（图标操作列）+ 回收站 tab |
| `resources/views/core/files/office.blade.php` | 未配置提示分支 / 内嵌 DocsAPI 分支 |
| `routes/core.php` | 11 条 `core.files.*` |
| `database/seeders/FileDemoSeeder.php` | 3 份中文名演示文件（txt / csv / 现造 docx） |
| `database/scripts/file_verify.php` | 7 段 28 项断言 |

## 关键实现

1. **上传**：单文件 ≤ 20 MB（`MAX_KB=20480`），26 种扩展名白名单，一次最多 5 个；`visibility=module`（模块内全员可见，`status=approved`）或 `private`（仅上传人/审核人，`status=pending`）。落盘路径 `files/{module}/{Ymd}/{uuid}.{ext}`。
2. **版本**：`newVersion` 先把旧文件 `copy` 成 `files/{module}/versions/{id}/v{old}.{ext}` 并写一条 `FileVersion`（`snapshot={"path":…}`），再把主记录 `path/size/version+1` 换掉。历史版本不可回滚成当前（PM 原实现也没有回滚），只作查证。
3. **删除三级**：`moveToTrash` 只写 `deleted_by` + 软删，**绝不碰物理文件**；`restore` 校验「我删的 / 我传的 / admin」；`purge` **先删记录再删文件**（主文件 + 所有版本路径 + snapshot 路径）。
4. **权限**：`visibleTo = 模块公开 ∪ 我上传 ∪ 我审核`；`trashFor` = 我删的 ∪ 我传的（admin 全量）。控制器 `authorizeAccess` 同样口径，越权下载/预览返回 403。
5. **下载/预览**：`respondWith` 手写 `Content-Disposition: attachment|inline; filename*=UTF-8''{rawurlencode}`，中文名靠 RFC5987，不靠 `filename=`。
6. **MIME**：命令行/API 构造的 `UploadedFile` 客户端类型经常是 `application/octet-stream`，会导致预览白名单永远不命中；`resolveMime()` 改成「客户端类型非 octet-stream 才采用，否则按扩展名用 `Symfony\Component\Mime\MimeTypes` 推断」。
7. **正文抽取**：`MAX_CHARS=20000`、`MAX_BYTES=20MB`，全部 try/catch 返回空串——抽取失败不影响上传与下载。实测 docx 抽出中文正文（「智能网关 V2 接口评审纪要」四段）、csv 抽出表头与三行、txt 抽出整段送货单。
8. **OnlyOffice**：`keyFor()` = `substr(hash_hmac('sha256', "{id}:{version}:{path}", APP_KEY), 0, 20)`，**无状态**，换版本自动换 key；`document.url` 用 `URL::temporarySignedRoute('core.files.raw', 6h, […, 'key'=>…])`，`callbackUrl` 固定 `/files/onlyoffice/callback/{id}`。`handleCallback` 校验 key → `status 2/6` 才保存 → `Http::get(callback url)` 拉新文件 → 旧文件 copy 成 snapshot + 写 FileVersion → 覆盖正文 → `size/version+1`。
9. **CSRF**：回调来自文档服务器，没有会话与 token，`bootstrap/app.php` 加 `validateCsrfTokens(except: ['files/onlyoffice/callback/*'])`，安全靠 document key。

## 路由（真实 `route:list --path=files`，11 条）

```
GET|HEAD  files                          core.files.index
POST      files                          core.files.store
GET|HEAD  files/{file}/download          core.files.download
GET|HEAD  files/{file}/preview           core.files.preview
GET|HEAD  files/{file}/office            core.files.office
POST      files/{file}/versions          core.files.versions.store
DELETE    files/{file}                   core.files.destroy
POST      files/trash/{id}/restore       core.files.restore
DELETE    files/trash/{id}               core.files.purge
GET|HEAD  files/{file}/raw                core.files.raw          ← 签名 URL，给 OnlyOffice 取文件
POST      files/onlyoffice/callback/{file} core.files.callback   ← 无 auth / 无 CSRF，靠 key
```

## 验收

```
php database/scripts/file_verify.php     # 28 项断言：OK 28 / FAIL 0
```

断言覆盖：中文名逐字保存、路径不塞中文、MIME 推断、下载 200 + `filename*=UTF-8''` 编码、v2 上传后 v1 快照仍在、v2 正文可抽、软删不碰物理文件、恢复清 `deleted_by`、彻底删后主文件与快照全消失、私有文件他人 403、签名 URL + 正确 key 200、错误 key 403、回调 key 不符 `{"error":1}`、`status=1` 不触发保存。

界面取证 `docs/screenshots-tc12/`：

| 图 | 内容 |
|---|---|
| `01-files-soho-empty.png` | SOHO 视角空文件库（同期 ERP 有 4 个文件），证明按 `module` 隔离 |
| `02-files-erp-list.png` | ERP 在用列表：文件名/大小/版本/上传人/时间 + 图标操作列（下载/预览/编辑/删除） |
| `03-files-versions.png` | 展开历史版本（v1 + 上传人 + 时间） |
| `04-files-trash.png` | 回收站：文件名 + v2 + 删除时间 + 恢复/彻底删除 |
| `05-files-office-unconfigured.png` | 未配 `ONLYOFFICE_URL` 时的提示分支（回调地址按 file id 给出） |
| `06-files-preview-inline.png` | txt 内联预览（真实流式输出，非直链） |

界面实测：admin 登录 → `/files` → 选「客户回款对账单-2026年11月.csv」上传（1 个文件，中文名原样）→ 展开「上传新版本」提 v2（提示「已上传新版本 v2」）→ 图标删除 → 回收站「恢复」→ 全部无脚本介入。

## 已知边界

- OnlyOffice 真机 6 小时签名 URL、`status=2` 真拉取覆盖这条分支**只有代码没有真机验证**：本机没有部署文档服务器（`.env` 的 `ONLYOFFICE_URL` 留空），页面走「未配置」分支。要验需要起 `onlyoffice/documentserver` 并把容器能访问的 `APP_URL` 配上。
- `handleCallback` 的 `status=2` 分支在验收脚本里只验了 key 校验与 `status=1` 不保存，**没有 mock 一个可下载的 callback url 跑真覆盖**。
- 预览走内联流（txt/csv 直接原文），没有做 PDF 转换/图片查看器；PDF 预览依赖浏览器自带阅读器。
- 回收站没有「超期自动清理」定时任务（PM 的 `PurgeExpiredTrash` 未迁入），需要时按 `deleted_at` 加一条命令。
- `newVersion` 会用新上传的文件名替换 `original_name`（PM 同行为），如果只想换内容不改名，需要在表单里保留原名——当前未做开关。
