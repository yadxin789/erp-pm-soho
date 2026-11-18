@extends('soho::layouts.app')
@section('title', '同步任务详情')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">同步任务详情 #{{ $syncTask->id }}</h5>
        <a href="{{ route('soho.sync-task.index') }}" class="btn btn-secondary btn-sm">返回列表</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr><th width="120">ID</th><td>{{ $syncTask->id }}</td></tr>
            <tr><th>店铺</th><td>{{ $syncTask->shop->name ?? '-' }}</td></tr>
            <tr><th>任务类型</th><td>{{ $syncTask->task_type }}</td></tr>
            <tr><th>状态</th><td>
                @php
                    $statusMap = ['pending' => '待执行', 'running' => '执行中', 'success' => '成功', 'failed' => '失败'];
                @endphp
                {{ $statusMap[$syncTask->status] ?? $syncTask->status }}
            </td></tr>
            <tr><th>计划时间</th><td>{{ $syncTask->scheduled_at?->format('Y-m-d H:i:s') ?? '-' }}</td></tr>
            <tr><th>开始时间</th><td>{{ $syncTask->started_at?->format('Y-m-d H:i:s') ?? '-' }}</td></tr>
            <tr><th>完成时间</th><td>{{ $syncTask->completed_at?->format('Y-m-d H:i:s') ?? '-' }}</td></tr>
            <tr><th>错误信息</th><td>{{ $syncTask->error_message ?? '-' }}</td></tr>
            <tr><th>结果</th><td><pre class="bg-light p-2">{{ $syncTask->result ?? '-' }}</pre></td></tr>
            <tr><th>创建时间</th><td>{{ $syncTask->created_at?->format('Y-m-d H:i:s') }}</td></tr>
        </table>
        @if(in_array($syncTask->status, ['pending', 'failed']))
            <form method="POST" action="{{ route('soho.sync-task.execute', $syncTask) }}">
                @csrf
                <button type="submit" class="btn btn-primary">执行此任务</button>
            </form>
        @endif
    </div>
</div>
@endsection
