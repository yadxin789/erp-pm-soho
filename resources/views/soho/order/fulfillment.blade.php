@extends('soho::layouts.app')

@section('title', '履约单')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">履约单总数</div>
                <div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">待处理</div>
                <div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">履约单列表</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('soho.order.pending-fulfill') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa fa-plus me-1"></i> 去生成履约单
            </a>
            <a href="{{ route('soho.order.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> 返回订单
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索系统单号 / 平台单号 / 买家" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="pending" @selected(request('status') === 'pending')>待处理</option>
                        <option value="picking" @selected(request('status') === 'picking')>拣货中</option>
                        <option value="done" @selected(request('status') === 'done')>已完成</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.order.fulfillment') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>系统单号</th>
                        <th>平台单号</th>
                        <th>买家</th>
                        <th>仓库</th>
                        <th class="text-center">状态</th>
                        <th>备注</th>
                        <th class="text-center">生成时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="text-muted">#{{ $row->id }}</td>
                            <td class="font-monospace small">{{ $row->system_order_no }}</td>
                            <td class="font-monospace small text-muted">{{ $row->platform_order_no }}</td>
                            <td>{{ $row->buyer_nick ?: '-' }}</td>
                            <td>{{ $row->warehouse_name ? ($row->warehouse_code.' '.$row->warehouse_name) : '-' }}</td>
                            <td class="text-center">
                                @php
                                    $map = [
                                        'pending' => ['待处理', 'warning'],
                                        'picking' => ['拣货中', 'info'],
                                        'done' => ['已完成', 'success'],
                                    ];
                                    [$label, $class] = $map[$row->status] ?? [$row->status, 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $class }}">{{ $label }}</span>
                            </td>
                            <td class="small text-muted">{{ $row->remark ?: '-' }}</td>
                            <td class="text-center small text-muted">{{ $row->created_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                还没有履约单。先到「待履约」页面为已付款订单生成履约单。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
