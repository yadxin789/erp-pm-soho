@extends('soho::layouts.app')

@section('title', '商品版本')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">版本总数</div>
                <div class="fs-4 fw-semibold">{{ $stats['total'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">今日新增</div>
                <div class="fs-4 fw-semibold text-primary">{{ $stats['today'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">商品版本历史</h5>
        <a href="{{ route('soho.product.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 返回商品
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索版本号 / 标题" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach($changeTypeOptions as $key => $label)
                            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.product.versions') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>商品</th>
                        <th>版本号</th>
                        <th>标题</th>
                        <th class="text-end">价格</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">发布时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($versions as $version)
                        <tr>
                            <td>{{ $version->id }}</td>
                            <td>
                                {{ $version->product_name ?? '-' }}
                                @if($version->product_code ?? null)
                                    <span class="text-muted small d-block font-monospace">{{ $version->product_code }}</span>
                                @endif
                            </td>
                            <td class="font-monospace">{{ $version->version_no }}</td>
                            <td>{{ $version->title ?: '-' }}</td>
                            <td class="text-end">¥{{ number_format((float) $version->price, 2) }}</td>
                            <td class="text-center">
                                @php
                                    $statusMap = [
                                        'draft' => ['草稿', 'secondary'],
                                        'published' => ['已发布', 'success'],
                                        'offline' => ['已下架', 'warning'],
                                    ];
                                    [$label, $class] = $statusMap[$version->status] ?? [$version->status ?: '未知', 'light'];
                                @endphp
                                <span class="badge bg-{{ $class }}">{{ $label }}</span>
                            </td>
                            <td class="text-center text-muted small">{{ $version->published_at ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">暂无版本记录</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $versions->links() }}
    </div>
</div>
@endsection
