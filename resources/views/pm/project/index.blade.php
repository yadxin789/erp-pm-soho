@extends('pm::layouts.app')

@section('title', '项目列表')

@section('content')
<div class="pm-panel">
    <div class="pm-panel-head">
        <h3><i class="fas fa-folder-open" aria-hidden="true"></i>项目列表</h3>
        <span class="text-muted small">共 {{ $stats['total'] }} 个项目 · 进行中 {{ $stats['in_progress'] }} · 暂停 {{ $stats['suspended'] }}</span>
    </div>
    <div class="pm-panel-body">
        @if ($projects->isEmpty())
            <div class="pm-empty"><i class="fas fa-folder-open" aria-hidden="true"></i>暂无项目</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                    <tr><th>项目名称</th><th>项目编码</th><th>产品型号</th><th>状态</th><th>进度</th><th>开始</th><th>结束</th><th></th></tr>
                    </thead>
                    <tbody>
                    @foreach ($projects as $p)
                        <tr>
                            <td><a href="{{ route('pm.project.show', $p) }}">{{ $p->name }}</a></td>
                            <td>{{ $p->code }}</td>
                            <td>{{ $p->product_model ?: '—' }}</td>
                            <td><span class="pm-status" style="background:{{ $p->statusColor() }}">{{ $p->statusLabel() }}</span></td>
                            <td style="min-width:120px">
                                <div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ (float) $p->progress }}%"></div></div>
                                <span class="small text-muted">{{ (float) $p->progress }}%</span>
                            </td>
                            <td>{{ $p->start_date?->format('Y-m-d') }}</td>
                            <td>{{ $p->end_date?->format('Y-m-d') }}</td>
                            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('pm.project.show', $p) }}">进入</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
