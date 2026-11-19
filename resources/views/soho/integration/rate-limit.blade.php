@extends('soho::layouts.app')

@section('title', '限流监控')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">涉及平台</div><div class="fs-4 fw-semibold">{{ $stats['platforms'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">今日调用量</div><div class="fs-4 fw-semibold">{{ $stats['today_calls'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">峰值（次/分钟）</div><div class="fs-4 fw-semibold">{{ $stats['peak'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">超阈值平台</div><div class="fs-4 fw-semibold text-{{ $stats['over_limit'] > 0 ? 'danger' : 'success' }}">{{ $stats['over_limit'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">今日各平台调用量</h5>
        <a href="{{ route('soho.integration.api.health') }}" class="btn btn-outline-secondary btn-sm">API 健康</a>
    </div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            阈值取自同步游标配置项 `rate_limit_per_minute`，未配置时按 20 次/分钟计算；超阈值平台优先排查是否为重复同步。
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center">平台</th>
                        <th class="text-center">今日调用</th>
                        <th class="text-center">成功</th>
                        <th class="text-center">阈值（次/分钟）</th>
                        <th class="text-center">批量大小</th>
                        <th class="text-center">同步间隔（秒）</th>
                        <th class="text-center">状态</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perPlatform as $p)
                        @php
                            $limit = $limits[$p->platform]['per_minute'] ?? 20;
                            $over = $p->calls > $limit * 60;
                        @endphp
                        <tr>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($p->platform) }}</span></td>
                            <td class="text-center">{{ $p->calls }}</td>
                            <td class="text-center text-success">{{ $p->success }}</td>
                            <td class="text-center">{{ $limit }}</td>
                            <td class="text-center">{{ $limits[$p->platform]['batch_size'] ?? '—' }}</td>
                            <td class="text-center">{{ $limits[$p->platform]['interval'] ?? '—' }}</td>
                            <td class="text-center"><span class="badge bg-{{ $over ? 'danger' : 'success' }}">{{ $over ? '超阈值' : '正常' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">今日暂无接口调用。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">分钟级峰值 Top 15</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead><tr><th class="text-center">平台</th><th class="text-center">时间（分钟）</th><th class="text-center">调用次数</th></tr></thead>
                <tbody>
                    @forelse($peaks as $peak)
                        <tr>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($peak->platform) }}</span></td>
                            <td class="text-center small">{{ $peak->minute }}</td>
                            <td class="text-center">{{ $peak->calls }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">暂无分钟级数据。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
