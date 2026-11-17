<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\ProjectTemplate;
use Illuminate\Http\Request;

/**
 * 项目模板（对应参考目录 project-template*）
 * 模板保存一套任务清单配置，新建项目时可一键套用。
 */
class ProjectTemplateController extends Controller
{
    public function index()
    {
        $templates = ProjectTemplate::with('creator:id,name')->orderByDesc('id')->paginate(15);

        return view('app.project-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('app.project-templates.form', ['template' => new ProjectTemplate(), 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        ProjectTemplate::create($data + ['created_by' => $request->user()->id]);

        return redirect()->route('app.project-templates.index')->with('success', '模板已创建');
    }

    public function edit(ProjectTemplate $template)
    {
        return view('app.project-templates.form', ['template' => $template, 'mode' => 'edit']);
    }

    public function update(Request $request, ProjectTemplate $template)
    {
        $template->update($this->validated($request));

        return redirect()->route('app.project-templates.index')->with('success', '模板已更新');
    }

    public function destroy(ProjectTemplate $template)
    {
        $template->delete();

        return redirect()->route('app.project-templates.index')->with('success', '模板已删除');
    }

    /** 供新建项目页异步取模板任务清单 */
    public function config(ProjectTemplate $template)
    {
        return response()->json(['template' => [
            'id' => $template->id,
            'name' => $template->name,
            'tasks' => $template->tasks,
        ]]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
            'tasks' => 'nullable|array',
            'tasks.*.name' => 'required_with:tasks|string|max:200',
            'tasks.*.priority' => 'nullable|integer|in:1,2,3,4',
            'tasks.*.duration_days' => 'nullable|integer|min:1|max:365',
        ], ['name.required' => '请填写模板名称']);

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'config' => ['tasks' => array_values($data['tasks'] ?? [])],
        ];
    }
}
