@extends('soho::layouts.app')

@section('title', '新建库存单据')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-1"><i class="fa fa-file-circle-plus text-primary me-1"></i>新建库存单据</h5>
        <div class="text-muted small">入库 / 出库 / 调拨 / 盘点；创建后进入草稿，补充明细 → 提交审批 → 审批 → 执行。</div>
    </div>
    <a href="{{ route('soho.warehouse.inventory-doc') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i> 返回单据列表
    </a>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">单据信息</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.warehouse.inventory-doc.store') }}" class="row g-3">
            @csrf
            <div class="col-md-3">
                <label class="form-label small text-muted">单据类型 <span class="text-danger">*</span></label>
                <select name="document_type" id="doc-type" class="form-select" required>
                    @foreach(['inbound' => '入库单', 'outbound' => '出库单', 'transfer' => '调拨单', 'stocktake' => '盘点单'] as $k => $v)
                        <option value="{{ $k }}" @selected(old('document_type') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">仓库 <span class="text-danger">*</span></label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">请选择仓库</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(old('warehouse_id') == $w->id)>{{ $w->code }}　{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3" id="target-wrap" style="display:none;">
                <label class="form-label small text-muted">目标仓 <span class="text-danger">*</span></label>
                <select name="target_warehouse_id" class="form-select">
                    <option value="">请选择目标仓</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(old('target_warehouse_id') == $w->id)>{{ $w->code }}　{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">备注</label>
                <input type="text" name="remark" class="form-control" value="{{ old('remark') }}" placeholder="可选">
            </div>
            <div class="col-12">
                <button class="btn btn-primary"><i class="fa fa-plus me-1"></i> 创建单据</button>
                <span class="form-text ms-2" id="doc-hint">入库单创建后在详情页追加明细行。</span>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var typeEl = document.getElementById('doc-type');
    var wrap = document.getElementById('target-wrap');
    var hint = document.getElementById('doc-hint');
    var hints = {
        inbound: '入库单创建后在详情页追加明细行；执行时增加库存。',
        outbound: '出库单创建后在详情页追加明细行；执行时按批次 FIFO 扣减库存。',
        transfer: '调拨单需指定目标仓；执行时来源仓扣减、目标仓增加。',
        stocktake: '盘点单创建后进入「批量录入实盘数」页，按 SKU 录入实盘数量。'
    };
    function sync() {
        var t = typeEl.value;
        wrap.style.display = t === 'transfer' ? '' : 'none';
        if (hint) { hint.textContent = hints[t] || ''; }
    }
    typeEl.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
