@extends('soho::layouts.app')

@section('title', '采购到货')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">采购到货</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item active">采购到货</li>
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
                        <label class="form-label small">到货单号/订单号</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}">
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
                        <label class="form-label small">到货日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">至</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                        <a href="{{ route('soho.procurement.arrivals.index') }}" class="btn btn-outline-secondary">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white"><div class="card-body py-3"><small>总到货数</small><h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark"><div class="card-body py-3"><small>待入库</small><h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white"><div class="card-body py-3"><small>待验收</small><h3 class="mb-0">{{ $stats['inspecting'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white"><div class="card-body py-3"><small>已入库</small><h3 class="mb-0">{{ $stats['completed'] ?? 0 }}</h3></div></div>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>到货单号</th>
                        <th>采购订单</th>
                        <th>供应商</th>
                        <th>到货数量</th>
                        <th>状态</th>
                        <th>到货日期</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arrivals as $arrival)
                        <tr>
                            <td class="text-muted">{{ $arrival->id }}</td>
                            <td><code>{{ $arrival->arrival_no ?? 'ARR-' . $arrival->id }}</code></td>
                            <td>{{ $arrival->order_no ?? '-' }}</td>
                            <td>{{ $arrival->supplier_name ?? '-' }}</td>
                            <td>{{ $arrival->quantity ?? 0 }}</td>
                            <td>
                                @php
                                    $statusColors = ['pending' => 'warning', 'inspecting' => 'info', 'completed' => 'success'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$arrival->status] ?? 'secondary' }}">
                                    {{ $statusOptions[$arrival->status] ?? $arrival->status }}
                                </span>
                            </td>
                            <td class="small">{{ optional($arrival->created_at)->format('Y-m-d H:i') }}</td>
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
        @if($arrivals->total() > 0)
            <div class="card-footer">{{ $arrivals->links() }}</div>
        @endif
    </div>
@endsection
