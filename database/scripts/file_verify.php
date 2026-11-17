<?php

/**
 * TC-12 文件中心验收：上传 / 下载 / 版本 / 回收站 四条链路 + 中文文件名 + 正文抽取 + OnlyOffice 回调。
 *
 * 断言方式尽量贴近真实 HTTP：用 Symfony 的 HttpKernel 发内部请求（走完整中间件与控制器），
 * 只对「无登录态的签名 URL / 回调」直接构造请求，验证 key 与签名两条防线。
 * 用完自清：删掉 [verify] 文件、快照与回收站残留。
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Core\Models\File;
use App\Core\Models\FileVersion;
use App\Core\Models\User;
use App\Core\Services\FileService;
use App\Core\Services\OnlyOfficeService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

$service = app(FileService::class);
$office = app(OnlyOfficeService::class);
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$pass = 0;
$fail = 0;

function ok(bool $cond, string $label, string $extra = ''): void
{
    global $pass, $fail;
    if ($cond) {
        $pass++;
        echo "  OK   {$label}".($extra ? "  [{$extra}]" : '')."\n";
    } else {
        $fail++;
        echo "  FAIL {$label}".($extra ? "  [{$extra}]" : '')."\n";
    }
}

function cleanup(): void
{
    foreach (File::withTrashed()->where(function ($q) { $q->where('original_name', 'like', '验收%')->orWhere('original_name', 'like', '%[verify]%'); })->get() as $file) {
        $paths = [$file->path];
        foreach (FileVersion::where('file_id', $file->id)->get() as $v) {
            $paths[] = $v->path;
            $paths[] = $v->snapshot['path'] ?? null;
        }
        FileVersion::where('file_id', $file->id)->delete();
        $file->forceDelete();
        foreach (array_filter($paths) as $path) {
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }
    }
}

cleanup();

$admin = User::where('username', 'admin')->first();
ok($admin !== null, '拿到 admin 账号');

// 内部请求要走 auth 中间件，先把这个账号塞进 web guard（不落 session）
Illuminate\Support\Facades\Auth::guard('web')->setUser($admin);

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ddwl-file-verify';
if (! is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}

// 带中文 + 空格 + 括号的文件名（最容易乱码的组合）
$chineseName = '验收-中文名称 测试（括号）.txt';
$content = "第一行：中文内容不乱码\n第二行：含全角标点，验收用。\n";
$srcPath = $tmpDir.DIRECTORY_SEPARATOR.'v1.txt';
file_put_contents($srcPath, $content);

echo "== 1. 上传（中文文件名）\n";
$upload = new UploadedFile($srcPath, $chineseName, 'text/plain', null, true);
$file = $service->store($upload, $admin, 'erp', ['category' => 'verify']);
ok($file->original_name === $chineseName, '原始文件名逐字保存', $file->original_name);
ok(str_contains($file->path, 'files/erp/') && ! str_contains($file->path, '验收'), '磁盘路径用 UUID（不把中文塞进路径）', $file->path);
ok(Storage::disk('local')->exists($file->path), '物理文件已落盘');
ok($file->mime_type === 'text/plain', 'MIME 按扩展名推断正确', $file->mime_type);
ok($file->version === 1, '首传版本 v1');

echo "== 2. 下载（Content-Disposition 中文编码）\n";
$request = Request::create('/files/'.$file->id.'/download', 'GET');
$request->setUserResolver(fn () => $admin);
$response = $kernel->handle($request);
$disposition = (string) $response->headers->get('Content-Disposition');
ok($response->getStatusCode() === 200, '下载返回 200', 'status='.$response->getStatusCode());
ok(str_contains($disposition, "filename*=UTF-8''"), '用 RFC5987 的 filename* 传中文名', $disposition);
ok(str_contains($disposition, rawurlencode('验收')), '中文名在 header 里被 URL 编码而不是乱码');
ok(str_starts_with((string) $response->headers->get('Content-Type'), 'text/plain'), 'Content-Type 正确', (string) $response->headers->get('Content-Type'));

echo "== 3. 新版本\n";
$v2Path = $tmpDir.DIRECTORY_SEPARATOR.'v2.txt';
file_put_contents($v2Path, $content."第三行：这是第二版新增内容。\n");
$service->newVersion($file, new UploadedFile($v2Path, $chineseName, 'text/plain', null, true), $admin);
$file->refresh();
ok($file->version === 2, '版本号 +1', 'v'.$file->version);
$version = FileVersion::where('file_id', $file->id)->first();
ok($version !== null && $version->version === 1, '保留了 v1 快照记录');
ok($version && Storage::disk('local')->exists($version->snapshot['path'] ?? ''), 'v1 快照物理文件还在', (string) ($version->snapshot['path'] ?? ''));
ok(str_contains($service->textOf($file), '第二版新增内容'), '抽取到 v2 的正文');
ok(str_contains($service->textOf($file), '中文内容不乱码'), 'v2 正文中文正常');

echo "== 4. 回收站（软删 → 恢复 → 彻底删）\n";
$service->moveToTrash($file, $admin);
ok($file->fresh()->trashed(), '软删后进回收站');
ok(Storage::disk('local')->exists($file->path), '软删不碰物理文件（可恢复）');
ok(File::where('id', $file->id)->count() === 0, '在用列表里不再出现');
$restored = $service->restore($file->id, $admin);
ok(! $restored->trashed() && $restored->deleted_by === null, '恢复后 trash 标记与 deleted_by 清空');

$service->moveToTrash($restored, $admin);
$mainPath = $restored->path;
$snapshotPath = $version->snapshot['path'] ?? '';
$service->purge($restored->id, $admin);
ok(File::withTrashed()->where('id', $restored->id)->count() === 0, '彻底删除后记录消失');
ok(! Storage::disk('local')->exists($mainPath), '主文件物理删除');
ok(! Storage::disk('local')->exists($snapshotPath), '历史快照同步清理');
ok(FileVersion::where('file_id', $restored->id)->count() === 0, '版本记录一并清空');

echo "== 5. 权限\n";
$other = User::where('id', '!=', $admin->id)->whereNotNull('department_id')->first();
$private = $service->store(new UploadedFile($srcPath, '验收-私有文件[verify].txt', 'text/plain', null, true), $admin, 'erp', ['visibility' => File::VISIBILITY_PRIVATE]);
$req = Request::create('/files/'.$private->id.'/download', 'GET');
$req->setUserResolver(fn () => $other);
Illuminate\Support\Facades\Auth::guard('web')->setUser($other);
$res = $kernel->handle($req);
ok($res->getStatusCode() === 403, '私有文件对他人 403', 'status='.$res->getStatusCode());
Illuminate\Support\Facades\Auth::guard('web')->setUser($admin);

echo "== 6. 签名 URL 与 OnlyOffice 回调\n";
$rawUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('core.files.raw', now()->addMinutes(10), ['file' => $private->id, 'key' => $office->keyFor($private)]);
$req = Request::create($rawUrl, 'GET');
$res = $kernel->handle($req);
ok($res->getStatusCode() === 200, '带签名 + 正确 key 可取文件', 'status='.$res->getStatusCode());

$badUrl = preg_replace('/key=[^&]+/', 'key=wrong', $rawUrl);
$req = Request::create($badUrl, 'GET');
$res = $kernel->handle($req);
ok($res->getStatusCode() === 403, 'key 不对被拒', 'status='.$res->getStatusCode());

$req = Request::create('/files/onlyoffice/callback/'.$private->id, 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['key' => 'wrong', 'status' => 2]));
$res = $kernel->handle($req);
$json = json_decode((string) $res->getContent(), true);
ok(($json['error'] ?? null) === 1, '回调 key 不匹配返回 error=1', (string) $res->getContent());

$before = $private->fresh()->version;
$req = Request::create('/files/onlyoffice/callback/'.$private->id, 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['key' => $office->keyFor($private), 'status' => 1]));
$res = $kernel->handle($req);
$json = json_decode((string) $res->getContent(), true);
ok(($json['error'] ?? null) === 0 && $private->fresh()->version === $before, 'status=1（编辑中）不触发保存');

echo "== 7. 演示数据\n";
echo "  文件 ".File::count()." 个（回收站 ".File::onlyTrashed()->count()."）\n";
foreach (File::withTrashed()->orderBy('id')->get() as $f) {
    echo "    #{$f->id} {$f->original_name} · v{$f->version} · ".$service->formatSize($f->size).' · '.($f->trashed() ? '回收站' : '在用')."\n";
}

cleanup();
@rmdir($tmpDir);

echo "\n通过 {$pass} 项，失败 {$fail} 项\n";
exit($fail === 0 ? 0 : 1);
