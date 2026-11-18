@extends('soho::layouts.app')
@section('title', '出库管理')
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">出库操作</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('soho.inventory.out.store') }}">
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
                        <label class="form-label">SKU <span class="text-danger">*</span></label>
                        <select name="sku_id" class="form-select" required>
                            <option value="">请选择SKU</option>
                            @foreach($skus as $sku)
                            <option value="{{ $sku->id }}" @selected(old('sku_id') == $sku->id)>{{ $sku->sku_code ?? $sku->name }} - {{ $sku->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">数量 <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" value="{{ old('quantity') }}" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">关联单号</label>
                        <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}" placeholder="如订单号/发货单号">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="出库备注信息">{{ old('remark') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-minus me-1"></i> 确认出库
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">出库记录 <small class="text-muted">（最近 50 条）</small></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>流水号</th>
                                <th>仓库</th>
                                <th>SKU</th>
                                <th>数量</th>
                                <th>关联单号</th>
                                <th>操作人</th>
                                <th>时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outboundFlows ?? collect() as $flow)
                            <tr>
                                <td>{{ $flow->flow_no ?? $flow->id }}</td>
                                <td>{{ $flow->warehouse->name ?? '-' }}</td>
                                <td>{{ $flow->sku->sku_code ?? ($flow->sku->name ?? '-') }}</td>
                                <td><span class="badge bg-danger">-{{ $flow->quantity ?? $flow->change_quantity ?? 0 }}</span></td>
                                <td>{{ $flow->reference_no ?? '-' }}</td>
                                <td>{{ $flow->operator->name ?? '-' }}</td>
                                <td>{{ $flow->created_at?->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted">暂无出库记录</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection