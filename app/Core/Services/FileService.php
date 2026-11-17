<?php

namespace App\Core\Services;

use App\Core\Models\File;
use App\Core\Models\FileVersion;
use App\Core\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * 文件服务（TC-12）：上传 / 下载 / 版本 / 回收站四条链路，三源共用。
 *
 * 沿用 PM 的约定：
 *   - 允许的扩展名与 20MB 上限（`file_verify` 会校验）；
 *   - 软删除只写 deleted_at + deleted_by，**绝不碰物理文件**，保证可恢复；
 *   - 彻底删除「先删记录、后删文件」——宁可留孤儿文件，也不出现「记录在、文件丢」。
 * 新增能力：多模块 + 部门可见性 + 版本快照。
 *
 * 中文文件名：磁盘上落 UUID 名（避免编码/特殊字符踩坑），原名完整保存在 original_name，
 * 下载响应由 Symfony 生成 `filename*=UTF-8''…`，浏览器端不会乱码。
 */
class FileService
{
    public const MAX_KB = 20480;

    public const ALLOWED_EXT = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'ofd',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp',
        'txt', 'md', 'csv', 'json', 'xml', 'log',
        'zip', 'rar', '7z', 'tar', 'gz',
    ];

    public const PREVIEWABLE_MIME = [
        'application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'text/plain', 'text/csv',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function __construct(private DocumentTextExtractor $extractor)
    {
    }

    /**
     * 首传。
     */
    public function store(UploadedFile $upload, User $user, string $module, array $options = []): File
    {
        $this->validateUpload($upload);

        $original = $this->normalizeName($upload->getClientOriginalName());
        $ext = strtolower($upload->getClientOriginalExtension() ?: $upload->extension());
        $disk = $options['disk'] ?? 'local';
        $category = $options['category'] ?? 'attachment';
        $path = sprintf('files/%s/%s/%s.%s', $module, date('Ymd'), Str::uuid()->toString(), $ext);

        Storage::disk($disk)->put($path, file_get_contents($upload->getRealPath()));

        return File::create([
            'module' => $module,
            'department_id' => $options['department_id'] ?? $user->department_id,
            'task_id' => $options['task_id'] ?? null,
            'uploader_id' => $user->id,
            'auditor_id' => $options['auditor_id'] ?? null,
            'name' => $options['name'] ?? $original,
            'original_name' => $original,
            'disk' => $disk,
            'path' => $path,
            'size' => $upload->getSize(),
            'mime_type' => $this->resolveMime($upload, $ext),
            'category' => $category,
            'visibility' => $options['visibility'] ?? File::VISIBILITY_MODULE,
            'version' => 1,
            'status' => $options['status'] ?? File::STATUS_APPROVED,
        ]);
    }

    /**
     * 上传新版本：旧文件先快照进 file_versions，再把主记录的 path/version 换掉。
     * 「同名不同内容」在 PM 里就是靠这个表达，绝不覆盖历史。
     */
    public function newVersion(File $file, UploadedFile $upload, User $user): File
    {
        $this->validateUpload($upload);

        $oldVersion = max(1, (int) $file->version);
        $snapshot = sprintf('files/%s/versions/%d/v%d.%s', $file->module, $file->id, $oldVersion, $file->extension());
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->copy($file->path, $snapshot);
        }

        FileVersion::create([
            'file_id' => $file->id,
            'task_id' => $file->task_id,
            'version' => $oldVersion,
            'disk' => $file->disk,
            'path' => $file->path,
            'uploader_id' => $file->uploader_id,
            'auditor_id' => $file->auditor_id,
            'status' => $file->status,
            'audit_time' => $file->audit_time,
            'audit_reason' => $file->audit_reason,
            'snapshot' => ['path' => $snapshot],
        ]);

        $original = $this->normalizeName($upload->getClientOriginalName());
        $ext = strtolower($upload->getClientOriginalExtension() ?: $upload->extension());
        $path = sprintf('files/%s/%s/%s.%s', $file->module, date('Ymd'), Str::uuid()->toString(), $ext);
        Storage::disk($file->disk)->put($path, file_get_contents($upload->getRealPath()));

        $file->update([
            'original_name' => $original,
            'name' => $file->name && $file->name !== '' ? $file->name : $original,
            'path' => $path,
            'size' => $upload->getSize(),
            'mime_type' => $this->resolveMime($upload, $ext),
            'version' => $oldVersion + 1,
            'status' => File::STATUS_PENDING,
        ]);

        return $file;
    }

    /**
     * 软删除到回收站。
     */
    public function moveToTrash(File $file, User $user): void
    {
        $file->deleted_by = $user->id;
        $file->save();
        $file->delete();
    }

    public function restore(int $fileId, User $user): File
    {
        $file = File::withTrashed()->findOrFail($fileId);
        if (! $user->is_admin && $file->deleted_by !== $user->id && $file->uploader_id !== $user->id) {
            throw new RuntimeException('只能恢复自己删除或自己上传的文件');
        }
        $file->restore();
        $file->deleted_by = null;
        $file->save();

        return $file;
    }

    /**
     * 彻底删除：先删记录、后删物理文件（含历史快照）。
     */
    public function purge(int $fileId, User $user): void
    {
        $file = File::withTrashed()->findOrFail($fileId);
        if (! $user->is_admin && $file->deleted_by !== $user->id && ($file->uploader_id !== $user->id || ! $file->trashed())) {
            throw new RuntimeException('只能彻底删除回收站里属于自己的文件');
        }

        $disk = $file->disk;
        $paths = [$file->path];
        foreach (FileVersion::where('file_id', $file->id)->get() as $version) {
            $paths[] = $version->path;
            $paths[] = $version->snapshot['path'] ?? null;
        }

        FileVersion::where('file_id', $file->id)->delete();
        $file->forceDelete();

        foreach (array_filter($paths) as $path) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    /**
     * 当前账号可见的「在用文件」。模块内公开的文件 + 自己上传的私有文件。
     */
    public function visibleTo(User $user, ?string $module, ?string $keyword = null): Collection
    {
        return File::query()
            ->when($module, fn ($q) => $q->where('module', $module))
            ->where(function ($q) use ($user) {
                $q->where('visibility', File::VISIBILITY_MODULE)
                    ->orWhere('uploader_id', $user->id)
                    ->orWhere('auditor_id', $user->id);
            })
            ->when($keyword, fn ($q) => $q->where(function ($inner) use ($keyword) {
                $inner->where('name', 'like', "%{$keyword}%")
                    ->orWhere('original_name', 'like', "%{$keyword}%");
            }))
            ->with(['uploader', 'versions'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * 个人回收站：我删的 + 我上传的（管理员看全部）。
     */
    public function trashFor(User $user, ?string $module): Collection
    {
        return File::onlyTrashed()
            ->when($module, fn ($q) => $q->where('module', $module))
            ->when(! $user->is_admin, fn ($q) => $q->where(function ($inner) use ($user) {
                $inner->where('deleted_by', $user->id)->orWhere('uploader_id', $user->id);
            }))
            ->with(['uploader'])
            ->orderByDesc('deleted_at')
            ->get();
    }

    public function canPreview(File $file): bool
    {
        return in_array($file->mime_type, self::PREVIEWABLE_MIME, true);
    }

    public function formatSize(?int $bytes): string
    {
        $bytes = (int) $bytes;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    /**
     * 抽取正文（供 AI 读取）。失败返回空串，不影响主流程。
     */
    public function textOf(File $file, int $maxChars = 20000): string
    {
        $absolute = Storage::disk($file->disk)->path($file->path);
        if (! is_file($absolute)) {
            return '';
        }

        return $this->extractor->extract($absolute, $file->extension(), $maxChars);
    }

    /**
     * MIME 解析：优先浏览器给的类型，拿不到（CLI/命令行上传常见）就按扩展名推断，
     * 否则预览白名单永远命中不了 application/octet-stream。
     */
    private function resolveMime(UploadedFile $upload, string $ext): string
    {
        $client = (string) $upload->getClientMimeType();
        if ($client !== '' && $client !== 'application/octet-stream') {
            return $client;
        }

        $guess = \Symfony\Component\Mime\MimeTypes::getDefault()->getMimeTypes($ext);

        return $guess[0] ?? 'application/octet-stream';
    }

    private function validateUpload(UploadedFile $upload): void    {
        if (! $upload->isValid()) {
            throw new RuntimeException('文件上传失败，请重试');
        }
        $ext = strtolower($upload->getClientOriginalExtension() ?: $upload->extension());
        if (! in_array($ext, self::ALLOWED_EXT, true)) {
            throw new RuntimeException('不支持的文件类型：'.$ext);
        }
        if ($upload->getSize() > self::MAX_KB * 1024) {
            throw new RuntimeException('文件超过 '.self::MAX_KB.' KB 上限');
        }
    }

    /**
     * 规范化原始文件名：只清掉控制字符与路径分隔符，**不做转码**（PHP 收到的是 UTF-8）。
     */
    private function normalizeName(string $name): string
    {
        $name = str_replace(['\\', '/', "\0"], '', $name);
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? $name;
        $name = trim($name);

        return $name !== '' ? $name : '未命名文件';
    }
}
