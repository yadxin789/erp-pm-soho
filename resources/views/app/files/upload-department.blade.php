@extends('layouts.business')

@section('title', '上传部门资料')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-solid fa-building me-1"></i>上传部门资料</h2>
        <a href="{{ erp_route('app.files.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ erp_route('app.files.store-department') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">所属部门 <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select" required>
                            <option value="">— 选择部门 —</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">文件 <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i>上传</button>
                    <a href="{{ erp_route('app.files.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
