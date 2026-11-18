@extends('soho::layouts.app')

@section('title', '新建调价任务')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('price.price-change-tasks.index') }}">调价任务</a></li>
        <li class="breadcrumb-item active">新建任务</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-plus-circle me-2"></i>新建调价任务</h4>
    <a href="{{ route('price.price-change-tasks.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i>返回列表
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('price.price-change-tasks.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">关联申请</label>
                        <select name="request_id" class="form-select" id="requestSelect">
                            <option value="">不关联申请</option>
                            @foreach($pendingRequests ?? [] as $request)
                                <option value="{{ $request->id }}" {{ old('request_id') == $request->id ? 'selected' : '' }}>
                                    #{{ $request->id }} - {{ $request->product->name ?? '' }} (¥{{ number_format($request->requested_price ?? 0, 2) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">可选，关联已通过审批的调价申请</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">任务类型 <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="">请选择类型</option>
                            @foreach($typeLabels ?? [] as $value => $label)
                                <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">目标类型 <span class="text-danger">*</span></label>
                            <select name="target_type" class="form-select" required>
                                <option value="product" {{ old('target_type') == 'product' ? 'selected' : '' }}>商品</option>
                                <option value="sku" {{ old('target_type') == 'sku' ? 'selected' : '' }}>SKU</option>
                                <option value="category" {{ old('target_type') == 'category' ? 'selected' : '' }}>分类</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">目标 ID <span class="text-danger">*</span></label>
                            <input type="number" name="target_id" class="form-control" value="{{ old('target_id') }}" required placeholder="商品/分类ID">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">目标价格 (¥) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="target_price" class="form-control" value="{{ old('target_price') }}" required min="0" placeholder="如：99.90">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">计划执行时间</label>
                        <input type="datetime-local" name="planned_at" class="form-control" value="{{ old('planned_at') }}">
                        <div class="form-text text-muted">留空则立即执行</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">执行人</label>
                        <select name="executor_id" class="form-select">
                            <option value="">自动分配</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ old('executor_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="任务备注信息">{{ old('remark') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('price.price-change-tasks.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-1"></i>取消
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i>创建任务
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection