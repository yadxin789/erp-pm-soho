@extends('layouts.business')

@section('title', $project->name . ' · 协同办公')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="page-title mb-1"><i class="fa-solid fa-file-pen"></i>协同办公</h2>
            <div class="small text-muted">{{ $project->name }} · 项目组在线文档</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ erp_route('app.projects.collab.create', $project) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>新建文档</a>
            <a href="{{ erp_route('app.projects.messages.index', $project) }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-comments me-1"></i>聊天室</a>
            <a href="{{ erp_route('app.projects.voice.index', $project) }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-phone-volume me-1"></i>电话会议</a>
            <a href="{{ erp_route('app.projects.show', $project) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>项目详情</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>文档名称</th>
                    <th>类型</th>
                    <th>创建人</th>
                    <th>最近保存</th>
                    <th class="text-end">操作</th>
                </tr>
                </thead>
                <tbody>
                @forelse($documents as $doc)
                    <tr>
                        <td>
                            <a href="{{ erp_route('app.projects.collab.open', [$project, $doc]) }}" class="fw-semibold text-decoration-none">
                                <i class="fa-regular fa-file-lines me-1"></i>{{ $doc->title }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ ['doc' => '文档', 'sheet' => '表格', 'slide' => '演示'][$doc->ext] ?? $doc->ext }}</span>
                        </td>
                        <td class="small text-muted">{{ $doc->creator->name ?? '—' }}</td>
                        <td class="small text-muted">{{ optional($doc->last_saved_at ?: $doc->updated_at)->format('m-d H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ erp_route('app.projects.collab.open', [$project, $doc]) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-pen-to-square me-1"></i>打开</a>
                            <a href="{{ erp_route('app.projects.collab.download', [$project, $doc]) }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download"></i></a>
                            <form method="POST" action="{{ erp_route('app.projects.collab.destroy', [$project, $doc]) }}" class="d-inline" onsubmit="return confirm('删除该文档？')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">还没有协作文档，点击右上角“新建文档”开始</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
