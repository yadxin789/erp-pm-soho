@extends("pm::layouts.app")
@section("title", "甘特图 · ".$project->name)
@section("content")
@include("pm::project.partials.tabs", ["project" => $project, "activeTab" => "gantt"])
<div class="pm-panel">
    <div class="pm-panel-head"><h3><i class="fas fa-chart-bar" aria-hidden="true"></i>甘特图（{{ $tasks->count() }} 个任务）</h3></div>
    <div class="pm-panel-body">
        @if ($tasks->isEmpty())
            <div class="pm-empty"><i class="fas fa-chart-bar" aria-hidden="true"></i>该项目暂无任务</div>
        @else
            @php
                $min = $tasks->min("start_date"); $max = $tasks->max("end_date");
                $start = $min ? \Carbon\Carbon::parse($min) : now();
                $end = $max ? \Carbon\Carbon::parse($max) : now()->addDays(30);
                $days = max(1, $start->diffInDays($end) + 1);
            @endphp
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th style="width:26%">任务</th><th style="width:12%">执行人</th><th>时间轴（{{ $start->format("Y-m-d") }} ~ {{ $end->format("Y-m-d") }}）</th></tr></thead>
                    <tbody>
                    @foreach ($tasks as $task)
                        @php
                            $s = $task->start_date ? \Carbon\Carbon::parse($task->start_date) : $start;
                            $e = $task->end_date ? \Carbon\Carbon::parse($task->end_date) : $s;
                            $offset = max(0, $start->diffInDays($s, false));
                            $width = max(1, $s->diffInDays($e) + 1);
                        @endphp
                        <tr>
                            <td>{{ $task->name }}</td>
                            <td>{{ $task->executor?->displayName() ?: "—" }}</td>
                            <td>
                                <div style="position:relative;height:16px;background:#f4f5f9;border-radius:3px">
                                    <div title="{{ $s->format("m-d") }} ~ {{ $e->format("m-d") }}"
                                         style="position:absolute;left:{{ $offset / $days * 100 }}%;width:{{ $width / $days * 100 }}%;height:16px;border-radius:3px;background:{{ $task->statusColor() }}"></div>
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
@endsection