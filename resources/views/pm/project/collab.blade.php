@extends('pm::layouts.app')

@section('title', '协同办公 · '.$project->name)

@section('content')
@include('pm::project.partials.tabs', ['project' => $project, 'activeTab' => 'collab'])

<div class="pm-collab-title">
    <div>
        <h2><i class="fas fa-users" aria-hidden="true"></i>协同办公</h2>
        <div class="pm-collab-sub">项目：{{ $project->name }}（{{ $project->code }}）· 项目组成员可共同编辑文档</div>
    </div>
    <button type="button" class="pm-btn-primary" data-bs-toggle="modal" data-bs-target="#newDocModal">
        <i class="fas fa-plus" aria-hidden="true"></i> 新建文档
    </button>
</div>

@if ($documents->isEmpty())
    <div class="pm-panel"><div class="pm-empty">
        <i class="fas fa-file-alt" aria-hidden="true"></i>
        该项目还没有协作文档，点击右上角“新建文档”开始协同编辑。
    </div></div>
@else
    <div class="pm-panel">
        <div class="pm-panel-head"><h3><i class="fas fa-file-alt" aria-hidden="true"></i>协作文档（{{ $documents->count() }}）</h3></div>
        <div class="pm-panel-body p-0">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light"><tr><th>文档名</th><th>类型</th><th>版本</th><th>创建人</th><th>更新时间</th><th></th></tr></thead>
                <tbody>
                @foreach ($documents as $doc)
                    <tr>
                        <td>{{ $doc->name }}</td>
                        <td>{{ strtoupper($doc->extension) }}</td>
                        <td>v{{ $doc->version }}</td>
                        <td>{{ $doc->creator?->displayName() ?: '—' }}</td>
                        <td>{{ $doc->updated_at?->format('Y-m-d H:i') }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('pm.project.collab.open', [$project, $doc]) }}">打开</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="modal fade" id="newDocModal" tabindex="-1" aria-labelledby="newDocLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('pm.project.collab.store', $project) }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="newDocLabel">新建协作文档</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="doc-name">文档名称</label>
                    <input class="form-control" id="doc-name" name="name" required maxlength="120" placeholder="例如：结构设计评审记录">
                </div>
                <div class="mb-0">
                    <label class="form-label" for="doc-ext">类型</label>
                    <select class="form-select" id="doc-ext" name="extension">
                        <option value="docx">Word 文档（.docx）</option>
                        <option value="xlsx">Excel 表格（.xlsx）</option>
                        <option value="pptx">演示文稿（.pptx）</option>
                    </select>
                </div>
                @unless ($officeReady)
                    <p class="text-muted small mb-0 mt-2">当前未配置 OnlyOffice 文档服务，文档记录可创建，在线编辑需配置 `ONLYOFFICE_URL` 后生效。</p>
                @endunless
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary">创建</button>
            </div>
        </form>
    </div>
</div>
@endsection
