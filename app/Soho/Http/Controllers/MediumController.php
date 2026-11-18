<?php

namespace App\Soho\Http\Controllers;

use App\Soho\Models\Medium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediumController extends Controller
{
    /**
     * 媒体文件列表
     */
    public function index(Request $request)
    {
        $mediaItems = Medium::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('file_name', 'like', "%{$request->search}%");
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('file_type', $request->type);
            })
            ->orderBy('sort')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // 获取各类型的文件数量统计
        $typeStats = [
            'total' => Medium::count(),
            'image' => Medium::where('file_type', 'image')->count(),
            'video' => Medium::where('file_type', 'video')->count(),
            'document' => Medium::where('file_type', 'document')->count(),
            'audio' => Medium::where('file_type', 'audio')->count(),
            'other' => Medium::whereNotIn('file_type', ['image', 'video', 'document', 'audio'])->count(),
        ];

        // 存储统计（模拟数据）
        $storage = [
            'used' => round(Medium::sum('file_size') / 1048576, 2),
            'total' => 5, // 5 GB 总容量
        ];
        $storage['percent'] = $storage['total'] > 0 ? round(($storage['used'] / $storage['total']) * 100, 1) : 0;

        return view('media.index', compact('mediaItems', 'typeStats', 'storage'));
    }

    /**
     * 上传媒体文件
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,bmp,webp,svg,mp4,avi,mov,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:5120',
            'directory' => 'nullable|string|max:100',
        ]);

        $file = $request->file('file');
        $directory = $request->input('directory', 'media');
        $tenantId = auth()->user()->tenant_id ?? 1;

        // 生成唯一文件名
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid()->toString() . '.' . $extension;

        // 根据 MIME 类型判断文件类型
        $mimeType = $file->getClientMimeType();
        $fileType = $this->determineFileType($mimeType, $extension);

        // 存储文件到 public disk
        $storagePath = "tenant_{$tenantId}/{$directory}";
        $filePath = $file->storeAs($storagePath, $fileName, 'public');

        // 获取文件大小
        $fileSize = $file->getSize();

        // 为图片类型生成缩略图路径
        $thumbnailPath = null;
        if ($fileType === 'image') {
            $thumbnailPath = str_replace($storagePath, $storagePath . '/thumbnails', $filePath);
        }

        // 保存媒体记录
        $medium = Medium::create([
            'tenant_id' => $tenantId,
            'file_name' => $originalName,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'thumbnail_path' => $thumbnailPath,
            'sort' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => '文件上传成功',
            'data' => [
                'id' => $medium->id,
                'file_name' => $medium->file_name,
                'file_path' => Storage::disk('public')->url($medium->file_path),
                'file_type' => $medium->file_type,
                'file_size' => $medium->file_size,
                'mime_type' => $medium->mime_type,
            ],
        ]);
    }

    /**
     * 下载媒体文件
     */
    public function download(Medium $medium)
    {
        if (!Storage::disk('public')->exists($medium->file_path)) {
            return back()->with('error', '文件不存在');
        }
        return Storage::disk('public')->download($medium->file_path, $medium->file_name);
    }

    /**
     * 删除媒体文件
     */
    public function destroy(Medium $medium)
    {
        if ($medium->tenant_id !== (auth()->user()->tenant_id ?? 1)) {
            abort(403, '无权操作此文件');
        }

        // 删除存储中的文件
        if (Storage::disk('public')->exists($medium->file_path)) {
            Storage::disk('public')->delete($medium->file_path);
        }

        // 删除缩略图
        if ($medium->thumbnail_path && Storage::disk('public')->exists($medium->thumbnail_path)) {
            Storage::disk('public')->delete($medium->thumbnail_path);
        }

        $medium->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => '文件删除成功']);
        }

        return back()->with('success', '文件删除成功');
    }

    /**
     * 根据 MIME 类型和扩展名判断文件类型
     */
    protected function determineFileType(string $mimeType, string $extension): string
    {
        $extension = strtolower($extension);

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];

        if (in_array($extension, $imageExtensions) || str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (in_array($extension, $videoExtensions) || str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (in_array($extension, $documentExtensions)) {
            return 'document';
        }

        return 'other';
    }
}
