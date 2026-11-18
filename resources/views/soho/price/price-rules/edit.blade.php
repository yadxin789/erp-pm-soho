@extends('soho::layouts.app')

@section('title', '编辑价格规则')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.price.rules.index') }}">价格规则</a></li>
        <li class="breadcrumb-item active">编辑规则</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-edit me-2"></i>编辑价格规则</h4>
    <a href="{{ route('soho.price.rules.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i>返回列表
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('soho.price.rules.update', $rule) }}">
                    @csrf @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">规则名称 <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">规则编码</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code', $rule->code) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">规则类型 <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" id="ruleType" required>
                            <option value="">请选择类型</option>
                            @foreach($typeLabels ?? [] as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $rule->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">规则描述</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $rule->description) }}</textarea>
                    </div>

                    {{-- 动态参数表单 --}}
                    <div class="card mb-3">
                        <div class="card-header"><h6 class="mb-0"><i class="fa fa-sliders me-2"></i>规则参数</h6></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">加价比例 (%)</label>
                                    <input type="number" step="0.01" name="markup_percent" class="form-control" value="{{ old('markup_percent', $rule->markup_percent) }}">
                                    <div class="form-text text-muted">按成本价的百分比加价</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">固定加价金额</label>
                                    <input type="number" step="0.01" name="markup_amount" class="form-control" value="{{ old('markup_amount', $rule->markup_amount) }}">
                                    <div class="form-text text-muted">在加价比例基础上再加固定金额</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">目标利润率 (%)</label>
                                    <input type="number" step="0.01" name="target_profit_rate" class="form-control" value="{{ old('target_profit_rate', $rule->target_profit_rate) }}">
                                    <div class="form-text text-muted">根据目标利润率反推售价</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">最低售价</label>
                                    <input type="number" step="0.01" name="min_sale_price" class="form-control" value="{{ old('min_sale_price', $rule->min_sale_price) }}">
                                    <div class="form-text text-muted">规则计算出的价格不低于此值</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">最低价格范围</label>
                            <input type="number" step="0.01" name="min_price" class="form-control" value="{{ old('min_price', $rule->min_price) }}" min="0">
                            <div class="form-text text-muted">规则适用的最低价格</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">最高价格范围</label>
                            <input type="number" step="0.01" name="max_price" class="form-control" value="{{ old('max_price', $rule->max_price) }}" min="0">
                            <div class="form-text text-muted">规则适用的最高价格</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">优先级</label>
                            <input type="number" name="priority" class="form-control" value="{{ old('priority', $rule->priority ?? 0) }}" min="0">
                            <div class="form-text text-muted">数值越大优先级越高</div>
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="statusEnabled" name="status" value="enabled" {{ old('status', $rule->status) == 'enabled' ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusEnabled">启用此规则</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('soho.price.rules.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-1"></i>取消
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i>保存修改
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection