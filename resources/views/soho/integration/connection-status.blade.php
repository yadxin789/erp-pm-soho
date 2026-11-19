@extends('soho::layouts.app')

@section('title', '店铺连接状态')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">同步游标总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">同步中</div><div class="fs-4 fw-semibold text-primary">{{ $stats['syncing'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">异常</div><div class="fs-4 fw-semibold text-danger">{{ $stats['error'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已停用</div><div class="fs-4 fw-semibold text-secondary">{{ $stats['disabled'] }}</div>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">店铺 × 同步类型 连接状态</h5>
        <a href="{{ route('soho.integration.api.health') }}" class="btn btn-outline-secondary btn-sm">API 健康</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        @foreach($platforms as $p)
                            <option value="{{ $p }}" @selected(request('platform') === $p)>{{ strtoupper($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sync_type" class="form-select">
                        <option value="">全部类型</option>
                        @foreach($syncTypes as $ty)
                            <option value="{{ $ty }}" @selected(request('sync_type') === $ty)>{{ $ty }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['idle' => '空闲', 'syncing' => '同步中', 'error' => '异常', 'disabled' => '停用'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.integration.connection.status') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>店铺</th>
                        <th class="text-center">平台</th>
                        <th class="text-center">同步类型</th>
                        <th class="text-center">模式</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">最近成功</th>
                        <th class="text-center">连续失败</th>
                        <th class="text-center">下次同步</th>
                        <th>最近错误</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $st = ['idle' => ['空闲', 'secondary'], 'syncing' => ['同步中', 'primary'], 'error' => ['异常', 'danger'], 'disabled' => ['停用', 'dark']]; @endphp
                        <tr>
                            <td>{{ $shops[$row->shop_id]->shop_name ?? '店铺#'.$row->shop_id }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($row->platform_code) }}</span></td>
                            <td class="text-center">{{ $row->sync_type }}</td>
                            <td class="text-center small text-muted">{{ $row->sync_mode }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="text-center small text-muted">{{ $row->last_success_at ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $row->consecutive_failures > 0 ? 'danger' : 'secondary' }}">{{ $row->consecutive_failures }}</span>
                            </td>
                            <td class="text-center small text-muted">{{ $row->next_sync_at ?: '—' }}</td>
                            <td class="small text-danger">{{ $row->last_error_message ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">暂无同步游标记录。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
