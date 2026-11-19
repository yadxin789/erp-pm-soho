@extends('soho::layouts.app')

@section('title', '分仓管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">分仓管理</h5>
        <a href="{{ route('soho.order.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 返回订单
        </a>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted mb-3">
            这里列出「待处理 / 已付款 / 已发货」订单中还未指定仓库的明细行。勾选后选择仓库即可批量分仓。
        </div>

        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索系统单号 / 商品 / SKU" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.order.warehouse.assign') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('soho.order.warehouse.assign.apply') }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input class="form-check-input" type="checkbox" id="checkAll">
                            </th>
                            <th>系统单号</th>
                            <th>买家</th>
                            <th>商品</th>
                            <th>SKU</th>
                            <th class="text-center">数量</th>
                            <th class="text-center">订单状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="text-center">
                                    <input class="form-check-input js-item" type="checkbox" name="item_ids[]" value="{{ $item->id }}">
                                </td>
                                <td class="font-monospace small">{{ $item->system_order_no }}</td>
                                <td>{{ $item->buyer_nick ?: '-' }}</td>
                                <td>{{ $item->product_name ?: '-' }}</td>
                                <td class="text-muted">{{ $item->sku_name ?: '-' }}</td>
                                <td class="text-center">{{ (int) $item->quantity }}</td>
                                <td class="text-center">
                                    @php
                                        $map = [
                                            'pending' => ['待处理', 'secondary'],
                                            'paid' => ['已付款', 'success'],
                                            'shipped' => ['已发货', 'info'],
                                        ];
                                        [$label, $class] = $map[$item->order_status] ?? [$item->order_status, 'light'];
                                    @endphp
                                    <span class="badge bg-{{ $class }}">{{ $label }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fa fa-circle-check text-success me-1"></i> 所有在途订单明细都已指定仓库。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->total() > 0)
                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2 mt-2">
                    <select name="warehouse_id" class="form-select" style="max-width: 260px;" required>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->code }} {{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-warehouse me-1"></i> 指定仓库
                    </button>
                </div>
            @endif
        </form>

        {{ $items->links() }}
    </div>
</div>

@push('scripts')
<script>
document.getElementById('checkAll')?.addEventListener('change', function () {
    document.querySelectorAll('.js-item').forEach(function (box) { box.checked = this.checked; }, this);
});
</script>
@endpush
@endsection
