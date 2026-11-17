@extends('layouts.business')

@section('title', '编辑项目')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-pen"></i>编辑项目</h2>
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
        <div class="card-body">
            <form method="POST" action="{{ erp_route('app.projects.update', $project) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="name">项目名称 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required value="{{ old('name', $project->name) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="status">状态</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $project->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="budget">项目预算</label>
                        <input type="number" step="0.01" id="budget" name="budget" class="form-control"
                               value="{{ old('budget', $project->budget) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="product_name">产品名称</label>
                        <input type="text" id="product_name" name="product_name" class="form-control"
                               value="{{ old('product_name', $project->product_name) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="product_model">产品型号</label>
                        <input type="text" id="product_model" name="product_model" class="form-control"
                               value="{{ old('product_model', $project->product_model) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="version">版本号</label>
                        <input type="text" id="version" name="version" class="form-control"
                               value="{{ old('version', $project->version) }}">
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="start_date">开始日期</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                               value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="end_date">结束日期</label>
                        <input type="date" id="end_date" name="end_date" class="form-control"
                               value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="customer_id">关联客户</label>
                        <select id="customer_id" name="customer_id" class="form-select">
                            <option value="">（无）</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $project->customer_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="manager_id">项目负责人</label>
                        <select id="manager_id" name="manager_id" class="form-select">
                            <option value="">（未指定）</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('manager_id', $project->manager_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="description">项目说明</label>
                        <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $project->description) }}</textarea>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0 small">
                    直接改状态只用于数据修正；正常业务流程请用「暂停 / 恢复 / 变更」操作，它们会写入项目履历。
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ erp_route('app.projects.show', $project) }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
