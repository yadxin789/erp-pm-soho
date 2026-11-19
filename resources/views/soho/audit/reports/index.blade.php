@extends('soho::layouts.app')

{{-- 审计报表视图 --}}
@section('title', '审计报表')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">审计报表</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item">审计中心</li>
                    <li class="breadcrumb-item active">审计报表</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.audit.reports') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">开始日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">快捷范围</label>
                        <select class="form-select" id="rangeSelect">
                            <option value="">自定义</option>
                            @foreach($rangeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                        <a href="{{ route('soho.audit.reports') }}" class="btn btn-outline-secondary">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>操作日志数</small>
                    <h3 class="mb-0">{{ $stats['operation'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>敏感操作数</small>
                    <h3 class="mb-0">{{ $stats['sensitive'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>异常事件数</small>
                    <h3 class="mb-0">{{ $stats['exception'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>审计事件合计</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 数据表格 --}}
    <div class="card">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fa fa-chart-bar me-2 text-primary"></i>每日审计统计明细</h6>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>日期</th>
                        <th>操作日志数</th>
                        <th>敏感操作数</th>
                        <th>异常事件数</th>
                        <th>当日合计</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyStats as $index => $row)
                        <tr>
                            <td class="text-muted">{{ $dailyStats->firstItem() + $index }}</td>
                            <td><code>{{ $row['stat_date'] ?? '-' }}</code></td>
                            <td><span class="badge bg-primary">{{ $row['operation_count'] ?? 0 }}</span></td>
                            <td><span class="badge bg-warning text-dark">{{ $row['sensitive_count'] ?? 0 }}</span></td>
                            <td><span class="badge bg-danger">{{ $row['exception_count'] ?? 0 }}</span></td>
                            <td class="fw-semibold">{{ $row['daily_total'] ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>所选时间范围内暂无审计数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- 分页 --}}
        @if($dailyStats->total() > 0)
            <div class="card-footer">{{ $dailyStats->links() }}</div>
        @endif
    </div>

    {{-- 快捷范围选择脚本 --}}
    <script>
        // 选择快捷范围时自动填充日期并提交表单
        document.getElementById('rangeSelect').addEventListener('change', function () {
            var days = parseInt(this.value);
            if (!days) return;
            var today = new Date();
            var start = new Date();
            start.setDate(start.getDate() - days);
            document.querySelector('input[name="date_from"]').value = start.toISOString().split('T')[0];
            document.querySelector('input[name="date_to"]').value = today.toISOString().split('T')[0];
        });
    </script>
@endsection
