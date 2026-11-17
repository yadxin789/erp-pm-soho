@extends("pm::layouts.app")
@section("title", "项目履历 · ".$project->name)
@section("content")
@include("pm::project.partials.tabs", ["project" => $project, "activeTab" => "history"])
<div class="pm-panel">
    <div class="pm-panel-head"><h3><i class="fas fa-history" aria-hidden="true"></i>项目履历</h3></div>
    <div class="pm-panel-body p-0">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light"><tr><th>任务</th><th>部门</th><th>执行人</th><th>审核人</th><th>状态</th><th>完成时间</th></tr></thead>
            <tbody>
            @forelse ($tasks as $task)
                <tr>
                    <td>{{ $task->name }}</td>
                    <td>{{ $task->department?->name ?: "—" }}</td>
                    <td>{{ $task->executor?->displayName() ?: "—" }}</td>
                    <td>{{ $task->auditor?->displayName() ?: "—" }}</td>
                    <td><span class="pm-status" style="background:{{ $task->statusColor() }}">{{ $task->statusLabel() }}</span></td>
                    <td>{{ $task->completed_at?->format("Y-m-d H:i") ?: "—" }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">暂无履历</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection