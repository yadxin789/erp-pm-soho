@extends("pm::layouts.app")
@section("title", "项目日志 · ".$project->name)
@section("content")
@include("pm::project.partials.tabs", ["project" => $project, "activeTab" => "logs"])
<div class="pm-panel">
    <div class="pm-panel-head"><h3><i class="fas fa-list-alt" aria-hidden="true"></i>项目日志</h3></div>
    <div class="pm-panel-body p-0">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light"><tr><th style="width:160px">时间</th><th style="width:140px">操作人</th><th style="width:160px">动作</th><th>内容</th></tr></thead>
            <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td>{{ $log->created_at?->format("Y-m-d H:i") }}</td>
                    <td>{{ $log->user?->displayName() ?: "系统" }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->content }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">暂无日志</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
        <div class="pm-panel-body">{{ $logs->links() }}</div>
    @endif
</div>
@endsection