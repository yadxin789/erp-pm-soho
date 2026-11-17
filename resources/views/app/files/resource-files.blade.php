@extends('layouts.business')

@section('title', '资源文件')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-solid fa-box-archive"></i>资源文件</h2>
        <div class="d-flex gap-2">
            <a href="{{ erp_route('app.files.manage-resource-files') }}" class="btn btn-outline-secondary btn-sm">资源管理</a>
            <a href="{{ erp_route('app.files.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>文件中心</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>资料名称</th><th>分类</th><th>文件</th><th>大小</th><th>下载次数</th><th>上传人</th><th class="text-end">操作</th></tr></thead>
                <tbody>
                @forelse($files as $row)
                    <tr>
                        <td>{{ $row->title }}</td>
                        <td class="small">{{ $row->category ?: '通用' }}</td>
                        <td class="small">{{ $row->original_name }}</td>
                        <td class="small">{{ $row->size_label }}</td>
                        <td class="small">{{ $row->downloads }}</td>
                        <td class="small">{{ $row->uploader->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ erp_route('app.files.download-resource', $row->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">暂无资源文件</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $files->links() }}</div>
    </div>
@endsection
