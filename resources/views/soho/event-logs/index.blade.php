@extends('soho::layouts.app')

@section('title', '事件日志')

@section('content')
<!-- 统计概览 -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">总事件数</h6>
                        <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                    </div>
                    <div class="text-primary">
                        <i class="fa fa-list-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">已处理</h6>
                        <h4 class="mb-0 text-success">{{ $stats['processed'] ?? 0 }}</h4>
                    </div>
                    <div class="text-success">
                        <i class="fa fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">未处理</h6>
                        <h4 class="mb-0 text-warning">{{ $stats['unprocessed'] ?? 0 }}</h4>
                    </div>
                    <div class="text-warning">
                        <i class="fa fa-clock-o fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">今日新增</h6>
                        <h4 class="mb-0 text-info">{{ $stats['today'] ?? 0 }}</h4>
                    </div>
                    <div class="text-info">
                        <i class="fa fa-calendar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">事件日志列表</h5>
    </div>
    <div class="card-body">
        <!-- 搜索筛选 -->
        <form method="GET" action="{{ route('soho.event-log.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="搜索事件类型/模型" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="event_type" class="form-select">
                        <option value="">全部事件类型</option>
                        @foreach($eventTypeStats ?? [] as $type => $count)
                            <option value="{{ $type }}" @selected(request('event_type') == $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="processed" @selected(request('status') == 'processed')>已处理</option>
                        <option value="unprocessed" @selected(request('status') == 'unprocessed')>未处理</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}" placeholder="结束日期">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.event-log.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <!-- 事件日志表格 -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th width="160">发生时间</th>
                        <th width="140">事件类型</th>
                        <th width="120">模型类型</th>
                        <th width="100">模型ID</th>
                        <th width="120">操作人</th>
                        <th width="160">处理时间</th>
                        <th width="80">状态</th>
                        <th width="80">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventLogs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td><span class="badge bg-info">{{ $log->event_type }}</span></td>
                        <td>{{ $log->model_type ?? '-' }}</td>
                        <td>{{ $log->model_id ?? '-' }}</td>
                        <td>{{ $log->user->name ?? '系统' }}</td>
                        <td>
                            @if($log->processed_at)
                                {{ $log->processed_at->format('Y-m-d H:i:s') }}
                            @else
                                <span class="text-warning">未处理</span>
                            @endif
                        </td>
                        <td>
                            @if($log->processed_at)
                                <span class="badge bg-success">已处理</span>
                            @else
                                <span class="badge bg-warning">未处理</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('soho.event-log.show', $log) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2"></i>
                            <p>暂无事件日志</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                共 {{ $eventLogs->total() }} 条数据
            </div>
            {{ $eventLogs->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection
