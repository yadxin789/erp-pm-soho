<?php

namespace App\Core\Services;

use App\Core\Models\File;
use App\Core\Models\FileVersion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * OnlyOffice 协同编辑接入（TC-12）。
 *
 * PM 原实现挂在 CollabDocument 上（doc_key + file_repository 磁盘 + history 目录）。
 * 上收到 Core 后改为「任意 File 都能开编辑器」：
 *   - key = HMAC(file.id:version:path)，无状态、不落库，改一次版本就自动换 key；
 *   - 服务器取文件走一次性签名 URL（`files.raw`），不暴露登录态；
 *   - 保存回调（status 2/6）先把当前文件快照进 file_versions，再用回调给的临时 URL 覆盖正文并 version+1。
 * 这样 PM 的协同文档迁入后直接复用，ERP/SOHO 的「附件预览编辑」也能用同一套回调。
 */
class OnlyOfficeService
{
    public function enabled(): bool
    {
        return (string) config('services.onlyoffice.url', '') !== '';
    }

    public function keyFor(File $file): string
    {
        return substr(hash_hmac('sha256', $file->id.':'.$file->version.':'.$file->path, (string) config('app.key')), 0, 20);
    }

    /**
     * 编辑器配置（前端 docEditor 直接吃这份 JSON）。
     */
    public function configFor(File $file, int $userId, string $userName): array
    {
        $rawUrl = URL::temporarySignedRoute('core.files.raw', now()->addHours(6), ['file' => $file->id, 'key' => $this->keyFor($file)]);
        $callbackUrl = url('/files/onlyoffice/callback/'.$file->id);

        return [
            'documentType' => $this->documentType($file->extension()),
            'document' => [
                'fileType' => $file->extension(),
                'key' => $this->keyFor($file),
                'title' => $file->original_name,
                'url' => $rawUrl,
                'permissions' => ['edit' => true, 'download' => true, 'print' => false],
            ],
            'editorConfig' => [
                'mode' => 'edit',
                'callbackUrl' => $callbackUrl,
                'lang' => 'zh-CN',
                'user' => ['id' => (string) $userId, 'name' => $userName],
                'customization' => ['autosave' => true, 'forcesave' => true, 'compactHeader' => true],
            ],
            'height' => '100%',
            'width' => '100%',
        ];
    }

    /**
     * 处理保存回调。返回 [ok, message]。
     */
    public function handleCallback(File $file, array $payload): array
    {
        if (($payload['key'] ?? null) !== $this->keyFor($file)) {
            return [false, 'key 不匹配'];
        }

        $status = (int) ($payload['status'] ?? 0);
        // 2 = 编辑完成待保存；6 = 强制保存（forcesave）
        if (! in_array($status, [2, 6], true) || empty($payload['url'])) {
            return [true, 'ignored status='.$status];
        }

        $content = Http::timeout(30)->get((string) $payload['url'])->throw()->body();
        if ($content === '') {
            return [false, '回调文件为空'];
        }

        // 旧文件先快照，再覆盖正文
        $snapshot = sprintf('files/%s/versions/%d/v%d.%s', $file->module, $file->id, $file->version, $file->extension());
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->copy($file->path, $snapshot);
        }

        FileVersion::create([
            'file_id' => $file->id,
            'task_id' => $file->task_id,
            'version' => $file->version,
            'disk' => $file->disk,
            'path' => $file->path,
            'uploader_id' => $file->uploader_id,
            'auditor_id' => $file->auditor_id,
            'status' => $file->status,
            'snapshot' => ['path' => $snapshot],
        ]);

        Storage::disk($file->disk)->put($file->path, $content);
        $file->update([
            'size' => Storage::disk($file->disk)->size($file->path),
            'version' => $file->version + 1,
        ]);

        return [true, 'saved v'.$file->version];
    }

    private function documentType(string $ext): string
    {
        return match ($ext) {
            'doc', 'docx', 'odt', 'rtf', 'txt' => 'word',
            'xls', 'xlsx', 'ods', 'csv' => 'cell',
            'ppt', 'pptx', 'odp' => 'slide',
            default => 'word',
        };
    }
}
