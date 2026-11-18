@extends('soho::layouts.app')

@section('title', '作业异常列表')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">作业异常</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-exclamation-triangle me-2"></i>作业异常列表</h4>
    <div>
        <button type="button" class="btn btn-success btn-sm" onclick="exportData()">
            <i class="fa fa-download me-1"></i>导出异常
        </button>
    </div>
</div>

{{-- 异常统计卡片 --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">待处理</div>
                <div class="fs-3 text-danger">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">处理中</div>
                <div class="fs-3 text-warning">{{ $stats['processing'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">已解决</div>
                <div class="fs-3 text-success">{{ $stats['resolved'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">今日新增</div>
                <div class="fs-3 text-info">{{ $stats['today'] ?? 0 }}</div>
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
                    <input type="text" name="search" class="form-control" placeholder="异常编号/关联单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="module" class="form-select">
                        <option value="">全部模块</option>
                        <option value="pick" @selected(request('module') == 'pick')>拣货</option>
                        <option value="check" @selected(request('module') == 'check')>复核</option>
                        <option value="pack" @selected(request('module') == 'pack')>打包</option>
                        <option value="ship" @selected(request('module') == 'ship')>发货</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="severity" class="form-select">
                        <option value="">全部严重程度</option>
                        <option value="high" @selected(request('severity') == 'high')>高</option>
                        <option value="medium" @selected(request('severity') == 'medium')>中</option>
                        <option value="low" @selected(request('severity') == 'low')>低</option>
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
                    <a href="{{ route('fulfillment.exceptions.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>异常编号</th>
                        <th>所属模块</th>
                        <th>异常类型</th>
                        <th>异常标题</th>
                        <th>关联单号</th>
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
                        <td>
                            @if($exception->module == 'pick')
                                <span class="badge bg-info">拣货</span>
                            @elseif($exception->module == 'check')
                                <span class="badge bg-warning text-dark">复核</span>
                            @elseif($exception->module == 'pack')
                                <span class="badge bg-primary">打包</span>
                            @elseif($exception->module == 'ship')
                                <span class="badge bg-success">发货</span>
                            @else
                                <span class="badge bg-secondary">{{ $exception->module }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $exception->type_label ?? $exception->type }}</span>
                        </td>
                        <td>{{ $exception->title }}</td>
                        <td>{{ $exception->related_no ?? '-' }}</td>
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
                            <a href="{{ route('fulfillment.exceptions.show', $exception) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="handleException({{ $exception->id }})">
                                <i class="fa fa-check"></i> 处理
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-check-circle fa-2x mb-2 d-block"></i>
                            暂无作业异常
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
        // 处理异常逻辑
    }
}

function exportData() {
    if (confirm('确认导出异常数据？')) {
        // 导出逻辑
    }
}
</script>
@endsection