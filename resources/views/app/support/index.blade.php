@extends('layouts.business')

@section('title', '部门支援')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-hands-holding-circle"></i>部门支援</h2>
            <div class="small text-muted">部门资料、公共资源与项目通知</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ erp_route('app.support.department-files') }}" class="btn btn-outline-secondary btn-sm">部门资料</a>
            <a href="{{ erp_route('app.support.send-notification') }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-bell me-1"></i>发送通知</a>
            <a href="{{ erp_route('app.support.upload-resource-file') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-upload me-1"></i>上传资源文件</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">部门资料</div>
                    <div class="fs-3 fw-semibold">{{ $stats['department_files'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">公共资源文件</div>
                    <div class="fs-3 fw-semibold">{{ $stats['resource_files'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">最近部门资料</div>
                <div class="list-group list-group-flush">
                    @forelse($departments as $row)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="small">{{ $row->original_name }}<br><span class="text-muted">{{ $row->department->name ?? '—' }}</span></span>
                            <a href="{{ erp_route('app.files.download-department', $row->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download"></i></a>
                        </div>
                    @empty
                        <div class="list-group-item small text-muted">暂无</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">最近资源文件</div>
                <div class="list-group list-group-flush">
                    @forelse($resources as $row)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="small">{{ $row->title }}<br><span class="text-muted">{{ $row->category ?: '通用' }}</span></span>
                            <a href="{{ erp_route('app.files.download-resource', $row->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-download"></i></a>
                        </div>
                    @empty
                        <div class="list-group-item small text-muted">暂无</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
