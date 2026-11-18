@extends('soho::layouts.app')

@section('title', '库存批次')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.inventory.index') }}">库存管理</a></li>
        <li class="breadcrumb-item active">库存批次</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">库存批次列表</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.inventory.batches') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索批次号/SKU" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.inventory.batches') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>批次号</th>
                        <th>SKU</th>
                        <th>商品名称</th>
                        <th>仓库</th>
                        <th>库存数量</th>
                        <th>入库日期</th>
                        <th>有效期</th>
                        <th>状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                    <tr>
                        <td><code>{{ $batch->batch_no }}</code></td>
                        <td>{{ $batch->sku->sku_code ?? '-' }}</td>
                        <td>{{ $batch->sku->product->name ?? ($batch->sku->name ?? '-') }}</td>
                        <td>{{ $batch->warehouse->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $batch->quantity <= 0 ? 'danger' : 'success' }}">
                                {{ $batch->quantity }}
                            </span>
                        </td>
                        <td>{{ $batch->inbound_date ? $batch->inbound_date->format('Y-m-d') : '-' }}</td>
                        <td>
                            @if($batch->expiry_date)
                                @if($batch->expiry_date->isPast())
                                    <span class="text-danger">{{ $batch->expiry_date->format('Y-m-d') }} (已过期)</span>
                                @else
                                    {{ $batch->expiry_date->format('Y-m-d') }}
                                @endif
                            @else
                                <span class="text-muted">永久</span>
                            @endif
                        </td>
                        <td>
                            @if($batch->quantity > 0 && (!$batch->expiry_date || !$batch->expiry_date->isPast()))
                                <span class="badge bg-success">正常</span>
                            @elseif($batch->expiry_date && $batch->expiry_date->isPast())
                                <span class="badge bg-danger">已过期</span>
                            @else
                                <span class="badge bg-secondary">已售罄</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $batches->links() }}
    </div>
</div>
@endsection