@extends('layouts.business')

@section('title', '项目模板')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-layer-group"></i>项目模板</h2>
            <div class="small text-muted">把常用项目拆解固化成模板，新建项目时可一键套用任务清单</div>
        </div>
        <a href="{{ erp_route('app.project-templates.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>新建模板</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>模板名称</th><th>说明</th><th>任务数</th><th>创建人</th><th class="text-end">操作</th></tr></thead>
                <tbody>
                @forelse($templates as $template)
                    <tr>
                        <td class="fw-semibold">{{ $template->name }}</td>
                        <td class="small text-muted">{{ $template->description ?: '—' }}</td>
                        <td class="small">{{ count($template->tasks) }}</td>
                        <td class="small">{{ $template->creator->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ erp_route('app.project-templates.edit', $template) }}" class="btn btn-outline-secondary btn-sm">编辑</a>
                            <form method="POST" action="{{ erp_route('app.project-templates.destroy', $template) }}" class="d-inline" onsubmit="return confirm('删除该模板？')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">删除</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">暂无模板</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $templates->links() }}</div>
    </div>
@endsection
