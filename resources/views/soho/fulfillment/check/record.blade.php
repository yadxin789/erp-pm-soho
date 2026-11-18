@extends('soho::layouts.app')
@php $checkers = $checkers ?? collect(); @endphp


@section('title', '复核记录')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">复核记录</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-history me-2"></i>复核记录</h4>
    <div>
        <a href="{{ route('soho.check.tasks.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-clock me-1"></i>待复核任务
        </a>
        <button type="button" class="btn btn-success btn-sm" onclick="exportData()">
            <i class="fa fa-download me-1"></i>导出记录
        </button>
    </div>
</div>

{{-- 记录列表 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">复核操作记录</h5>
        <span class="badge bg-info">{{ $records->total() }} 条记录</span>
    </div>
    <div class="card-body">
        {{-- 筛选表单 --}}
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="复核单号/订单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="checker_id" class="form-select">
                        <option value="">全部复核员</option>
                        @foreach($checkers as $checker)
                            <option value="{{ $checker->id }}" @selected(request('checker_id') == $checker->id)>{{ $checker->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="result" class="form-select">
                        <option value="">全部结果</option>
                        <option value="pass" @selected(request('result') == 'pass')>复核通过</option>
                        <option value="diff" @selected(request('result') == 'diff')>有差异</option>
                        <option value="fail" @selected(request('result') == 'fail')>复核失败</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}" placeholder="结束日期">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i>筛选
                    </button>
                    <a href="{{ route('soho.check.tasks.record') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>记录编号</th>
                        <th>复核单号</th>
                        <th>订单号</th>
                        <th>复核员</th>
                        <th>操作类型</th>
                        <th>结果</th>
                        <th>操作时间</th>
                        <th>详情</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                    <tr>
                        <td><code>{{ $record->record_no }}</code></td>
                        <td>{{ $record->check_no ?? '-' }}</td>
                        <td>{{ $record->order_no ?? '-' }}</td>
                        <td>{{ $record->checker->name ?? '-' }}</td>
                        <td>
                            @if($record->action == 'scan')
                                <span class="badge bg-info">扫码复核</span>
                            @elseif($record->action == 'manual')
                                <span class="badge bg-warning text-dark">手动复核</span>
                            @elseif($record->action == 'system')
                                <span class="badge bg-secondary">系统自动</span>
                            @else
                                <span class="badge bg-light text-dark">{{ $record->action }}</span>
                            @endif
                        </td>
                        <td>
                            @if($record->result == 'pass')
                                <span class="badge bg-success">通过</span>
                            @elseif($record->result == 'diff')
                                <span class="badge bg-warning text-dark">有差异</span>
                            @else
                                <span class="badge bg-danger">失败</span>
                            @endif
                        </td>
                        <td>{{ optional($record->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('soho.check.tasks.record', $record) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> 查看
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                            暂无复核记录
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportData() {
    if (confirm('确认导出复核记录？')) {
        // 导出逻辑
    }
}
</script>
@endsection