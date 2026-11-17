@extends('layouts.business')

@section('title', '资源文件管理')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-solid fa-gear"></i>资源文件管理</h2>
        <a href="{{ erp_route('app.files.resource-files') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>资源列表</a>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="fa-solid fa-upload me-1"></i>上传资源文件</div>
        <div class="card-body">
            <form method="POST" action="{{ erp_route('app.support.store-resource-file') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label small">资料名称</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">分类</label>
                    <input type="text" name="category" class="form-control" placeholder="通用">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">文件</label>
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>上传</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>资料名称</th><th>分类</th><th>文件</th><th>大小</th><th>下载</th><th class="text-end">操作</th></tr></thead>
                <tbody>
                @forelse($files as $row)
                    <tr>
                        <td>{{ $row->title }}</td>
                        <td class="small">{{ $row->category ?: '通用' }}</td>
                        <td class="small">{{ $row->original_name }}</td>
                        <td class="small">{{ $row->size_label }}</td>
                        <td class="small">{{ $row->downloads }}</td>
                        <td class="text-end">
                            <a href="{{ erp_route('app.files.download-resource', $row->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">暂无资源文件</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $files->links() }}</div>
    </div>
@endsection
