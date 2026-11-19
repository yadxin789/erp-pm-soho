@extends('soho::layouts.app')

@section('title', '安全库存')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">安全库存</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item active">安全库存</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- 搜索/筛选 -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">SKU/商品名称</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">仓库</label>
                        <select class="form-select" name="warehouse_id">
                            <option value="">全部</option>
                            @foreach($warehouseOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('warehouse_id') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">预警等级</label>
                        <select class="form-select" name="alert_level">
                            <option value="">全部</option>
                            <option value="low" {{ request('alert_level') == 'low' ? 'selected' : '' }}>低于安全线</option>
                            <option value="warn" {{ request('alert_level') == 'warn' ? 'selected' : '' }}>接近安全线</option>
                            <option value="normal" {{ request('alert_level') == 'normal' ? 'selected' : '' }}>正常</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                        <a href="{{ route('soho.procurement.safe-stocks.index') }}" class="btn btn-outline-secondary">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white"><div class="card-body py-3"><small>监控SKU数</small><h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white"><div class="card-body py-3"><small>低于安全线</small><h3 class="mb-0">{{ $stats['below_safe'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark"><div class="card-body py-3"><small>接近安全线</small><h3 class="mb-0">{{ $stats['near_safe'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white"><div class="card-body py-3"><small>库存正常</small><h3 class="mb-0">{{ $stats['normal'] ?? 0 }}</h3></div></div>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>SKU</th>
                        <th>商品名称</th>
                        <th>仓库</th>
                        <th>当前库存</th>
                        <th>安全库存</th>
                        <th>状态</th>
                        <th>最后更新</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($safeStocks as $item)
                        <tr>
                            <td class="text-muted">{{ $item->id }}</td>
                            <td><code>{{ $item->sku_code ?? '-' }}</code></td>
                            <td>{{ $item->product_name ?? '-' }}</td>
                            <td>{{ $item->warehouse_name ?? '-' }}</td>
                            <td>{{ $item->current_stock ?? 0 }}</td>
                            <td>{{ $item->safe_stock ?? 0 }}</td>
                            <td>
                                @php
                                    $stock = $item->current_stock ?? 0;
                                    $safe = $item->safe_stock ?? 0;
                                    $level = $stock < $safe ? 'low' : ($stock < $safe * 1.2 ? 'warn' : 'normal');
                                    $levelColors = ['low' => 'danger', 'warn' => 'warning', 'normal' => 'success'];
                                    $levelLabels = ['low' => '低于安全线', 'warn' => '接近安全线', 'normal' => '正常'];
                                @endphp
                                <span class="badge bg-{{ $levelColors[$level] }}">{{ $levelLabels[$level] }}</span>
                            </td>
                            <td class="small">{{ optional($item->updated_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($safeStocks->total() > 0)
            <div class="card-footer">{{ $safeStocks->links() }}</div>
        @endif
    </div>
@endsection
