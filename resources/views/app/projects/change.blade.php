@extends('layouts.business')

@section('title', '项目变更')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-shuffle"></i>{{ $project->name }} · 项目变更</h2>
        <a href="{{ erp_route('app.projects.show', $project) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回详情
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>当前排期</div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="text-muted small">开始日期</div>
                    <div class="fw-semibold">{{ optional($project->start_date)->format('Y-m-d') ?: '—' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">结束日期</div>
                    <div class="fw-semibold">{{ optional($project->end_date)->format('Y-m-d') ?: '—' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">负责人</div>
                    <div class="fw-semibold">{{ $project->manager?->name ?: '—' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">累计暂停</div>
                    <div class="fw-semibold">{{ $project->total_paused_days }} 天</div>
                </div>
            </div>

            <form method="POST" action="{{ erp_route('app.projects.change.update', $project) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="start_date">新的开始日期</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                               value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="end_date">新的结束日期</label>
                        <input type="date" id="end_date" name="end_date" class="form-control"
                               value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="manager_id">新的负责人</label>
                        <select id="manager_id" name="manager_id" class="form-select">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('manager_id', $project->manager_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="reason">变更原因 <span class="text-danger">*</span></label>
                        <textarea id="reason" name="reason" rows="3" class="form-control" required
                                  placeholder="如：客户要求提前一版样机，交付日期顺延 10 天">{{ old('reason') }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-warning"><i class="fa-solid fa-floppy-disk me-1"></i>保存变更</button>
                    <a href="{{ erp_route('app.projects.show', $project) }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
