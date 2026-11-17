<?php

namespace App\Core\Http\Controllers;

use App\Core\Models\File;
use App\Core\Services\FileService;
use App\Core\Services\OnlyOfficeService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * 文件中心（TC-12）：上传 / 下载 / 版本 / 回收站 + OnlyOffice 打开与回调。
 *
 * 可见范围：模块内公开文件 + 自己上传/自己审核的文件；回收站按「我删的 / 我上传的」。
 */
class FileController extends Controller
{
    public function __construct(
        private FileService $files,
        private OnlyOfficeService $onlyoffice
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $module = $request->session()->get('active_module', 'core');
        $tab = $request->string('tab')->toString() === 'trash' ? 'trash' : 'active';
        $keyword = $request->string('q')->toString() ?: null;

        $items = $this->files->visibleTo($user, $module, $keyword);
        $trash = $this->files->trashFor($user, $module);

        return view('core::files.index', [
            'module' => $module,
            'tab' => $tab,
            'keyword' => $keyword,
            'items' => $items,
            'trash' => $trash,
            'maxKb' => FileService::MAX_KB,
            'allowedExt' => FileService::ALLOWED_EXT,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $module = $request->session()->get('active_module', 'core');

        $request->validate([
            'files' => ['required', 'array', 'max:5'],
            'files.*' => ['file'],
            'visibility' => ['nullable', 'in:module,private'],
            'category' => ['nullable', 'string', 'max:32'],
        ]);

        $saved = 0;
        foreach ($request->file('files') as $upload) {
            try {
                $this->files->store($upload, $user, $module, [
                    'visibility' => $request->input('visibility', File::VISIBILITY_MODULE),
                    'category' => $request->input('category', 'attachment'),
                ]);
                $saved++;
            } catch (RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        return back()->with('success', "已上传 {$saved} 个文件");
    }

    public function newVersion(Request $request, File $file)
    {
        $request->validate(['file' => ['required', 'file']]);

        try {
            $this->files->newVersion($file, $request->file('file'), $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', '已上传新版本：'.$file->fresh()->original_name.'（v'.$file->fresh()->version.'）');
    }

    public function download(Request $request, File $file)
    {
        $this->authorizeAccess($request, $file);

        return $this->respondWith($file, true);
    }

    public function preview(Request $request, File $file)
    {
        $this->authorizeAccess($request, $file);

        return $this->respondWith($file, false);
    }

    /**
     * OnlyOffice 服务器取文件：一次性签名 URL，不依赖登录态。
     */
    public function raw(Request $request, File $file)
    {
        abort_unless($request->hasValidSignature(), 403, '链接已过期');
        abort_unless($request->query('key') === $this->onlyoffice->keyFor($file), 403, 'key 不匹配');
        abort_unless(Storage::disk($file->disk)->exists($file->path), 404);

        return response()->file(Storage::disk($file->disk)->path($file->path), [
            'Content-Type' => $file->mime_type,
        ]);
    }

    public function office(Request $request, File $file)
    {
        $this->authorizeAccess($request, $file);

        $config = $this->onlyoffice->enabled()
            ? $this->onlyoffice->configFor($file, $request->user()->id, $request->user()->displayName())
            : null;

        return view('core::files.office', [
            'file' => $file,
            'config' => $config,
            'serverUrl' => rtrim((string) config('services.onlyoffice.url', ''), '/'),
        ]);
    }

    /**
     * OnlyOffice 保存回调（无登录态，靠 document key 校验）。
     */
    public function callback(Request $request, File $file)
    {
        [$ok, $message] = $this->onlyoffice->handleCallback($file, $request->json()->all());

        return response()->json(['error' => $ok ? 0 : 1, 'message' => $message]);
    }

    public function destroy(Request $request, File $file)
    {
        $this->authorizeAccess($request, $file);
        $this->files->moveToTrash($file, $request->user());

        return back()->with('success', '已移入回收站：'.$file->original_name);
    }

    public function restore(Request $request, int $id)
    {
        try {
            $file = $this->files->restore($id, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', '已恢复：'.$file->original_name);
    }

    public function purge(Request $request, int $id)
    {
        try {
            $this->files->purge($id, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', '已彻底删除（物理文件同步清理）');
    }

    private function authorizeAccess(Request $request, File $file): void
    {
        $user = $request->user();
        abort_unless($user, 403);

        $ok = $file->visibility === File::VISIBILITY_MODULE
            || $file->uploader_id === $user->id
            || $file->auditor_id === $user->id
            || $user->is_admin;

        abort_unless($ok, 403, '这个文件对你不可见');
    }

    private function respondWith(File $file, bool $attachment)
    {
        abort_unless(Storage::disk($file->disk)->exists($file->path), 404, '文件实体已丢失');

        $absolute = Storage::disk($file->disk)->path($file->path);
        $name = $file->original_name ?: basename($file->path);

        return response()->file($absolute, [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => ($attachment ? 'attachment' : 'inline').'; filename*=UTF-8\'\''.rawurlencode($name),
        ]);
    }
}
