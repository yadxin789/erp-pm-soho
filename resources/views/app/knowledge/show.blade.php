@extends('layouts.business')

@section('title', $space->title)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-book-open"></i>{{ $space->title }}</h2>
            <div class="small text-muted">{{ $space->description ?: '暂无说明' }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ erp_route('app.knowledge.edit', $space) }}" class="btn btn-outline-secondary btn-sm">编辑知识库</a>
            <a href="{{ erp_route('app.knowledge.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="fa-solid fa-plus me-1"></i>新增文档</div>
        <div class="card-body">
            <form method="POST" action="{{ erp_route('app.knowledge.doc.store', $space) }}">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="title" class="form-control" placeholder="文档标题" required>
                    </div>
                    <div class="col-md-7">
                        <textarea name="content" class="form-control" rows="2" placeholder="正文内容"></textarea>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button class="btn btn-primary"><i class="fa-solid fa-check"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @foreach($space->docs as $doc)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-regular fa-file-lines me-1"></i>{{ $doc->title }}
                    <span class="badge bg-secondary-subtle text-secondary-emphasis">v{{ $doc->version }}</span>
                </span>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#doc-{{ $doc->id }}">
                        编辑
                    </button>
                    <form method="POST" action="{{ erp_route('app.knowledge.doc.destroy', [$space, $doc]) }}" onsubmit="return confirm('删除该文档？')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">删除</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div style="white-space:pre-wrap;">{{ $doc->content }}</div>
                <div class="small text-muted mt-2">{{ $doc->creator->name ?? '—' }} · {{ optional($doc->updated_at)->format('Y-m-d H:i') }}</div>

                <div class="collapse mt-3" id="doc-{{ $doc->id }}">
                    <form method="POST" action="{{ erp_route('app.knowledge.doc.update', [$space, $doc]) }}">
                        @csrf @method('PUT')
                        <input type="text" name="title" class="form-control mb-2" value="{{ $doc->title }}" required>
                        <textarea name="content" class="form-control mb-2" rows="4">{{ $doc->content }}</textarea>
                        <button class="btn btn-primary btn-sm">保存为新版本</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
