@extends('layouts.business')

@section('title', '知识库')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-solid fa-book-open"></i>知识库</h2>
        <a href="{{ erp_route('app.knowledge.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>新建知识库</a>
    </div>

    <div class="row g-3">
        @forelse($spaces as $space)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="{{ erp_route('app.knowledge.show', $space) }}" class="text-decoration-none">{{ $space->title }}</a>
                        </h5>
                        <p class="small text-muted mb-2">{{ $space->description ?: '暂无说明' }}</p>
                        <div class="small text-muted">文档 {{ $space->docs_count }} 篇 · {{ $space->creator->name ?? '—' }}</div>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <a href="{{ erp_route('app.knowledge.show', $space) }}" class="btn btn-outline-primary btn-sm">打开</a>
                        <a href="{{ erp_route('app.knowledge.edit', $space) }}" class="btn btn-outline-secondary btn-sm">编辑</a>
                        <form method="POST" action="{{ erp_route('app.knowledge.destroy', $space) }}" onsubmit="return confirm('删除该知识库？')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">删除</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">还没有知识库</div>
        @endforelse
    </div>

    <div class="mt-3">{{ $spaces->links() }}</div>
@endsection
