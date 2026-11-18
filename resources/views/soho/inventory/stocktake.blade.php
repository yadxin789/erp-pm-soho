@extends('soho::layouts.app')
@section('title', '库存盘点')
@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">库存盘点</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('soho.inventory.stocktake.store') }}" id="stocktake-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">仓库 <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value="">请选择仓库</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">盘点明细 <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-item">
                                <i class="fa fa-plus"></i> 添加项
                            </button>
                        </div>
                        <div id="items-container">
                            <div class="item-row row g-2 mb-2">
                                <div class="col-md-7">
                                    <select name="items[0][sku_id]" class="form-select" required>
                                        <option value="">选择SKU</option>
                                        @foreach($skus as $sku)
                                        <option value="{{ $sku->id }}">{{ $sku->sku_code ?? $sku->name }} - {{ $sku->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="items[0][quantity]" class="form-control" placeholder="数量" min="0" required>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger remove-item w-100">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="盘点备注信息">{{ old('remark') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-check me-1"></i> 确认盘点
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">盘点记录 <small class="text-muted">（最近 50 条）</small></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>流水号</th>
                                <th>仓库</th>
                                <th>SKU</th>
                                <th>变动数量</th>
                                <th>变动前</th>
                                <th>变动后</th>
                                <th>操作人</th>
                                <th>时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocktakeFlows ?? collect() as $flow)
                            <tr>
                                <td>{{ $flow->flow_no ?? $flow->id }}</td>
                                <td>{{ $flow->warehouse->name ?? '-' }}</td>
                                <td>{{ $flow->sku->sku_code ?? ($flow->sku->name ?? '-') }}</td>
                                <td>
                                    @php
                                        $diff = ($flow->after_quantity ?? 0) - ($flow->before_quantity ?? 0);
                                    @endphp
                                    @if($diff > 0)
                                        <span class="badge bg-success">+{{ $diff }}</span>
                                    @elseif($diff < 0)
                                        <span class="badge bg-danger">{{ $diff }}</span>
                                    @else
                                        <span class="badge bg-secondary">0</span>
                                    @endif
                                </td>
                                <td>{{ $flow->before_quantity ?? 0 }}</td>
                                <td>{{ $flow->after_quantity ?? 0 }}</td>
                                <td>{{ $flow->operator->name ?? '-' }}</td>
                                <td>{{ $flow->created_at?->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted">暂无盘点记录</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    const container = document.getElementById('items-container');
    const addBtn = document.getElementById('add-item');
    let itemIndex = 1;

    const skuOptions = `@foreach($skus as $sku)<option value="{{ $sku->id }}">{{ $sku->sku_code ?? $sku->name }} - {{ $sku->name }}</option>@endforeach`;

    function updateIndices() {
        const rows = container.querySelectorAll('.item-row');
        rows.forEach(function(row, index) {
            row.querySelector('select').name = 'items[' + index + '][sku_id]';
            row.querySelector('input').name = 'items[' + index + '][quantity]';
        });
    }

    addBtn.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'item-row row g-2 mb-2';
        row.innerHTML = `
            <div class="col-md-7">
                <select name="items[${itemIndex}][sku_id]" class="form-select" required>
                    <option value="">选择SKU</option>
                    ${skuOptions}
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="数量" min="0" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger remove-item w-100">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        itemIndex++;
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const rows = container.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
                updateIndices();
            } else {
                alert('至少保留一项盘点明细');
            }
        }
    });
})();
</script>
@endsection