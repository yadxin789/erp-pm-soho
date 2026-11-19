@extends('soho::layouts.app')

@section('title', '活动报名')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">报名记录</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待同步</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已同步</div><div class="fs-4 fw-semibold text-success">{{ $stats['synced'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">同步失败</div><div class="fs-4 fw-semibold text-danger">{{ $stats['failed'] }}</div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">提交平台活动报名</h5></div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            报名后生成一条「促销 ↔ 平台活动」映射，状态为待同步，由平台同步任务推送至对应店铺。
        </div>
        <form method="POST" action="{{ route('soho.marketing.campaign.join') }}">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">促销活动</label>
                    <select name="promotion_id" class="form-select" required>
                        @foreach($promotions as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">目标店铺</label>
                    <select name="shop_id" class="form-select" required>
                        @foreach($shops as $s)
                            <option value="{{ $s->id }}">{{ $s->shop_name }}（{{ strtoupper($s->platform) }}）</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">平台</label>
                    <select name="platform" class="form-select" required>
                        @foreach(['taobao' => '淘宝', 'jd' => '京东', 'pinduoduo' => '拼多多', 'amazon' => 'Amazon'] as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane me-1"></i> 提交报名</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">报名记录与平台同步</h5>
        <a href="{{ route('soho.marketing.campaign.rule') }}" class="btn btn-outline-secondary btn-sm">活动规则</a>
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
                    <select name="sync_status" class="form-select">
                        <option value="">全部同步状态</option>
                        @foreach(['pending' => '待同步', 'syncing' => '同步中', 'synced' => '已同步', 'failed' => '同步失败'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('sync_status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.marketing.campaign.join') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>促销活动</th>
                        <th class="text-center">平台</th>
                        <th>店铺</th>
                        <th class="font-monospace">平台活动 ID</th>
                        <th class="text-center">同步状态</th>
                        <th class="text-center">最近同步</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $st = ['pending' => ['待同步', 'warning'], 'syncing' => ['同步中', 'info'], 'synced' => ['已同步', 'success'], 'failed' => ['同步失败', 'danger']];
                            $promo = $promotions->firstWhere('id', $row->promotion_id);
                        @endphp
                        <tr>
                            <td>{{ $promo->name ?? '促销#'.$row->promotion_id }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($row->platform) }}</span></td>
                            <td>{{ $shops[$row->shop_id]->shop_name ?? '店铺#'.$row->shop_id }}</td>
                            <td class="small text-muted">{{ $row->platform_promotion_id ?: '—' }}</td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->sync_status][1] ?? 'secondary' }}">{{ $st[$row->sync_status][0] ?? $row->sync_status }}</span></td>
                            <td class="text-center small text-muted">{{ $row->last_sync_at ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">暂无报名记录。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
