@extends('soho::layouts.app')

@section('title', '提交库存调整申请')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-1"><i class="fa fa-sliders text-warning me-1"></i>提交库存调整申请</h5>
        <div class="text-muted small">提交后进入审批队列；审批通过即按申请数量落账并写库存事务/流水。</div>
    </div>
    <a href="{{ route('soho.warehouse.inventory-doc') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i> 返回单据列表
    </a>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">申请信息</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.warehouse.adjust.store') }}" class="row g-3">
            @csrf
            <div class="col-md-3">
                <label class="form-label small text-muted">仓库 <span class="text-danger">*</span></label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">请选择仓库</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(old('warehouse_id') == $w->id)>{{ $w->code }}　{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">SKU <span class="text-danger">*</span></label>
                <select name="sku_id" class="form-select" required>
                    <option value="">请选择 SKU</option>
                    @foreach($skus as $sku)
                        <option value="{{ $sku->id }}" @selected(old('sku_id') == $sku->id)>{{ $sku->sku_code }}　{{ $sku->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">调整数量 <span class="text-danger">*</span></label>
                <input type="number" step="1" name="quantity" class="form-control" required
                       value="{{ old('quantity') }}" placeholder="正数增/负数减">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">调整原因 <span class="text-danger">*</span></label>
                <input type="text" name="reason" class="form-control" required maxlength="100"
                       value="{{ old('reason') }}" placeholder="如：破损报废 / 盘盈盘亏 / 样品领用">
            </div>
            <div class="col-12">
                <label class="form-label small text-muted">备注</label>
                <textarea name="remark" class="form-control" rows="2" maxlength="500">{{ old('remark') }}</textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-warning"><i class="fa fa-paper-plane me-1"></i> 提交申请</button>
                <span class="form-text ms-2">数量为正表示增加库存，为负表示扣减（不得超过当前可用库存）。</span>
            </div>
        </form>
    </div>
</div>
@endsection
