@extends('soho::layouts.app')

{{-- 库存预警报表视图 --}}
@section('title', '库存预警报表')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">库存预警报表</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">运营报表</li>
                    <li class="breadcrumb-item active">库存预警</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.operation.inventory.warning') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">仓库</label>
                    <select name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">预警级别</label>
                    <select name="level" class="form-select">
                        <option value="">全部</option>
                        @foreach($levelOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('level') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.operation.inventory.warning') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>紧急缺货</small>
                    <h3 class="mb-0">{{ number_format($stats['critical_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>库存预警</small>
                    <h3 class="mb-0">{{ number_format($stats['warning_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>库存正常</small>
                    <h3 class="mb-0">{{ number_format($stats['normal_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>总SKU数</small>
                    <h3 class="mb-0">{{ number_format($stats['total_count'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 数据表格 --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>商品名称</th>
                        <th>SKU</th>
                        <th>仓库</th>
                        <th class="text-end">当前库存</th>
                        <th class="text-end">安全库存</th>
                        <th class="text-center">预警级别</th>
                        <th>更新时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>{{ $item->product_name ?? '-' }}</td>
                            <td><code>{{ $item->sku_code ?? '-' }}</code></td>
                            <td>{{ $item->warehouse_name ?? '-' }}</td>
                            <td class="text-end"><span class="fw-bold">{{ $item->actual_quantity }}</span></td>
                            <td class="text-end">{{ $item->safety_quantity }}</td>
                            <td class="text-center">
                                @if($item->warning_level === 'critical')
                                    <span class="badge bg-danger">紧急缺货</span>
                                @elseif($item->warning_level === 'warning')
                                    <span class="badge bg-warning text-dark">库存预警</span>
                                @else
                                    <span class="badge bg-success">库存正常</span>
                                @endif
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
        @if($items->total() > 0)
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
