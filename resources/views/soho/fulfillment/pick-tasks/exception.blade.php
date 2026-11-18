@extends('soho::layouts.app')

@section('title', '拣货异常列表')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item"><a href="{{ route('fulfillment.pick-tasks.index') }}">待拣货任务</a></li>
        <li class="breadcrumb-item active">拣货异常</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-exclamation-triangle me-2"></i>拣货异常列表</h4>
    <div>
        <a href="{{ route('fulfillment.pick-tasks.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i>返回待拣货
        </a>
    </div>
</div>

{{-- 异常统计卡片 --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">库存不足</div>
                <div class="fs-3 text-danger">{{ $stats['stock_shortage'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">条码不匹配</div>
                <div class="fs-3 text-warning">{{ $stats['barcode_mismatch'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">库位错误</div>
                <div class="fs-3 text-info">{{ $stats['location_error'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">其他异常</div>
                <div class="fs-3 text-secondary">{{ $stats['other'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

{{-- 异常列表 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">异常记录</h5>
        <span class="badge bg-danger">{{ $exceptions->total() }} 条异常</span>
    </div>
    <div class="card-body">
        {{-- 筛选表单 --}}
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="异常编号/任务编号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="stock_shortage" @selected(request('type') == 'stock_shortage')>库存不足</option>
                        <option value="barcode_mismatch" @selected(request('type') == 'barcode_mismatch')>条码不匹配</option>
                        <option value="location_error" @selected(request('type') == 'location_error')>库位错误</option>
                        <option value="other" @selected(request('type') == 'other')>其他异常</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="pending" @selected(request('status') == 'pending')>待处理</option>
                        <option value="processing" @selected(request('status') == 'processing')>处理中</option>
                        <option value="resolved" @selected(request('status') == 'resolved')>已解决</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i>筛选
                    </button>
                    <a href="{{ route('fulfillment.pick-tasks.exception') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>异常编号</th>
                        <th>任务编号</th>
                        <th>异常类型</th>
                        <th>异常描述</th>
                        <th>严重程度</th>
                        <th>状态</th>
                        <th>上报时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exceptions as $exception)
                    <tr>
                        <td><code>{{ $exception->exception_no }}</code></td>
                        <td>{{ $exception->task_no ?? '-' }}</td>
                        <td>
                            @if($exception->type == 'stock_shortage')
                                <span class="badge bg-danger">库存不足</span>
                            @elseif($exception->type == 'barcode_mismatch')
                                <span class="badge bg-warning text-dark">条码不匹配</span>
                            @elseif($exception->type == 'location_error')
                                <span class="badge bg-info">库位错误</span>
                            @else
                                <span class="badge bg-secondary">其他</span>
                            @endif
                        </td>
                        <td>{{ $exception->description }}</td>
                        <td>
                            @if($exception->severity == 'high')
                                <span class="badge bg-danger">高</span>
                            @elseif($exception->severity == 'medium')
                                <span class="badge bg-warning text-dark">中</span>
                            @else
                                <span class="badge bg-secondary">低</span>
                            @endif
                        </td>
                        <td>
                            @if($exception->status == 'pending')
                                <span class="badge bg-danger">待处理</span>
                            @elseif($exception->status == 'processing')
                                <span class="badge bg-warning text-dark">处理中</span>
                            @else
                                <span class="badge bg-success">已解决</span>
                            @endif
                        </td>
                        <td>{{ optional($exception->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('fulfillment.pick-tasks.exception.show', $exception) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="handleException({{ $exception->id }})">
                                <i class="fa fa-check"></i> 处理
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-check-circle fa-2x mb-2 d-block"></i>
                            暂无拣货异常
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $exceptions->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function handleException(id) {
    if (confirm('确认处理该异常？')) {
        // 跳转到异常处理页面
    }
}
</script>
@endsection