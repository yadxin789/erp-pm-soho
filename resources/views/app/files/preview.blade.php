@extends('layouts.business')

@section('title', '文件预览')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-regular fa-eye"></i>{{ $file->original_name }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ erp_route('app.files.download', $file->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download me-1"></i>下载</a>
            <a href="{{ erp_route('app.files.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="small text-muted mb-2">
                所属项目 {{ $file->project->name ?? '—' }} · 任务 {{ $file->task->name ?? '—' }}
                · 版本 v{{ $file->version }} · {{ $file->size_label }}
            </div>
            @php $mime = (string) $file->mime_type; @endphp
            @if(str_starts_with($mime, 'image/'))
                <img src="{{ $inlineUrl }}" class="img-fluid rounded border" alt="{{ $file->original_name }}">
            @elseif($mime === 'application/pdf')
                <iframe src="{{ $inlineUrl }}" style="width:100%;height:640px;border:0;"></iframe>
            @else
                <iframe src="{{ $inlineUrl }}" style="width:100%;height:640px;border:1px solid #dee2e6;"></iframe>
                <p class="small text-muted mt-2 mb-0">该格式浏览器可能无法内嵌显示，可直接下载查看。</p>
            @endif
        </div>
    </div>
@endsection
