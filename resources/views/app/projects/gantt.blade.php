@extends('layouts.business')

@section('title', '甘特图')

@section('content')
    @php
        $tasks = $tasks ?? collect();
        $statusLabels = $statusLabels ?? \App\Erp\Models\Project\ProjectTask::STATUS;
        $rangeStart = $project->start_date ?: $tasks->filter(fn ($t) => $t->start_date)->min('start_date');
        $rangeEnd = $project->end_date ?: $tasks->filter(fn ($t) => $t->end_date)->max('end_date');
        $rangeStart = $rangeStart ? \Carbon\Carbon::parse($rangeStart) : now();
        $rangeEnd = $rangeEnd ? \Carbon\Carbon::parse($rangeEnd) : now()->addDays(14);
        if ($rangeEnd->lt($rangeStart)) { $rangeEnd = $rangeStart->copy()->addDays(14); }
        $totalDays = $rangeStart->copy()->startOfDay()->diffInDays($rangeEnd->copy()->startOfDay()) + 1;
        $totalDays = max(1, min($totalDays, 400));
        $cellWidth = $totalDays > 120 ? 14 : 26;

        $statusBg = [
            'pending' => '#e2e3e5',
            'in_progress' => '#0d6efd',
            'for_audit' => '#0dcaf0',
            'completed' => '#198754',
            'overdue' => '#dc3545',
            'overdue_completed' => '#fd7e14',
            'rejected' => '#343a40',
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-chart-gantt"></i>{{ $project->name }} · 甘特图</h2>
        <div class="d-flex gap-2">
            <a href="{{ erp_route('app.projects.show', $project) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>返回详情
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="small text-muted">
                {{ $rangeStart->format('Y-m-d') }} ~ {{ $rangeEnd->format('Y-m-d') }}（{{ $totalDays }} 天）
            </span>
            <span class="d-flex flex-wrap gap-2 small">
                @foreach($statusLabels as $key => $label)
                    <span class="d-inline-flex align-items-center gap-1">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:{{ $statusBg[$key] ?? '#adb5bd' }}"></span>
                        {{ $label }}
                    </span>
                @endforeach
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="min-width: {{ 420 + $totalDays * $cellWidth }}px;">
                    <thead>
                        <tr>
                            <th style="position: sticky; left: 0; background: var(--bs-body-bg); z-index: 2; min-width: 240px;">任务</th>
                            <th style="position: sticky; left: 240px; background: var(--bs-body-bg); z-index: 2; min-width: 90px;">执行人</th>
                            @for($i = 0; $i < $totalDays; $i++)
                                @php $day = $rangeStart->copy()->startOfDay()->addDays($i); @endphp
                                <th class="text-center small {{ $day->isWeekend() ? 'text-danger' : 'text-muted' }}"
                                    style="min-width: {{ $cellWidth }}px; padding: 2px; font-weight: 400;">
                                    {{ $day->format('d') }}
                                </th>
                            @endfor
                        </tr>
                        <tr>
                            <th colspan="2" class="small text-muted" style="position: sticky; left: 0; background: var(--bs-body-bg);">月份</th>
                            @for($i = 0; $i < $totalDays; $i++)
                                @php $day = $rangeStart->copy()->startOfDay()->addDays($i); @endphp
                                <th class="text-center small text-muted" style="padding: 2px; font-weight: 400;">
                                    {{ $day->day === 1 || $i === 0 ? $day->format('m月') : '' }}
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            @php
                                $barStart = $task->start_date ? \Carbon\Carbon::parse($task->start_date) : $rangeStart->copy();
                                $barEnd = $task->end_date ? \Carbon\Carbon::parse($task->end_date) : $barStart->copy();
                                $offset = max(0, $rangeStart->copy()->startOfDay()->diffInDays($barStart->copy()->startOfDay(), false));
                                $length = max(1, $barStart->copy()->startOfDay()->diffInDays($barEnd->copy()->startOfDay()) + 1);
                                if ($offset >= $totalDays) { $offset = $totalDays - 1; }
                                $length = min($length, $totalDays - $offset);
                            @endphp
                            <tr>
                                <td class="small fw-semibold" style="position: sticky; left: 0; background: var(--bs-body-bg);">
                                    <a href="{{ erp_route('app.tasks.show', $task) }}" class="text-decoration-none">{{ $task->name }}</a>
                                    <span class="badge bg-{{ $task->status_color }}-subtle text-{{ $task->status_color }}-emphasis border border-{{ $task->status_color }}-subtle ms-1">
                                        {{ $task->status_label }}
                                    </span>
                                </td>
                                <td class="small" style="position: sticky; left: 240px; background: var(--bs-body-bg);">
                                    {{ $task->executor?->name ?: '—' }}
                                </td>
                                @for($i = 0; $i < $totalDays; $i++)
                                    <td style="padding: 0; position: relative; height: 30px;">
                                        @if($i === $offset)
                                            <span title="{{ $task->name }}（{{ $task->start_date ?: '未定' }} ~ {{ $task->end_date ?: '未定' }}）"
                                                  style="position:absolute; left:0; top:6px; height:16px; width:{{ $length * $cellWidth }}px; border-radius:3px; background:{{ $statusBg[$task->status] ?? '#adb5bd' }}; opacity:.85;"></span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @empty
                            <tr><td colspan="{{ $totalDays + 2 }}" class="erp-empty">暂无任务</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
