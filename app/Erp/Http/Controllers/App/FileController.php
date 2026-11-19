<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\DepartmentFile;
use App\Erp\Models\Project\Project;
use App\Erp\Models\Project\ResourceFile;
use App\Erp\Models\Project\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 文件中心（对应参考目录 file*）
 *   项目交付文件（读数）/ 部门资料 / 公共资源文件
 */
class FileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $projectIds = Project::visibleTo($user)->pluck('id');

        $files = TaskFile::with(['task:id,name,project_id', 'project:id,name', 'uploader:id,name'])
            ->whereIn('project_id', $projectIds)
            ->orderByDesc('id')
            ->paginate(20);

        $departments = DepartmentFile::with(['department:id,name', 'uploader:id,name'])
            ->orderByDesc('id')->limit(50)->get();

        return view('app.files.index', [
            'files' => $files,
            'departments' => $departments,
            'resourceCount' => ResourceFile::count(),
        ]);
    }

    public function resourceFiles()
    {
        $files = ResourceFile::with('uploader:id,name')->orderByDesc('id')->paginate(20);

        return view('app.files.resource-files', compact('files'));
    }

    public function manageResourceFiles()
    {
        $files = ResourceFile::with('uploader:id,name')->orderByDesc('id')->paginate(20);

        return view('app.files.manage-resource-files', compact('files'));
    }

    /** 项目交付文件预览 */
    public function preview(int $file)
    {
        $row = TaskFile::with('task:id,name', 'project:id,name')->findOrFail($file);

        abort_unless(Storage::disk('local')->exists($row->stored_path), 404, '文件不存在');

        return view('app.files.preview', ['file' => $row, 'inlineUrl' => route('app.files.raw', $row->id)]);
    }

    /** 原文件内联输出（图片 / PDF 用） */
    public function raw(int $file)
    {
        $row = TaskFile::findOrFail($file);
        abort_unless(Storage::disk('local')->exists($row->stored_path), 404);

        return Storage::disk('local')->response($row->stored_path, $row->original_name);
    }

    public function download(int $file): StreamedResponse
    {
        $row = TaskFile::findOrFail($file);
        abort_unless(Storage::disk('local')->exists($row->stored_path), 404);

        return Storage::disk('local')->download($row->stored_path, $row->original_name);
    }

    public function uploadDepartmentForm()
    {
        $departments = \App\Core\Models\Department::where('module', 'erp')->orderBy('name')->get(['id', 'name']);

        return view('app.files.upload-department', compact('departments'));
    }

    public function storeDepartmentFile(Request $request)
    {
        $data = $request->validate([
            'department_id' => 'required|integer',
            'file' => 'required|file|max:51200',
        ], ['file.required' => '请选择文件']);

        $file = $request->file('file');
        DepartmentFile::create([
            'department_id' => $data['department_id'],
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $file->store('departments/' . $data['department_id'] . '/files', 'local'),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
            'status' => 'approved',
        ]);

        return redirect()->route('app.files.index')->with('success', '部门资料已上传');
    }

    public function downloadDepartmentFile(int $file): StreamedResponse
    {
        $row = DepartmentFile::findOrFail($file);
        abort_unless(Storage::disk('local')->exists($row->stored_path), 404);

        return Storage::disk('local')->download($row->stored_path, $row->original_name);
    }

    public function updateDepartmentFile(Request $request, int $file)
    {
        $row = DepartmentFile::findOrFail($file);
        $request->validate(['file' => 'required|file|max:51200']);

        $upload = $request->file('file');
        $row->forceFill([
            'original_name' => $upload->getClientOriginalName(),
            'stored_path' => $upload->store('departments/' . $row->department_id . '/files', 'local'),
            'mime_type' => $upload->getMimeType(),
            'size' => $upload->getSize(),
            'version' => $row->version + 1,
            'uploaded_by' => $request->user()->id,
        ])->save();

        return back()->with('success', '资料已更新为新版本');
    }

    public function deleteDepartmentFile(int $file)
    {
        DepartmentFile::findOrFail($file)->delete();

        return back()->with('success', '资料已删除');
    }

    public function downloadResourceFile(int $file): StreamedResponse
    {
        $row = ResourceFile::findOrFail($file);
        abort_unless(Storage::disk('local')->exists($row->stored_path), 404);

        $row->increment('downloads');

        return Storage::disk('local')->download($row->stored_path, $row->original_name);
    }

    /** 资源文件上传（管理/支援共用） */
    public function storeResource(Request $request, bool $backToManage = false)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'category' => 'nullable|string|max:60',
            'file' => 'required|file|max:51200',
        ], ['title.required' => '请填写资料名称']);

        $file = $request->file('file');
        ResourceFile::create([
            'title' => $data['title'],
            'category' => $data['category'] ?? '通用',
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $file->store('resources', 'local'),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()
            ->route($backToManage ? 'app.files.manage-resource-files' : 'app.files.resource-files')
            ->with('success', '资源文件已上传');
    }

    public function destroyResource(int $file)
    {
        ResourceFile::findOrFail($file)->delete();

        return back()->with('success', '资源文件已删除');
    }
}
