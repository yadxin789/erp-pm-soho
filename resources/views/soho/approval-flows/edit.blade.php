@extends('soho::layouts.app')

@section('title', '编辑审批流')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.approval_flows.index') }}">审批流</a></li>
        <li class="breadcrumb-item active">编辑审批流</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header"><h5 class="mb-0">编辑审批流</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.approval_flows.update', $approvalFlow) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">审批流名称 <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $approvalFlow->name) }}" required>
                @if($errors->first('name'))
                    <div class="text-danger small mt-1">{{ $errors->first('name') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">审批类型 <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    <option value="order" @selected($approvalFlow->type == 'order')>订单审批</option>
                    <option value="refund" @selected($approvalFlow->type == 'refund')>退款审批</option>
                    <option value="stock" @selected($approvalFlow->type == 'stock')>库存审批</option>
                    <option value="purchase" @selected($approvalFlow->type == 'purchase')>采购审批</option>
                    <option value="finance" @selected($approvalFlow->type == 'finance')>财务审批</option>
                </select>
                @if($errors->first('type'))
                    <div class="text-danger small mt-1">{{ $errors->first('type') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">触发条件</label>
                <textarea name="trigger_condition" class="form-control" rows="3">{{ old('trigger_condition', $approvalFlow->trigger_condition) }}</textarea>
                @if($errors->first('trigger_condition'))
                    <div class="text-danger small mt-1">{{ $errors->first('trigger_condition') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">审批节点配置</label>
                <textarea name="nodes_config" class="form-control" rows="5">{{ old('nodes_config', $approvalFlow->nodes_config) }}</textarea>
                <small class="text-muted">格式示例：[{"role":"manager","order":1},{"role":"admin","order":2}]</small>
                @if($errors->first('nodes_config'))
                    <div class="text-danger small mt-1">{{ $errors->first('nodes_config') }}</div>
                @endif
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="status" class="form-check-input" value="1" @checked(old('status', $approvalFlow->status))>
                    <label class="form-check-label">启用</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> 保存
            </button>
            <a href="{{ route('soho.approval_flows.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> 返回
            </a>
        </form>
    </div>
</div>
@endsection