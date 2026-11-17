@extends('layouts.business')

@section('title', $task->name . ' · 申请延期')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="page-title mb-0"><i class="fa-solid fa-hourglass-half"></i>申请延期</h2>
            <div class="small text-muted">
                任务「{{ $task->name }}」 · 项目 {{ $task->project->name ?? '—' }} · 当前截止 {{ $task->end_date ?: '未设置' }}
            </div>
        </div>
        <a href="{{ erp_route('app.tasks.show', $task) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回任务</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ erp_route('app.tasks.store-extension', $task) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">延期天数 <span class="text-danger">*</span></label>
                        <input type="number" name="days" class="form-control" min="1" max="365" value="{{ old('days', 3) }}" required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">延期原因 <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control" value="{{ old('reason') }}" required placeholder="例如：关键元器件到货延迟">
                    </div>
                </div>
                <div class="alert alert-light border small mt-3 mb-0">
                    提交后由任务审核人（{{ $task->auditor->name ?? '未指定' }}）审批；批准后任务结束日期自动顺延，并清除逾期标记。
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-warning"><i class="fa-solid fa-paper-plane me-1"></i>提交申请</button>
                    <a href="{{ erp_route('app.tasks.show', $task) }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
