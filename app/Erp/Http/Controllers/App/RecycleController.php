<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\CollabDocument;
use App\Erp\Models\Project\DepartmentFile;
use App\Erp\Models\Project\Project;
use App\Erp\Models\Project\ProjectMessage;
use App\Erp\Models\Project\ProjectTask;
use App\Erp\Models\Project\ResourceFile;
use App\Erp\Models\Project\TaskFile;
use Illuminate\Http\Request;

/**
 * 回收站（对应参考目录 recycle，个人 + 管理员）
 */
class RecycleController extends Controller
{
    private const TYPES = [
        'projects' => [Project::class, '项目'],
        'tasks' => [ProjectTask::class, '任务'],
        'files' => [TaskFile::class, '交付文件'],
        'messages' => [ProjectMessage::class, '群聊消息'],
        'collab' => [CollabDocument::class, '协作文档'],
        'department-files' => [DepartmentFile::class, '部门资料'],
        'resources' => [ResourceFile::class, '资源文件'],
    ];

    public function index(Request $request)
    {
        $user = $request->user();

        $groups = [];
        foreach (self::TYPES as $key => [$class, $label]) {
            $query = $class::onlyTrashed();

            // 个人视角：只看自己的；管理员可见全部
            if (! $user->hasRole('admin')) {
                if ($key === 'projects') {
                    $query->where('creator_id', $user->id);
                } elseif (in_array($key, ['tasks'], true)) {
                    $query->where(function ($q) use ($user) {
                        $q->where('executor_id', $user->id)->orWhere('auditor_id', $user->id);
                    });
                } elseif (in_array($key, ['messages'], true)) {
                    $query->where('user_id', $user->id);
                }
            }

            $groups[$key] = [
                'label' => $label,
                'rows' => $query->orderByDesc('deleted_at')->limit(30)->get(),
            ];
        }

        return view('app.recycle.index', compact('groups'));
    }

    public function restore(Request $request, string $type, int $id)
    {
        $class = $this->classOf($type);

        $row = $class::onlyTrashed()->findOrFail($id);
        $this->authorizeRow($request, $type, $row);

        $row->restore();

        return back()->with('success', '已恢复到原列表');
    }

    public function forceDelete(Request $request, string $type, int $id)
    {
        $class = $this->classOf($type);

        $row = $class::onlyTrashed()->findOrFail($id);
        $this->authorizeRow($request, $type, $row);

        $row->forceDelete();

        return back()->with('success', '已彻底删除');
    }

    private function classOf(string $type): string
    {
        abort_unless(isset(self::TYPES[$type]), 404, '未知类型');

        return self::TYPES[$type][0];
    }

    private function authorizeRow(Request $request, string $type, $row): void
    {
        $user = $request->user();
        if ($user->hasRole('admin')) {
            return;
        }

        $owned = match ($type) {
            'projects' => (int) $row->creator_id === (int) $user->id,
            'tasks' => (int) $row->executor_id === (int) $user->id || (int) $row->auditor_id === (int) $user->id,
            'messages' => (int) $row->user_id === (int) $user->id,
            default => (int) ($row->uploaded_by ?? $row->created_by ?? 0) === (int) $user->id,
        };

        abort_unless($owned, 403, '只能处理自己的记录');
    }
}
