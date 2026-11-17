<?php

namespace App\Erp\Http\Controllers\App;

use App\Erp\Http\Controllers\Controller;
use App\Erp\Models\Project\KnowledgeBase;
use App\Erp\Models\Project\KnowledgeDoc;
use Illuminate\Http\Request;

/**
 * 知识库（对应参考目录 knowledge*）
 */
class KnowledgeController extends Controller
{
    public function index()
    {
        $spaces = KnowledgeBase::withCount('docs')->with('creator:id,name')->orderByDesc('updated_at')->paginate(15);

        return view('app.knowledge.index', compact('spaces'));
    }

    public function create()
    {
        return view('app.knowledge.form', ['space' => new KnowledgeBase(), 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
            'department_id' => 'nullable|integer',
        ], ['title.required' => '请填写知识库名称']);

        KnowledgeBase::create($data + ['created_by' => $request->user()->id]);

        return redirect()->route('app.knowledge.index')->with('success', '知识库已创建');
    }

    public function edit(KnowledgeBase $knowledge)
    {
        return view('app.knowledge.form', ['space' => $knowledge, 'mode' => 'edit']);
    }

    public function update(Request $request, KnowledgeBase $knowledge)
    {
        $data = $request->validate(['title' => 'required|string|max:120', 'description' => 'nullable|string|max:255']);
        $knowledge->update($data);

        return redirect()->route('app.knowledge.show', $knowledge)->with('success', '知识库已更新');
    }

    public function destroy(KnowledgeBase $knowledge)
    {
        $knowledge->delete();

        return redirect()->route('app.knowledge.index')->with('success', '知识库已删除');
    }

    public function show(KnowledgeBase $knowledge)
    {
        $knowledge->load('docs.creator:id,name');

        return view('app.knowledge.show', ['space' => $knowledge]);
    }

    public function docStore(Request $request, KnowledgeBase $knowledge)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'content' => 'nullable|string',
        ], ['title.required' => '请填写文档标题']);

        $knowledge->docs()->create($data + ['created_by' => $request->user()->id, 'version' => 1]);

        return back()->with('success', '文档已新增');
    }

    public function docUpdate(Request $request, KnowledgeBase $knowledge, KnowledgeDoc $doc)
    {
        $data = $request->validate(['title' => 'required|string|max:120', 'content' => 'nullable|string']);

        $doc->forceFill($data + ['version' => $doc->version + 1])->save();

        return back()->with('success', '文档已保存');
    }

    public function docDestroy(KnowledgeBase $knowledge, KnowledgeDoc $doc)
    {
        $doc->delete();

        return back()->with('success', '文档已删除');
    }
}
