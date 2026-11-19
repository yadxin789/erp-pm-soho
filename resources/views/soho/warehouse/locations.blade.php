@extends('soho::layouts.app')

@section('title', '库位管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">库位列表</h5>
        <a href="{{ route('soho.warehouse.zones') }}" class="btn btn-outline-secondary btn-sm">库区一览</a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="zone" class="form-select">
                        <option value="">全部库区</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone }}" @selected(request('zone') === $zone)>{{ $zone ?: '未分区' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="库位编码" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.warehouse.locations') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>库位编码</th>
                        <th>仓库</th>
                        <th>库区</th>
                        <th class="text-center">类型</th>
                        <th class="text-end">容量</th>
                        <th class="text-end">占用</th>
                        <th class="text-center">状态</th>
                        <th>备注</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="font-monospace small">{{ $row->location_code }}</td>
                            <td>{{ $warehouses[$row->warehouse_id]->name ?? '仓库#'.$row->warehouse_id }}</td>
                            <td>{{ $row->zone_name ?: '未分区' }}</td>
                            <td class="text-center small">{{ $row->location_type ?: '-' }}</td>
                            <td class="text-end">{{ number_format($row->capacity) }}</td>
                            <td class="text-end">{{ number_format($row->current_usage) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ in_array($row->status, ['1', 1], true) ? 'success' : 'secondary' }}">
                                    {{ in_array($row->status, ['1', 1], true) ? '启用' : '停用' }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $row->remark ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">暂无库位。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>
@endsection
