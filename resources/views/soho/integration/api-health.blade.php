@extends('soho::layouts.app')

@section('title', 'API 健康检查')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">接口调用总数</div><div class="fs-4 fw-semibold">{{ $stats['calls'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">近 24h 调用</div><div class="fs-4 fw-semibold">{{ $stats['calls_24h'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">近 24h 失败</div><div class="fs-4 fw-semibold text-danger">{{ $stats['failure_24h'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">平均响应</div><div class="fs-4 fw-semibold">{{ $stats['avg_ms'] }} <span class="fs-6 text-muted">ms</span></div>
    </div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">平台接口健康度</h5>
                <a href="{{ route('soho.integration.connection.status') }}" class="btn btn-outline-secondary btn-sm">连接状态</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">平台</th>
                                <th class="text-center">调用量</th>
                                <th class="text-center">成功</th>
                                <th class="text-center">失败</th>
                                <th class="text-center">成功率</th>
                                <th class="text-center">平均耗时</th>
                                <th class="text-center">峰值耗时</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($perPlatform as $p)
                                @php $rate = $p->total > 0 ? round($p->success / $p->total * 100, 1) : 0; @endphp
                                <tr>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($p->platform) }}</span></td>
                                    <td class="text-center">{{ $p->total }}</td>
                                    <td class="text-center text-success">{{ $p->success }}</td>
                                    <td class="text-center text-danger">{{ $p->failure }}</td>
                                    <td class="text-center"><span class="badge bg-{{ $rate >= 95 ? 'success' : ($rate >= 80 ? 'warning' : 'danger') }}">{{ $rate }}%</span></td>
                                    <td class="text-center">{{ (int) $p->avg_ms }} ms</td>
                                    <td class="text-center">{{ (int) $p->max_ms }} ms</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">暂无接口调用日志。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Token 刷新结果</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead><tr><th class="text-center">平台</th><th class="text-center">结果</th><th class="text-center">次数</th></tr></thead>
                        <tbody>
                            @forelse($tokens as $t)
                                <tr>
                                    <td class="text-center">{{ strtoupper($t->platform) }}</td>
                                    <td class="text-center"><span class="badge bg-{{ $t->result === 'success' ? 'success' : 'danger' }}">{{ $t->result === 'success' ? '成功' : '失败' }}</span></td>
                                    <td class="text-center">{{ $t->cnt }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">暂无 Token 刷新记录。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">最近失败调用</h5>
        <form method="GET" class="d-flex gap-2">
            <select name="platform" class="form-select form-select-sm" style="width:140px">
                <option value="">全部平台</option>
                @foreach($perPlatform as $p)
                    <option value="{{ $p->platform }}" @selected($platformFilter === $p->platform)>{{ strtoupper($p->platform) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">筛选</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center">平台</th>
                        <th>接口方法</th>
                        <th class="text-center">结果</th>
                        <th>错误信息</th>
                        <th class="text-center">耗时</th>
                        <th class="font-monospace">Trace ID</th>
                        <th class="text-center">时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($failures as $f)
                        <tr>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($f->platform) }}</span></td>
                            <td class="small font-monospace">{{ $f->api_method }}</td>
                            <td class="text-center"><span class="badge bg-danger">{{ $f->response_status }}</span></td>
                            <td class="small text-danger">{{ $f->error_message ?: '—' }}</td>
                            <td class="text-center">{{ (int) $f->response_time_ms }} ms</td>
                            <td class="small text-muted">{{ $f->trace_id }}</td>
                            <td class="text-center small text-muted">{{ $f->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <i class="fa fa-circle-check text-success d-block fs-4 mb-2"></i>
                            近期没有失败的接口调用。
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
