@extends('soho::layouts.app')

@section('title', '采购入库协同')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">采购入库协同</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item active">采购入库协同</li>
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
                        <label class="form-label small">入库单号/到货单号</label>
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
                        <label class="form-label small">状态</label>
                        <select class="form-select" name="status">
                            <option value="">全部</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">入库日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                        <a href="{{ route('soho.procurement.inbounds.index') }}" class="btn btn-outline-secondary">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white"><div class="card-body py-3"><small>总入库单</small><h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark"><div class="card-body py-3"><small>待入库</small><h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white"><div class="card-body py-3"><small>入库中</small><h3 class="mb-0">{{ $stats['processing'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white"><div class="card-body py-3"><small>已完成</small><h3 class="mb-0">{{ $stats['completed'] ?? 0 }}</h3></div></div>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>入库单号</th>
                        <th>关联到货单</th>
                        <th>仓库</th>
                        <th>商品数</th>
                        <th>状态</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inbounds as $inbound)
                        <tr>
                            <td class="text-muted">{{ $inbound->id }}</td>
                            <td><code>{{ $inbound->inbound_no ?? 'INB-' . $inbound->id }}</code></td>
                            <td>{{ $inbound->arrival_no ?? '-' }}</td>
                            <td>{{ $inbound->warehouse_name ?? '-' }}</td>
                            <td>{{ $inbound->items_count ?? 0 }} 项</td>
                            <td>
                                @php
                                    $statusColors = ['pending' => 'warning', 'processing' => 'info', 'completed' => 'success'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$inbound->status] ?? 'secondary' }}">
                                    {{ $statusOptions[$inbound->status] ?? $inbound->status }}
                                </span>
                            </td>
                            <td class="small">{{ optional($inbound->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inbounds->total() > 0)
            <div class="card-footer">{{ $inbounds->links() }}</div>
        @endif
    </div>
@endsection
