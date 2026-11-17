@extends('layouts.business')

@section('title', '部门资料')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-solid fa-building me-1"></i>部门资料</h2>
        <div class="d-flex gap-2">
            <a href="{{ erp_route('app.files.upload-department') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-upload me-1"></i>上传资料</a>
            <a href="{{ erp_route('app.support.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>资料</th><th>部门</th><th>版本</th><th>大小</th><th>上传人</th><th class="text-end">操作</th></tr></thead>
                <tbody>
                @forelse($files as $row)
                    <tr>
                        <td class="small">{{ $row->original_name }}</td>
                        <td class="small">{{ $row->department->name ?? '—' }}</td>
                        <td class="small">v{{ $row->version }}</td>
                        <td class="small">{{ $row->size_label }}</td>
                        <td class="small">{{ $row->uploader->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ erp_route('app.files.download-department', $row->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download"></i></a>
                            <form method="POST" action="{{ erp_route('app.files.update-department', $row->id) }}" enctype="multipart/form-data" class="d-inline-flex gap-1">
                                @csrf
                                <input type="file" name="file" class="form-control form-control-sm" style="width:170px;" required>
                                <button class="btn btn-outline-secondary btn-sm">更新版本</button>
                            </form>
                            <form method="POST" action="{{ erp_route('app.files.delete-department', $row->id) }}" class="d-inline" onsubmit="return confirm('删除该资料？')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">暂无部门资料</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $files->links() }}</div>
    </div>
@endsection
