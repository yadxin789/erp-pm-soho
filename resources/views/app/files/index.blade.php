@extends('layouts.business')

@section('title', '文件中心')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="page-title mb-1"><i class="fa-solid fa-folder-tree"></i>文件中心</h2>
            <div class="small text-muted">项目交付文件 · 部门资料 · 公共资源</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ erp_route('app.files.resource-files') }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-box-archive me-1"></i>资源文件</a>
            <a href="{{ erp_route('app.files.manage-resource-files') }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-gear me-1"></i>资源管理</a>
            <a href="{{ erp_route('app.files.upload-department') }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-building me-1"></i>上传部门资料</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="fa-solid fa-file-circle-check me-1"></i>项目交付文件</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr><th>文件</th><th>所属项目</th><th>任务</th><th>版本</th><th>状态</th><th>上传人</th><th>大小</th><th class="text-end">操作</th></tr>
                </thead>
                <tbody>
                @forelse($files as $file)
                    <tr>
                        <td class="small">{{ $file->original_name }}</td>
                        <td class="small">{{ $file->project->name ?? '—' }}</td>
                        <td class="small">{{ $file->task->name ?? '—' }}</td>
                        <td class="small">v{{ $file->version }}</td>
                        <td><span class="badge bg-{{ ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$file->status] ?? 'secondary' }}-subtle text-{{ ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$file->status] ?? 'secondary' }}-emphasis">{{ $file->status_label }}</span></td>
                        <td class="small">{{ $file->uploader->name ?? '—' }}</td>
                        <td class="small">{{ $file->size_label }}</td>
                        <td class="text-end">
                            <a href="{{ erp_route('app.files.preview', $file->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fa-regular fa-eye"></i></a>
                            <a href="{{ erp_route('app.files.download', $file->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">暂无交付文件</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $files->links() }}</div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-building-columns me-1"></i>部门资料</div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>资料</th><th>部门</th><th>版本</th><th>上传人</th><th class="text-end">操作</th></tr></thead>
                <tbody>
                @forelse($departments as $row)
                    <tr>
                        <td class="small">{{ $row->original_name }}</td>
                        <td class="small">{{ $row->department->name ?? '—' }}</td>
                        <td class="small">v{{ $row->version }}</td>
                        <td class="small">{{ $row->uploader->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ erp_route('app.files.download-department', $row->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download"></i></a>
                            <form method="POST" action="{{ erp_route('app.files.delete-department', $row->id) }}" class="d-inline" onsubmit="return confirm('删除该资料？')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">暂无部门资料</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
