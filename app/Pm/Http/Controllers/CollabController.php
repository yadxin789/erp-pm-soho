<?php

namespace App\Pm\Http\Controllers;

use App\Core\Services\OnlyOfficeService;
use App\Pm\Models\CollabDocument;
use App\Pm\Models\Project;
use App\Pm\Models\ProjectLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

/**
 * 项目协同办公（对齐原 PM CollabDocumentController）
 * 项目成员可在项目内新建协作文档；文档走 OnlyOffice，未配置时给出明确提示。
 */
class CollabController extends Controller
{
    public function __construct(private readonly OnlyOfficeService $office)
    {
    }

    public function index(Request $request, Project $project)
    {
        $documents = $project->documents()->with('creator')->orderByDesc('updated_at')->get();

        return view('pm::project.collab', [
            'project'     => $project,
            'documents'   => $documents,
            'participants' => $project->participants(),
            'officeReady' => $this->office->enabled(),
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'extension' => ['nullable', 'in:docx,xlsx,pptx'],
        ]);

        $doc = CollabDocument::create([
            'project_id'   => $project->id,
            'name'         => $data['name'],
            'extension'    => $data['extension'] ?? 'docx',
            'doc_key'      => Str::uuid()->toString(),
            'version'      => 1,
            'created_by'   => $request->user()->id,
            'updated_by'   => $request->user()->id,
            'is_temp_demo' => false,
        ]);

        ProjectLog::create([
            'project_id' => $project->id,
            'user_id'    => $request->user()->id,
            'action'     => 'collab.create',
            'content'    => '新建协作文档：'.$doc->name,
        ]);

        return redirect()->route('pm.project.collab.index', $project)
            ->with('status', '协作文档已创建：'.$doc->name);
    }

    public function open(Request $request, Project $project, CollabDocument $doc)
    {
        abort_unless($doc->project_id === $project->id, 404);

        return view('pm::project.collab-open', [
            'project'     => $project,
            'doc'         => $doc,
            'officeReady' => $this->office->enabled(),
            'editorUrl'   => null,
        ]);
    }
}
