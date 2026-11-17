@extends('layouts.business')

@section('title', '项目履历')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clock-rotate-left"></i>{{ $project->name }} · 项目履历</h2>
        <a href="{{ erp_route('app.projects.show', $project) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回详情
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 160px;">时间</th>
                            <th style="width: 120px;">操作人</th>
                            <th style="width: 140px;">动作</th>
                            <th>内容</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="small text-muted">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $log->user?->name ?: '系统' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $log->action_label }}</span>
                                </td>
                                <td class="small">
                                    {{ $log->description }}
                                    @if(! empty($log->meta['from']) || ! empty($log->meta['to']))
                                        <div class="text-muted">
                                            {{ $log->meta['from'] ?? '—' }} → {{ $log->meta['to'] ?? '—' }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="erp-empty">暂无履历</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">{{ $logs->links() }}</div>
        </div>
    </div>
@endsection
