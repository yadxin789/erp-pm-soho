@extends('soho::layouts.app')
@section('title', '包裹管理')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">包裹列表</h5>
        <a href="{{ route('soho.package.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> 创建包裹</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="包裹号/物流单号" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="pending" @selected(request('status') == 'pending')>待发货</option>
                        <option value="shipped" @selected(request('status') == 'shipped')>已发货</option>
                        <option value="delivered" @selected(request('status') == 'delivered')>已送达</option>
                        <option value="cancelled" @selected(request('status') == 'cancelled')>已取消</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="express_company_id" class="form-select">
                        <option value="">全部快递公司</option>
                        @foreach(\App\Soho\Models\ExpressCompany::where('status', true)->orderBy('sort_order')->get() as $company)
                        <option value="{{ $company->id }}" @selected(request('express_company_id') == $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.package.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>包裹号</th>
                        <th>订单号</th>
                        <th>快递公司</th>
                        <th>物流单号</th>
                        <th>重量(kg)</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                    <tr>
                        <td>{{ $package->package_no }}</td>
                        <td>{{ $package->order->order_number ?? '-' }}</td>
                        <td>{{ $package->expressCompany->name ?? '-' }}</td>
                        <td>{{ $package->tracking_no ?? '-' }}</td>
                        <td>{{ $package->weight ?? '-' }}</td>
                        <td>
                            @php
                                $statusMap = [
                                    'pending' => 'warning',
                                    'shipped' => 'info',
                                    'delivered' => 'success',
                                    'cancelled' => 'secondary',
                                ];
                                $statusLabelMap = [
                                    'pending' => '待发货',
                                    'shipped' => '已发货',
                                    'delivered' => '已送达',
                                    'cancelled' => '已取消',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusMap[$package->status] ?? 'secondary' }}">
                                {{ $statusLabelMap[$package->status] ?? $package->status }}
                            </span>
                        </td>
                        <td>{{ $package->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('soho.package.index', $package) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                            <a href="{{ route('soho.package.edit', $package) }}" class="btn btn-sm btn-outline-success">
                                <i class="fa fa-edit"></i> 编辑
                            </a>
                            <form method="POST" action="{{ route('package.destroy', $package) }}" class="d-inline" onsubmit="return confirm('确定删除此包裹？')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i> 删除
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $packages->links() }}
    </div>
</div>
@endsection