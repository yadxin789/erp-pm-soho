@extends('soho::layouts.app')

@section('title', '待发货包裹列表')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">待发货包裹</li>
    </ol>
</nav>

{{-- 页面标题栏 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-truck me-2"></i>待发货包裹列表</h4>
    <div>
        <a href="{{ route('fulfillment.shipments.push-pending') }}" class="btn btn-outline-warning btn-sm me-2">
            <i class="fa fa-sync me-1"></i>待推送发货
        </a>
        <a href="{{ route('fulfillment.shipments.push-failed') }}" class="btn btn-outline-danger btn-sm me-2">
            <i class="fa fa-exclamation-circle me-1"></i>推送失败
        </a>
        <a href="{{ route('fulfillment.shipments.callback') }}" class="btn btn-outline-info btn-sm">
            <i class="fa fa-sign-in me-1"></i>发货回传
        </a>
    </div>
</div>

{{-- 统计卡片 --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">待发货</div>
                <div class="fs-3 text-primary">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">今日已发货</div>
                <div class="fs-3 text-success">{{ $stats['shipped'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">待推送</div>
                <div class="fs-3 text-warning">{{ $stats['push_pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">推送失败</div>
                <div class="fs-3 text-danger">{{ $stats['push_failed'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

{{-- 待发货包裹列表 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待发货包裹</h5>
        <div>
            <span class="badge bg-info me-2">共 {{ $parcels->total() }} 条</span>
            <button type="button" class="btn btn-success btn-sm" onclick="batchShip()">
                <i class="fa fa-truck me-1"></i>批量发货
            </button>
        </div>
    </div>
    <div class="card-body">
        {{-- 筛选表单 --}}
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="包裹号/物流单号/订单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="express_company_id" class="form-select">
                        <option value="">全部快递公司</option>
                        @foreach($expressCompanies as $company)
                            <option value="{{ $company->id }}" @selected(request('express_company_id') == $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}" placeholder="结束日期">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i>筛选
                    </button>
                    <a href="{{ route('fulfillment.shipments.pending') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>包裹号</th>
                        <th>订单号</th>
                        <th>快递公司</th>
                        <th>物流单号</th>
                        <th>重量</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parcels as $parcel)
                    <tr>
                        <td><input type="checkbox" name="parcel_ids[]" value="{{ $parcel->id }}"></td>
                        <td><code>{{ $parcel->parcel_no }}</code></td>
                        <td>{{ $parcel->order_no ?? '-' }}</td>
                        <td>{{ $parcel->express_company_name ?? '-' }}</td>
                        <td>{{ $parcel->tracking_no ?? '-' }}</td>
                        <td>{{ $parcel->charge_weight ?? 0 }} kg</td>
                        <td>
                            <span class="badge bg-primary">待发货</span>
                        </td>
                        <td>{{ optional($parcel->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('fulfillment.shipments.show', $parcel) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                            <button type="button" class="btn btn-sm btn-success" onclick="shipParcel({{ $parcel->id }})">
                                <i class="fa fa-truck"></i> 发货
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                            暂无待发货包裹
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $parcels->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('input[name="parcel_ids[]"]').forEach(cb => cb.checked = this.checked);
});

function shipParcel(id) {
    if (confirm('确认发货？发货后将自动推送到对应平台。')) {
        // 发货逻辑
    }
}

function batchShip() {
    const selected = document.querySelectorAll('input[name="parcel_ids[]"]:checked');
    if (selected.length === 0) {
        alert('请至少选择一个包裹');
        return;
    }
    if (confirm('确认批量发货选中的 ' + selected.length + ' 个包裹？')) {
        // 批量发货逻辑
    }
}
</script>
@endsection