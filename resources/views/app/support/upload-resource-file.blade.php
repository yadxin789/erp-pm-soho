@extends('layouts.business')

@section('title', '上传资源文件')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-solid fa-upload"></i>上传资源文件</h2>
        <a href="{{ erp_route('app.support.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ erp_route('app.support.store-resource-file') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">资料名称 <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">分类</label>
                        <input type="text" name="category" class="form-control" placeholder="通用 / 制度 / 模板">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">文件 <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>上传</button>
                    <a href="{{ erp_route('app.support.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
