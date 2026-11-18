@extends('soho::layouts.app')

@section('title', '待推送发货列表')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">待推送发货</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-sync me-2"></i>待推送发货列表</h4>
    <div>
        <a href="{{ route('fulfillment.shipments.pending') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-clock me-1"></i>待发货包裹
        </a>
        <button type="button" class="btn btn-success btn-sm" onclick="pushAll()">
            <i class="fa fa-sync me-1"></i>一键推送全部
        </button>
    </div>
</div>

{{-- 推送说明 --}}
<div class="alert alert-info">
    <i class="fa fa-info-circle me-1"></i>
    <strong>说明：</strong>以下包裹已在本地生成，等待推送到对应电商平台完成发货确认。请确认信息无误后点击推送。
</div>

{{-- 待推送列表 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">待推送包裹</h5>
        <div>
            <span class="badge bg-warning text-dark">{{ $pushPending->total() }} 条待推送</span>
        </div>
    </div>
    <div class="card-body">
        {{-- 筛选表单 --}}
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="包裹号/物流单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        <option value="taobao" @selected(request('platform') == 'taobao')>淘宝</option>
                        <option value="jd" @selected(request('platform') == 'jd')>京东</option>
                        <option value="pinduoduo" @selected(request('platform') == 'pinduoduo')>拼多多</option>
                        <option value="amazon" @selected(request('platform') == 'amazon')>亚马逊</option>
                    </select>
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
                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}" placeholder="开始日期">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}" placeholder="结束日期">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i>筛选
                    </button>
                    <a href="{{ route('fulfillment.shipments.push-pending') }}" class="btn btn-outline-secondary">重置</a>
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
                        <th>平台</th>
                        <th>快递公司</th>
                        <th>物流单号</th>
                        <th>推送状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pushPending as $parcel)
                    <tr>
                        <td><input type="checkbox" name="parcel_ids[]" value="{{ $parcel->id }}"></td>
                        <td><code>{{ $parcel->parcel_no }}</code></td>
                        <td>{{ $parcel->order_no ?? '-' }}</td>
                        <td>
                            @if($parcel->platform == 'taobao')
                                <span class="badge bg-warning text-dark">淘宝</span>
                            @elseif($parcel->platform == 'jd')
                                <span class="badge bg-danger">京东</span>
                            @elseif($parcel->platform == 'pinduoduo')
                                <span class="badge bg-info">拼多多</span>
                            @elseif($parcel->platform == 'amazon')
                                <span class="badge bg-success">亚马逊</span>
                            @else
                                <span class="badge bg-secondary">{{ $parcel->platform }}</span>
                            @endif
                        </td>
                        <td>{{ $parcel->express_company_name ?? '-' }}</td>
                        <td>{{ $parcel->tracking_no ?? '-' }}</td>
                        <td>
                            <span class="badge bg-warning text-dark">待推送</span>
                        </td>
                        <td>{{ optional($parcel->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-success" onclick="pushParcel({{ $parcel->id }})">
                                <i class="fa fa-sync"></i> 推送
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-check-circle fa-2x mb-2 d-block"></i>
                            暂无待推送的包裹
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $pushPending->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('input[name="parcel_ids[]"]').forEach(cb => cb.checked = this.checked);
});

function pushParcel(id) {
    if (confirm('确认推送该包裹到对应平台？')) {
        // 推送逻辑
    }
}

function pushAll() {
    if (confirm('确认一键推送全部待推送包裹？')) {
        // 批量推送逻辑
    }
}
</script>
@endsection