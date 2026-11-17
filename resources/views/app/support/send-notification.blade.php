@extends('layouts.business')

@section('title', '发送通知')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-solid fa-bell"></i>发送通知</h2>
        <a href="{{ erp_route('app.support.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ erp_route('app.support.notification.post') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">标题 <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">关联项目（可选）</label>
                        <select name="project_id" class="form-select">
                            <option value="">— 不关联 —</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">内容</label>
                        <textarea name="body" class="form-control" rows="3">{{ old('body') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">接收人 <span class="text-danger">*</span></label>
                        <div class="border rounded p-2" style="max-height:220px;overflow-y:auto;">
                            @foreach($users as $user)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="targets[]" value="{{ $user->id }}" id="u{{ $user->id }}">
                                    <label class="form-check-label small" for="u{{ $user->id }}">{{ $user->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>发送</button>
                    <a href="{{ erp_route('app.support.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
