<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\CollabDocument;
use App\Erp\Models\Project\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 协同办公（对应参考目录 project/{project}/collab*）
 *
 * 双模式：
 *   1. 内置在线编辑器 —— 默认，纯本地运行，断网可用；
 *   2. OnlyOffice —— 当 config('services.onlyoffice.url') 指向可达的本地/内网文档服务时自动启用。
 */
class CollabDocumentController extends Controller
{
    private const EXTS = ['doc' => '文档', 'sheet' => '表格', 'slide' => '演示'];

    public function index(Project $project)
    {
        Gate::authorize('view', $project);

        $documents = CollabDocument::with('creator')
            ->where('project_id', $project->id)
            ->orderByDesc('updated_at')
            ->get();

        return view('app.collab.index', compact('project', 'documents'));
    }

    public function create(Project $project)
    {
        Gate::authorize('view', $project);

        return view('app.collab.create', ['project' => $project, 'types' => self::EXTS]);
    }

    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $data = $request->validate([
            'title' => 'required|string|max:120',
            'ext' => 'required|in:doc,sheet,slide',
        ], ['title.required' => '请填写文档名称']);

        $docKey = (string) Str::uuid();
        $path = "projects/{$project->id}/collab/{$docKey}.html";
        Storage::disk('local')->put($path, $this->blankContent($data['ext'], $data['title']));

        $doc = CollabDocument::create([
            'project_id' => $project->id,
            'title' => $data['title'],
            'doc_key' => $docKey,
            'file_path' => $path,
            'ext' => $data['ext'],
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'last_saved_at' => now(),
        ]);

        return redirect()->route('app.projects.collab.open', [$project, $doc->id])->with('success', '协作文档已创建');
    }

    public function open(Project $project, CollabDocument $doc)
    {
        Gate::authorize('view', $project);
        abort_unless((int) $doc->project_id === (int) $project->id, 404);

        $content = Storage::disk('local')->exists($doc->file_path)
            ? Storage::disk('local')->get($doc->file_path)
            : '';

        $onlyofficeUrl = $this->onlyOfficeUrl();

        return view('app.collab.open', compact('project', 'doc', 'content', 'onlyofficeUrl'));
    }

    /** 内置编辑器保存（轮询 / 手动保存都走这里） */
    public function save(Request $request, Project $project, CollabDocument $doc)
    {
        Gate::authorize('view', $project);
        abort_unless((int) $doc->project_id === (int) $project->id, 404);

        $data = $request->validate(['content' => 'nullable|string']);

        Storage::disk('local')->put($doc->file_path, $data['content'] ?? '');

        $doc->forceFill([
            'updated_by' => $request->user()->id,
            'last_saved_at' => now(),
        ])->save();

        return response()->json([
            'ok' => true,
            'saved_at' => $doc->last_saved_at->format('H:i:s'),
            'saved_by' => $request->user()->name,
        ]);
    }

    public function download(Project $project, CollabDocument $doc): StreamedResponse
    {
        Gate::authorize('view', $project);
        abort_unless((int) $doc->project_id === (int) $project->id, 404);

        abort_unless(Storage::disk('local')->exists($doc->file_path), 404, '文档不存在');

        return Storage::disk('local')->download($doc->file_path, $doc->title . '.html');
    }

    /** OnlyOffice / 信令服务端按 doc_key 拉取文档（无登录态） */
    public function serverDownload(string $doc): StreamedResponse
    {
        $document = CollabDocument::where('doc_key', $doc)->firstOrFail();
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->title . '.html');
    }

    /** OnlyOffice 保存回调：把编辑结果拉回本地 */
    public function callback(Request $request, string $doc)
    {
        $document = CollabDocument::where('doc_key', $doc)->firstOrFail();
        $status = (int) ($request->input('status') ?? 0);

        if (in_array($status, [2, 6], true) && ($url = $request->input('url'))) {
            try {
                $body = file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 20]]));
                if ($body !== false) {
                    Storage::disk('local')->put($document->file_path, $body);
                    $document->forceFill(['last_saved_at' => now()])->save();
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json(['error' => 0]);
    }

    public function destroy(Project $project, CollabDocument $doc)
    {
        Gate::authorize('view', $project);
        abort_unless((int) $doc->project_id === (int) $project->id, 404);

        $doc->delete();

        return redirect()->route('app.projects.collab.index', $project)->with('success', '文档已删除');
    }

    private function onlyOfficeUrl(): ?string
    {
        $url = config('services.onlyoffice.url');

        return $url ? rtrim($url, '/') : null;
    }

    private function blankContent(string $ext, string $title): string
    {
        if ($ext === 'sheet') {
            return "项目\t负责人\t计划完成\t状态\n";
        }

        if ($ext === 'slide') {
            return "# {$title}\n\n## 第 2 页\n\n";
        }

        return "<h3>{$title}</h3><p></p>";
    }
}
