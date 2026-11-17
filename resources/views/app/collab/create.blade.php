@extends('layouts.business')

@section('title', $project->name . ' · 新建协作文档')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-solid fa-file-circle-plus"></i>新建协作文档</h2>
        <a href="{{ erp_route('app.projects.collab.index', $project) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ erp_route('app.projects.collab.store', $project) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">文档名称 <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" maxlength="120" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">文档类型</label>
                        <select name="ext" class="form-select">
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('ext') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            文档创建后可直接在线编辑，多人可同时打开同一文档；每次保存都会记录保存人与时间。
                            全部数据保存在本机，断网也能使用。
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>创建并打开</button>
                    <a href="{{ erp_route('app.projects.collab.index', $project) }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
