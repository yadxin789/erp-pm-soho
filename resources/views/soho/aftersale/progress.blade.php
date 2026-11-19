@extends('soho::layouts.app')

@section('title', '售后进度')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted small me-2">状态分布：</span>
            <a href="{{ route('soho.aftersale.progress') }}" class="badge bg-secondary text-decoration-none">全部 {{ $byStatus->sum() }}</a>
            @foreach(['pending' => '待处理', 'approved' => '已同意', 'rejected' => '已拒绝', 'completed' => '已完成'] as $key => $label)
                <a href="{{ route('soho.aftersale.progress', ['status' => $key]) }}" class="badge bg-light text-dark border text-decoration-none">
                    {{ $label }} {{ (int) ($byStatus[$key] ?? 0) }}
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">售后进度跟踪</h5>
        <a href="{{ route('soho.aftersale.apply') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 售后申请
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="搜索售后单号 / 买家 / 订单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.aftersale.progress') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>售后单号</th>
                        <th>关联订单</th>
                        <th class="text-center">当前状态</th>
                        <th>处理进度</th>
                        <th>处理结果</th>
                        <th class="text-center">申请 / 处理时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="font-monospace small">{{ $row->after_sale_no }}</td>
                            <td class="font-monospace small text-muted">{{ $row->system_order_no ?: '-' }}</td>
                            <td class="text-center">
                                @php $st = ['pending' => ['待处理', 'warning'], 'approved' => ['已同意', 'success'], 'rejected' => ['已拒绝', 'danger'], 'completed' => ['已完成', 'secondary']]; @endphp
                                <span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span>
                            </td>
                            <td style="min-width: 260px;">
                                @php
                                    $steps = [
                                        ['label' => '买家申请', 'done' => true],
                                        ['label' => '资料齐全', 'done' => (bool) $row->description],
                                        ['label' => '客服处理', 'done' => (bool) $row->handle_time],
                                        ['label' => '处理完成', 'done' => in_array($row->status, ['approved', 'completed'], true)],
                                    ];
                                @endphp
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    @foreach($steps as $i => $step)
                                        <span class="badge {{ $step['done'] ? 'bg-success' : 'bg-light text-muted border' }}">{{ $step['label'] }}</span>
                                        @if(! $loop->last)
                                            <i class="fa fa-angle-right text-muted"></i>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="small">{{ $row->handle_result ?: '—' }}</td>
                            <td class="text-center small text-muted">
                                {{ $row->apply_time }}<br>{{ $row->handle_time ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">没有符合条件的售后单。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
