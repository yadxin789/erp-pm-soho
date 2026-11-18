@extends('soho::layouts.app')
@section('title', '库存流水')
@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">库存流水</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="in" @selected(request('type') == 'in')>入库</option>
                        <option value="out" @selected(request('type') == 'out')>出库</option>
                        <option value="transfer" @selected(request('type') == 'transfer')>调拨</option>
                        <option value="stocktake" @selected(request('type') == 'stocktake')>盘点</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach(\App\Soho\Models\Warehouse::where('status', true)->orderBy('name')->get() as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}" placeholder="结束日期">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.inventory.flow') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>流水号</th>
                        <th>类型</th>
                        <th>仓库</th>
                        <th>SKU</th>
                        <th>数量</th>
                        <th>变动前</th>
                        <th>变动后</th>
                        <th>关联单号</th>
                        <th>操作人</th>
                        <th>时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($flows as $flow)
                    <tr>
                        <td>{{ $flow->flow_no ?? $flow->id }}</td>
                        <td>
                            @php
                                $typeMap = [
                                    'in' => ['label' => '入库', 'class' => 'success'],
                                    'out' => ['label' => '出库', 'class' => 'warning'],
                                    'transfer' => ['label' => '调拨', 'class' => 'info'],
                                    'stocktake' => ['label' => '盘点', 'class' => 'primary'],
                                ];
                                $typeInfo = $typeMap[$flow->type] ?? ['label' => $flow->type, 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $typeInfo['class'] }}">{{ $typeInfo['label'] }}</span>
                        </td>
                        <td>{{ $flow->warehouse->name ?? '-' }}</td>
                        <td>{{ $flow->sku->sku_code ?? ($flow->sku->name ?? '-') }}</td>
                        <td>{{ $flow->quantity ?? $flow->change_quantity ?? 0 }}</td>
                        <td>{{ $flow->before_quantity ?? 0 }}</td>
                        <td>{{ $flow->after_quantity ?? 0 }}</td>
                        <td>{{ $flow->reference_no ?? '-' }}</td>
                        <td>{{ $flow->operator->name ?? '-' }}</td>
                        <td>{{ $flow->created_at?->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $flows->links() }}
    </div>
</div>
@endsection