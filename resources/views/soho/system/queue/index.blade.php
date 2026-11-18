@extends('soho::layouts.app')

@section('title', '队列监控')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-tasks me-2"></i>队列与定时任务监控</h4>
        <p class="text-muted mb-0 small">监控队列任务执行状态，管理失败任务与定时任务</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('soho.queue.monitor') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-refresh me-1"></i>刷新
        </a>
        <form method="POST" action="{{ route('soho.queue.flush') }}" class="d-inline"
              onsubmit="return confirm('确定清空所有失败任务？此操作不可恢复')">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fa fa-trash me-1"></i>清空失败任务
            </button>
        </form>
    </div>
</div>

{{-- 队列概览卡片 --}}
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                     style="width: 48px; height: 48px; background: #e3f2fd;">
                    <i class="fa fa-clock-o text-primary" style="font-size: 22px;"></i>
                </div>
                <div class="fw-bold text-primary" style="font-size: 1.8rem;">{{ $stats['pending'] }}</div>
                <div class="text-muted small">待处理任务</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                     style="width: 48px; height: 48px; background: #fff3cd;">
                    <i class="fa fa-hourglass-half text-warning" style="font-size: 22px;"></i>
                </div>
                <div class="fw-bold text-warning" style="font-size: 1.8rem;">{{ $stats['delayed'] }}</div>
                <div class="text-muted small">延迟任务</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                     style="width: 48px; height: 48px; background: #fde8e8;">
                    <i class="fa fa-exclamation-circle text-danger" style="font-size: 22px;"></i>
                </div>
                <div class="fw-bold text-danger" style="font-size: 1.8rem;">{{ $stats['failed'] }}</div>
                <div class="text-muted small">失败任务</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                     style="width: 48px; height: 48px; background: #e8f5e9;">
                    <i class="fa fa-database text-success" style="font-size: 22px;"></i>
                </div>
                <div class="fw-bold text-success" style="font-size: 1.8rem;">{{ $stats['total'] }}</div>
                <div class="text-muted small">任务总数</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- 左侧：失败任务列表 --}}
    <div class="col-lg-8 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fa fa-exclamation-triangle me-2 text-danger"></i>失败任务列表</h6>
                <span class="badge bg-danger">{{ $failedJobs->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($failedJobs->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">暂无失败任务</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>任务</th>
                                    <th>队列</th>
                                    <th>失败时间</th>
                                    <th class="text-end">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($failedJobs as $job)
                                    <tr>
                                        <td class="text-muted">{{ $job->id }}</td>
                                        <td>
                                            <div class="fw-semibold small">{{ $job->payload['displayName'] ?? ($job->payload['job'] ?? '未知任务') }}</div>
                                            <details class="small">
                                                <summary class="text-muted cursor-pointer">查看异常</summary>
                                                <pre class="small text-danger mt-1 p-2 bg-light rounded" style="white-space: pre-wrap; max-height: 200px; overflow-y: auto;">{{ $job->exception_short }}</pre>
                                            </details>
                                        </td>
                                        <td><code class="small">{{ $job->queue }}</code></td>
                                        <td class="small text-muted">{{ \Carbon\Carbon::parse($job->failed_at)->format('Y-m-d H:i:s') }}</td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <form method="POST" action="{{ route('soho.queue.retry', $job->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" title="重试">
                                                        <i class="fa fa-repeat"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('soho.queue.forget', $job->id) }}" class="d-inline"
                                                      onsubmit="return confirm('确定删除失败任务 #{{ $job->id }}？')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="删除">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 右侧：队列配置 + 定时任务 --}}
    <div class="col-lg-4 mb-3">
        {{-- 队列配置 --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fa fa-cog me-2 text-primary"></i>队列配置</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0 align-middle">
                    <tbody>
                        <tr>
                            <td class="text-muted small ps-3">默认连接</td>
                            <td class="fw-semibold small"><code>{{ $queueConfig['default'] }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted small ps-3">驱动类型</td>
                            <td class="fw-semibold small">{{ $queueConfig['driver'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small ps-3">队列名称</td>
                            <td class="fw-semibold small"><code>{{ $queueConfig['queue'] }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted small ps-3">重试超时</td>
                            <td class="fw-semibold small">{{ $queueConfig['retry_after'] }} 秒</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 定时任务 --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fa fa-calendar-check-o me-2 text-primary"></i>定时任务</h6>
                <span class="badge bg-secondary">{{ count($scheduledTasks) }}</span>
            </div>
            <div class="card-body p-0">
                @if(empty($scheduledTasks))
                    <div class="text-center py-4">
                        <i class="fa fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted small mb-0">暂无定时任务</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($scheduledTasks as $task)
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <code class="small">{{ $task['expression'] }}</code>
                                    <small class="text-muted">{{ $task['next_run'] }}</small>
                                </div>
                                <div class="small text-dark mt-1">{{ $task['command'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
