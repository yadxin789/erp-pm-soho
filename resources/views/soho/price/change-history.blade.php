@extends('soho::layouts.app')

@section('title', '调价历史')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">调价申请</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已批准</div><div class="fs-4 fw-semibold text-success">{{ $stats['approved'] }}</div>
    </div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待审批</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已驳回</div><div class="fs-4 fw-semibold text-danger">{{ $stats['rejected'] }}</div>
    </div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">涨价 / 降价</div><div class="fs-4 fw-semibold"><span class="text-danger">{{ $stats['up'] }}</span> / <span class="text-success">{{ $stats['down'] }}</span></div>
    </div></div></div>
    <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">平均变动幅度</div><div class="fs-4 fw-semibold">{{ number_format($avgChange, 2) }}%</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">调价申请历史</h5>
        <a href="{{ route('soho.report.procurement') }}" class="btn btn-outline-secondary btn-sm">采购报表</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['pending' => '待审批', 'approved' => '已批准', 'rejected' => '已驳回', 'cancelled' => '已取消'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="direction" class="form-select">
                        <option value="">全部方向</option>
                        <option value="up" @selected(request('direction') === 'up')>涨价</option>
                        <option value="down" @selected(request('direction') === 'down')>降价</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="SKU 编码 / 申请人" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.price.change.history') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th class="font-monospace">SKU</th>
                        <th class="text-end">原价</th>
                        <th class="text-end">申请价</th>
                        <th class="text-end">变动额</th>
                        <th class="text-end">变动幅度</th>
                        <th>原因</th>
                        <th>申请人</th>
                        <th class="text-center">申请时间</th>
                        <th class="text-center">审批</th>
                        <th class="text-center">状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $st = ['pending' => ['待审批', 'warning'], 'approved' => ['已批准', 'success'], 'rejected' => ['已驳回', 'danger'], 'cancelled' => ['已取消', 'secondary']];
                            $up = (float) $row->change_amount > 0;
                        @endphp
                        <tr>
                            <td class="small">{{ $row->sku_code }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->current_price, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $row->requested_price, 2) }}</td>
                            <td class="text-end {{ $up ? 'text-danger' : 'text-success' }}">{{ $up ? '+' : '' }}{{ number_format((float) $row->change_amount, 2) }}</td>
                            <td class="text-end {{ $up ? 'text-danger' : 'text-success' }}">{{ $up ? '+' : '' }}{{ number_format((float) $row->change_percentage, 2) }}%</td>
                            <td class="small text-muted">{{ $row->reason ?: '—' }}</td>
                            <td class="small">{{ $row->requested_by_name ?: '—' }}</td>
                            <td class="text-center small text-muted">{{ $row->requested_at }}</td>
                            <td class="text-center small text-muted">{{ $row->approved_by_name ?: '—' }}{{ $row->approved_at ? ' · '.$row->approved_at : '' }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">暂无调价历史。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">调价执行任务</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center">任务 ID</th>
                        <th class="font-monospace">目标编码</th>
                        <th class="text-end">执行前</th>
                        <th class="text-end">执行价</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">计划时间</th>
                        <th class="text-center">执行时间</th>
                        <th>结果</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td class="text-center small text-muted">#{{ $task->id }}</td>
                            <td class="small">{{ $task->target_code }}</td>
                            <td class="text-end">¥{{ number_format((float) $task->original_price, 2) }}</td>
                            <td class="text-end">¥{{ number_format((float) $task->target_price, 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ ['pending' => 'warning', 'executing' => 'info', 'completed' => 'success', 'failed' => 'danger'][$task->status] ?? 'secondary' }}">{{ $task->status }}</span>
                            </td>
                            <td class="text-center small text-muted">{{ $task->scheduled_at ?: '—' }}</td>
                            <td class="text-center small text-muted">{{ $task->executed_at ?: '—' }}</td>
                            <td class="small text-muted">{{ $task->result_message ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">暂无调价执行任务。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
