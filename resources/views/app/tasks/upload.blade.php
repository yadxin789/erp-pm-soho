@extends('layouts.business')

@section('title', $mode === 'update' ? '更新文件' : '上传交付文件')

@section('content')
    @php($isUpdate = ($mode ?? 'upload') === 'update')

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title">
            <i class="fa-solid fa-{{ $isUpdate ? 'arrows-rotate' : 'upload' }}"></i>
            {{ $isUpdate ? '更新文件（生成新版本）' : '上传交付文件' }}
        </h2>
        <a href="{{ erp_route('app.tasks.show', $task) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回任务
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 small">
                <div class="col-12 col-md-4">
                    <div class="text-muted">任务</div>
                    <div class="fw-semibold">{{ $task->name }}</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="text-muted">所属项目</div>
                    <div class="fw-semibold">{{ $task->project?->name }}</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="text-muted">执行人</div>
                    <div class="fw-semibold">{{ $task->executor?->name ?: '—' }}</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="text-muted">审核人</div>
                    <div class="fw-semibold">{{ $task->auditor?->name ?: '—' }}</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="text-muted">任务状态</div>
                    <div class="fw-semibold">{{ $task->status_label }}</div>
                </div>
            </div>

            @if($isUpdate && ! empty($approvedFile))
                <div class="alert alert-warning mt-3 mb-0 small">
                    当前已通过版本：v{{ $approvedFile->version }} · {{ $approvedFile->original_name }}
                    （{{ $approvedFile->created_at?->format('Y-m-d H:i') }}）。
                    上传新版本后将重新进入「待审核」，原版本保留为历史。
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ $isUpdate ? erp_route('app.tasks.update-file.store', $task) : erp_route('app.tasks.upload.store', $task) }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="file">选择文件 <span class="text-danger">*</span></label>
                        <input type="file" id="file" name="file" class="form-control" required>
                        <div class="form-text">单个文件不超过 20MB。</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="remark">提交说明</label>
                        <textarea id="remark" name="remark" rows="3" class="form-control"
                                  placeholder="如：已完成结构设计，附图纸与 BOM">{{ old('remark') }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane me-1"></i>提交审核
                    </button>
                    <a href="{{ erp_route('app.tasks.show', $task) }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
