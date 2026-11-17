<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\DepartmentFile;
use App\Erp\Models\Project\Project;
use App\Erp\Models\Project\ResourceFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 部门支援（对应参考目录 support*）
 * 文员/支援岗位：部门资料、公共资源文件、给项目参与人发通知
 */
class SupportController extends Controller
{
    public function index()
    {
        $departments = DepartmentFile::with('department:id,name')->orderByDesc('id')->limit(20)->get();
        $resources = ResourceFile::orderByDesc('id')->limit(20)->get();

        return view('app.support.index', [
            'departments' => $departments,
            'resources' => $resources,
            'stats' => [
                'department_files' => DepartmentFile::count(),
                'resource_files' => ResourceFile::count(),
            ],
        ]);
    }

    public function departmentFiles()
    {
        $files = DepartmentFile::with(['department:id,name', 'uploader:id,name'])->orderByDesc('id')->paginate(20);

        return view('app.support.department-files', compact('files'));
    }

    public function sendNotificationForm()
    {
        $projects = Project::query()->orderByDesc('id')->limit(50)->get(['id', 'name']);
        $users = \App\Core\Models\User::query()->orderBy('id')->get(['id', 'name']);

        return view('app.support.send-notification', compact('projects', 'users'));
    }

    /** 站内通知：写入目标用户待办（type=notice），断网可用 */
    public function sendNotification(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'nullable|string|max:500',
            'targets' => 'required|array|min:1',
            'targets.*' => 'integer',
            'project_id' => 'nullable|integer',
        ], [
            'title.required' => '请填写通知标题',
            'targets.required' => '请选择接收人',
        ]);

        $now = now();
        $rows = [];
        foreach ($data['targets'] as $userId) {
            $rows[] = [
                'user_id' => $userId,
                'project_id' => $data['project_id'] ?? null,
                'type' => 'notice',
                'ref_id' => null,
                'title' => '通知：' . $data['title'] . ($data['body'] ? '（' . mb_substr($data['body'], 0, 80) . '）' : ''),
                'due_date' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('erp_project_todos')->insert($rows);

        return back()->with('success', '通知已发送给 ' . count($rows) . ' 人，可在“待办”中查看');
    }

    public function uploadResourceForm()
    {
        return view('app.support.upload-resource-file');
    }

    public function storeResourceFile(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'category' => 'nullable|string|max:60',
            'file' => 'required|file|max:51200',
        ], ['title.required' => '请填写资料名称', 'file.required' => '请选择文件']);

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

        return redirect()->route('app.support.index')->with('success', '资源文件已上传');
    }
}
