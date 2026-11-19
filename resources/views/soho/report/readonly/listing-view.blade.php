@extends('soho::layouts.app')

{{-- 只读 Listing 查看视图 --}}
@section('title', 'Listing 查看（只读）')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                Listing 查看
                <span class="badge bg-secondary ms-2">只读</span>
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">只读报表</li>
                    <li class="breadcrumb-item active">Listing 查看</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.readonly.listing.view') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">平台</label>
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        @foreach($platformOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('platform') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">状态</label>
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.readonly.listing.view') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>Listing 总数</small>
                    <h3 class="mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>在售数</small>
                    <h3 class="mb-0">{{ number_format($stats['online'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>下架数</small>
                    <h3 class="mb-0">{{ number_format($stats['offline'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>审核中</small>
                    <h3 class="mb-0">{{ number_format($stats['review'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 数据表格 --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>商品名称</th>
                        <th>平台</th>
                        <th>店铺</th>
                        <th>平台商品ID</th>
                        <th class="text-end">价格</th>
                        <th class="text-end">库存</th>
                        <th class="text-center">状态</th>
                        <th>更新时间</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusColors = [
                            'online' => 'success', 'offline' => 'danger',
                            'review' => 'warning', 'rejected' => 'secondary',
                        ];
                    @endphp
                    @forelse($listings as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>{{ $item->product_name ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $item->platform }}</span></td>
                            <td>{{ $item->shop_name ?? '-' }}</td>
                            <td><code>{{ $item->platform_product_id ?? '-' }}</code></td>
                            <td class="text-end">¥{{ number_format($item->price ?? 0, 2) }}</td>
                            <td class="text-end">{{ $item->stock ?? 0 }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $statusColors[$item->status] ?? 'secondary' }}">
                                    {{ $statusOptions[$item->status] ?? $item->status }}
                                </span>
                            </td>
                            <td class="small">{{ optional($item->updated_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($listings->total() > 0)
            <div class="card-footer">{{ $listings->links() }}</div>
        @endif
    </div>
@endsection
