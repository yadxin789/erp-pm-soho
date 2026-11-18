@extends('soho::layouts.app')

@section('title', '调价申请')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">价格中心</a></li>
        <li class="breadcrumb-item active">调价申请</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-exchange me-2"></i>调价申请</h4>
    <a href="{{ route('price.price-change-requests.create') }}" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>新建申请
    </a>
</div>

{{-- 筛选表单 --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('price.price-change-requests.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">商品</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="商品名称/SKU">
            </div>
            <div class="col-md-2">
                <label class="form-label">状态</label>
                <select class="form-select" name="status">
                    <option value="">全部状态</option>
                    @foreach($statusLabels ?? [] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">开始时间</label>
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">结束时间</label>
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-1">
                    <i class="fa fa-search"></i> 筛选
                </button>
                <a href="{{ route('price.price-change-requests.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 申请列表 --}}
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-list me-2"></i>申请列表</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>商品</th>
                        <th>SKU</th>
                        <th>当前价格</th>
                        <th>申请价格</th>
                        <th>变动%</th>
                        <th>原因</th>
                        <th>申请人</th>
                        <th>状态</th>
                        <th>申请时间</th>
                        <th width="200">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $index => $request)
                        @php
                            $changePercent = $request->current_price > 0 ? (($request->requested_price - $request->current_price) / $request->current_price) * 100 : 0;
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $request->product->name ?? '-' }}</div>
                            </td>
                            <td><code class="small">{{ $request->sku ?? '-' }}</code></td>
                            <td class="text-muted">¥{{ number_format($request->current_price ?? 0, 2) }}</td>
                            <td class="fw-semibold">¥{{ number_format($request->requested_price ?? 0, 2) }}</td>
                            <td>
                                @if($changePercent > 0)
                                    <span class="badge bg-success">+{{ number_format($changePercent, 1) }}%</span>
                                @elseif($changePercent < 0)
                                    <span class="badge bg-danger">{{ number_format($changePercent, 1) }}%</span>
                                @else
                                    <span class="badge bg-secondary">0%</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ Str::limit($request->reason ?? '-', 30) }}</td>
                            <td class="small">{{ $request->applicant->name ?? '-' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'cancelled' => 'secondary',
                                        'executed' => 'info',
                                    ];
                                    $statusLabel = $statusLabels[$request->status] ?? $request->status;
                                @endphp
                                <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="small">{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('price.price-change-requests.show', $request) }}" class="btn btn-sm btn-outline-primary" title="查看详情">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @if($request->status === 'pending')
                                    <a href="{{ route('price.price-change-requests.edit', $request) }}" class="btn btn-sm btn-outline-warning" title="编辑">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('price.price-change-requests.cancel', $request) }}" class="d-inline" onsubmit="return confirm('确定取消此申请？')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="取消">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($requests, 'links'))
        <div class="card-footer">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection