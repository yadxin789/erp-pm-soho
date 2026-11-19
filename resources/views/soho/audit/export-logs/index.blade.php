@extends('soho::layouts.app')

{{-- 导出日志审计列表视图 --}}
@section('title', '导出日志')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">导出日志</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item">审计中心</li>
                    <li class="breadcrumb-item active">导出日志</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.audit.export-log') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">关键词</label>
                        <input type="text" class="form-control" name="search" placeholder="导出单号/文件名/操作人" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">导出模块</label>
                        <select class="form-select" name="module">
                            <option value="">全部</option>
                            @foreach($moduleOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('module') == $value ? 'selected' : '' }}>{{ $label }}</option>
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
                        <label class="form-label small">开始日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('soho.audit.export-log') }}" class="btn btn-outline-secondary btn-sm">重置筛选</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>导出总数</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>成功</small>
                    <h3 class="mb-0">{{ $stats['success'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>失败</small>
                    <h3 class="mb-0">{{ $stats['failed'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>今日导出</small>
                    <h3 class="mb-0">{{ $stats['today'] ?? 0 }}</h3>
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
                        <th>导出单号</th>
                        <th>操作人</th>
                        <th>导出模块</th>
                        <th>文件名</th>
                        <th>导出时间</th>
                        <th>状态</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 状态对应的 badge 颜色 --}}
                    @php
                        $statusColors = [
                            'success' => 'success',
                            'failed'  => 'danger',
                        ];
                    @endphp
                    @forelse($exportLogs as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $exportLogs->firstItem() + $index }}</td>
                            <td><code>{{ $item->export_no ?? '-' }}</code></td>
                            <td>{{ $item->operator ?? '-' }}</td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $moduleOptions[$item->module] ?? $item->module ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $item->file_name ?? '-' }}</td>
                            <td class="small">{{ $item->exported_at ? \Carbon\Carbon::parse($item->exported_at)->format('Y-m-d H:i') : '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$item->status] ?? 'secondary' }}">
                                    {{ $statusOptions[$item->status] ?? $item->status }}
                                </span>
                            </td>
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
        {{-- 分页 --}}
        @if($exportLogs->total() > 0)
            <div class="card-footer">{{ $exportLogs->links() }}</div>
        @endif
    </div>
@endsection
