@extends('pm::layouts.app')

@section('title', '部门资料文件')

@section('content')
<h1 class="mb-4">部门资料文件</h1>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    <div class="card-body">
        @if($files->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>文件名称</th>
                        <th>分类</th>
                        <th>上传人</th>
                        <th>大小</th>
                        <th>上传时间</th>
                        <th class="text-end">操作</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($files as $file)
                    <tr>
                        <td>{{ $file->original_name }}</td>
                        <td>{{ $file->category ?? '未分类' }}</td>
                        <td>{{ $file->uploader->name ?? '—' }}</td>
                        <td>{{ $file->formatted_size }}</td>
                        <td>{{ $file->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('pm.file.download', $file) }}">下载</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $files->links() }}</div>
        @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-folder-open fa-2x mb-3 d-block"></i>
            暂无部门资料文件
        </div>
        @endif
    </div>
</div>
@endsection
