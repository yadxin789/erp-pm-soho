@extends('layouts.business')

@section('title', $mode === 'create' ? '新建知识库' : '编辑知识库')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-solid fa-book"></i>{{ $mode === 'create' ? '新建知识库' : '编辑知识库' }}</h2>
        <a href="{{ erp_route('app.knowledge.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $mode === 'create' ? erp_route('app.knowledge.store') : erp_route('app.knowledge.update', $space) }}">
                @csrf
                @if($mode === 'edit') @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">知识库名称 <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $space->title) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">所属部门（可选）</label>
                        <select name="department_id" class="form-select">
                            <option value="">— 不限 —</option>
                            @foreach(\App\Core\Models\Department::orderBy('name')->get(['id','name']) as $department)
                                <option value="{{ $department->id }}" {{ old('department_id', $space->department_id) == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">说明</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $space->description) }}</textarea>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>保存</button>
                    <a href="{{ erp_route('app.knowledge.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
