@extends('soho::layouts.app')

@section('title', $title ?? '库存单据')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">单据总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">草稿 / 待审</div><div class="fs-4 fw-semibold text-secondary">{{ $stats['draft'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">执行中</div><div class="fs-4 fw-semibold text-warning">{{ $stats['executing'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已完成</div><div class="fs-4 fw-semibold text-success">{{ $stats['completed'] }}</div>
    </div></div></div>
</div>

@if(! empty($approvals))
<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">库存调整审批队列</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>单号</th><th>商品</th><th>仓库</th><th class="text-end">数量</th><th class="text-center">状态</th><th>原因</th><th class="text-center">提交时间</th><th class="text-center">操作</th></tr></thead>
                <tbody>
                    @forelse($approvals as $a)
                        <tr>
                            <td class="font-monospace small">{{ $a->order_no ?: '审批#'.$a->id }}</td>
                            <td>{{ $a->product_name ?: '-' }}</td>
                            <td>{{ $a->warehouse_id ? '#'.$a->warehouse_id : '-' }}</td>
                            <td class="text-end">{{ $a->quantity }}</td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $a->status }}</span></td>
                            <td class="small">{{ $a->reason ?: '-' }}</td>
                            <td class="text-center small text-muted">{{ $a->submitted_at ?: '-' }}</td>
                            <td class="text-center">
                                @if($a->status === 'pending')
                                    <div class="d-flex gap-1 justify-content-center">
                                        <form method="POST" action="{{ route('soho.warehouse.adjust.approve', $a->id) }}">
                                            @csrf
                                            <button class="btn btn-outline-success btn-sm"><i class="fa fa-check"></i> 通过</button>
                                        </form>
                                        <form method="POST" action="{{ route('soho.warehouse.adjust.reject', $a->id) }}"
                                              onsubmit="return confirm('确认驳回该调整申请？')">
                                            @csrf
                                            <button class="btn btn-outline-danger btn-sm"><i class="fa fa-times"></i> 驳回</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">暂无调整申请。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $approvals->links() }}
    </div>
</div>
@endif

<div class="card">
    <div class="card-header"><h5 class="mb-0">{{ $title ?? '库存单据' }}</h5></div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                @unless($fixedType)
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">全部类型</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" @selected(request('type') === $type)>{{ ['inbound' => '入库', 'outbound' => '出库', 'transfer' => '调拨', 'stocktake' => '盘点', 'adjust' => '调整'][$type] ?? $type }}</option>
                            @endforeach
                        </select>
                    </div>
                @endunless
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['draft' => '草稿', 'pending' => '待审', 'approved' => '已审', 'executing' => '执行中', 'completed' => '已完成', 'cancelled' => '已取消'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="单据号" value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>单据号</th>
                        <th class="text-center">类型</th>
                        <th>仓库</th>
                        <th class="text-end">数量</th>
                        <th class="text-end">金额</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">创建</th>
                        <th class="text-center">完成</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $st = ['draft' => 'secondary', 'pending' => 'warning', 'approved' => 'info', 'executing' => 'primary', 'completed' => 'success', 'cancelled' => 'dark'];
                            $tp = ['inbound' => '入库', 'outbound' => '出库', 'transfer' => '调拨', 'stocktake' => '盘点', 'adjust' => '调整'];
                        @endphp
                        <tr>
                            <td class="font-monospace small">{{ $row->document_no }}</td>
                            <td class="text-center small">{{ $tp[$row->document_type] ?? $row->document_type }}</td>
                            <td>{{ $warehouses[$row->warehouse_id]->name ?? '仓库#'.$row->warehouse_id }}</td>
                            <td class="text-end">{{ number_format((float) $row->total_quantity) }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->total_amount, 2) }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status] ?? 'secondary' }}">{{ $row->status }}</span></td>
                            <td class="text-center small text-muted">{{ $row->created_at }}</td>
                            <td class="text-center small text-muted">{{ $row->completed_at ?: '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('soho.warehouse.inventory-doc.flow', $row->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-search me-1"></i> 详情
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="fa fa-inbox fa-2x d-block mb-2"></i>暂无单据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
