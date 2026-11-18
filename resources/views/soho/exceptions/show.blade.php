@extends('soho::layouts.app')

@section('title', '异常详情')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.exceptions.index') }}">异常中心</a></li>
        <li class="breadcrumb-item active">异常详情</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">异常详情 #{{ $exceptionCase->id }}</h5>
                <div>
                    @if($exceptionCase->status == 'open')
                        <form method="POST" action="{{ route('soho.exceptions.resolve', $exceptionCase) }}" class="d-inline" onsubmit="return confirm('确认解决此异常？')">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fa fa-check"></i> 解决
                            </button>
                        </form>
                        <form method="POST" action="{{ route('soho.exceptions.ignore', $exceptionCase) }}" class="d-inline" onsubmit="return confirm('确认忽略此异常？')">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-ban"></i> 忽略
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 120px;">异常标题</th>
                        <td>{{ $exceptionCase->title }}</td>
                    </tr>
                    <tr>
                        <th>类型</th>
                        <td><span class="badge bg-info">{{ $exceptionCase->type_label ?? $exceptionCase->type }}</span></td>
                    </tr>
                    <tr>
                        <th>严重程度</th>
                        <td>
                            @if($exceptionCase->severity == 'high')
                                <span class="badge bg-danger">高</span>
                            @elseif($exceptionCase->severity == 'medium')
                                <span class="badge bg-warning text-dark">中</span>
                            @else
                                <span class="badge bg-secondary">低</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>状态</th>
                        <td>
                            @if($exceptionCase->status == 'open')
                                <span class="badge bg-danger">待处理</span>
                            @elseif($exceptionCase->status == 'processing')
                                <span class="badge bg-warning text-dark">处理中</span>
                            @elseif($exceptionCase->status == 'resolved')
                                <span class="badge bg-success">已解决</span>
                            @else
                                <span class="badge bg-secondary">已忽略</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>关联单号</th>
                        <td>{{ $exceptionCase->related_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>错误信息</th>
                        <td><pre class="bg-light p-3 rounded">{{ $exceptionCase->error_message ?? '-' }}</pre></td>
                    </tr>
                    <tr>
                        <th>堆栈信息</th>
                        <td><pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">{{ $exceptionCase->stack_trace ?? '-' }}</pre></td>
                    </tr>
                    <tr>
                        <th>创建时间</th>
                        <td>{{ $exceptionCase->created_at ? $exceptionCase->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>更新时间</th>
                        <td>{{ $exceptionCase->updated_at ? $exceptionCase->updated_at->format('Y-m-d H:i:s') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($exceptionCase->resolution_note)
        <div class="card">
            <div class="card-header"><h5 class="mb-0">处理记录</h5></div>
            <div class="card-body">
                <p>{{ $exceptionCase->resolution_note }}</p>
                @if($exceptionCase->resolver)
                    <small class="text-muted">处理人：{{ $exceptionCase->resolver->name ?? '-' }}</small>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">分配处理</h5></div>
            <div class="card-body">
                @if($exceptionCase->status == 'open' || $exceptionCase->status == 'processing')
                <form method="POST" action="{{ route('soho.exceptions.assign', $exceptionCase) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">分配给</label>
                        <select name="assignee_id" class="form-select">
                            <option value="">选择处理人</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-user"></i> 分配
                    </button>
                </form>
                @else
                <p class="text-muted text-center">当前状态不可分配</p>
                @endif
            </div>
        </div>
    </div>
</div>

<a href="{{ route('soho.exceptions.index') }}" class="btn btn-secondary mt-3">
    <i class="fa fa-arrow-left"></i> 返回列表
</a>
@endsection