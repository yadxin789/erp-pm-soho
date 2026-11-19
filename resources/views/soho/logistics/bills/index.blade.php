@extends('soho::layouts.app')

{{-- 物流账单页面标题 --}}
@section('title', '物流账单')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">物流账单</h4>
            {{-- 面包屑导航 --}}
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('soho.logistics.bill') }}">物流管理</a></li>
                    <li class="breadcrumb-item active">物流账单</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.logistics.bill') }}">
                <div class="row g-2 align-items-end">
                    {{-- 账单号搜索 --}}
                    <div class="col-md-3">
                        <label class="form-label small">账单号</label>
                        <input type="text" class="form-control" name="bill_no" placeholder="请输入账单号" value="{{ request('bill_no') }}">
                    </div>
                    {{-- 快递公司 --}}
                    <div class="col-md-2">
                        <label class="form-label small">快递公司</label>
                        <select class="form-select" name="express_company">
                            <option value="">全部</option>
                            @foreach(($expressCompanies ?? []) as $value => $label)
                                <option value="{{ $value }}" {{ request('express_company') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- 对账状态 --}}
                    <div class="col-md-2">
                        <label class="form-label small">对账状态</label>
                        <select class="form-select" name="status">
                            <option value="">全部</option>
                            @foreach(($statusOptions ?? []) as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- 时间范围 --}}
                    <div class="col-md-2">
                        <label class="form-label small">开始日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i></button>
                        <a href="{{ route('soho.logistics.bill') }}" class="btn btn-outline-secondary w-100 mt-1">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        {{-- 总账单数 --}}
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>总账单数</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 待对账 --}}
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>待对账</small>
                    <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 有差异 --}}
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>有差异</small>
                    <h3 class="mb-0">{{ $stats['difference'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        {{-- 已确认 --}}
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>已确认</small>
                    <h3 class="mb-0">¥{{ number_format($stats['total_amount'] ?? 0, 2) }}</h3>
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
                        <th>账单号</th>
                        <th>快递公司</th>
                        <th>账单周期</th>
                        <th>账单金额</th>
                        <th>差异金额</th>
                        <th>状态</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills ?? $items ?? [] as $bill)
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td><code>{{ $bill->bill_no ?? '-' }}</code></td>
                            <td>{{ $bill->express_company ?? '-' }}</td>
                            <td class="small">{{ $bill->period_start ?? '-' }} ~ {{ $bill->period_end ?? '-' }}</td>
                            <td>¥{{ number_format($bill->amount ?? 0, 2) }}</td>
                            <td>
                                @php
                                    // 差异金额大于 0 时高亮显示
                                    $diffAmount = $bill->diff_amount ?? 0;
                                @endphp
                                @if($diffAmount > 0)
                                    <span class="text-danger fw-bold">¥{{ number_format($diffAmount, 2) }}</span>
                                @else
                                    <span class="text-muted">¥0.00</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    // 对账状态颜色映射
                                    $statusColors = [
                                        'pending'    => 'warning',
                                        'matched'    => 'success',
                                        'difference' => 'danger',
                                        'confirmed'  => 'info',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$bill->status] ?? 'secondary' }}">
                                    {{ ($statusOptions[$bill->status] ?? $bill->status) ?? '-' }}
                                </span>
                            </td>
                            <td class="small">{{ optional($bill->created_at)->format('Y-m-d H:i') }}</td>
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
        {{-- 分页 --}}
        @if(isset($bills) && $bills->total() > 0)
            <div class="card-footer">{{ $bills->links() }}</div>
        @elseif(isset($items) && $items->total() > 0)
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
