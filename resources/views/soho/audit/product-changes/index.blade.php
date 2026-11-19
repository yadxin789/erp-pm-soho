@extends('soho::layouts.app')

{{-- 商品变更审计列表视图 --}}
@section('title', '商品变更审计')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">商品变更审计</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item">审计中心</li>
                    <li class="breadcrumb-item active">商品变更审计</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.audit.product-change') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">关键词</label>
                        <input type="text" class="form-control" name="search" placeholder="商品名称/变更字段/操作人" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">操作类型</label>
                        <select class="form-select" name="operation_type">
                            <option value="">全部</option>
                            @foreach($operationTypeOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('operation_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">操作人</label>
                        <input type="text" class="form-control" name="operator" placeholder="操作人" value="{{ request('operator') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">开始日期</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">结束日期</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('soho.audit.product-change') }}" class="btn btn-outline-secondary btn-sm">重置筛选</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>变更总数</small>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>新增</small>
                    <h3 class="mb-0">{{ $stats['create'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>修改</small>
                    <h3 class="mb-0">{{ $stats['update'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <small>删除</small>
                    <h3 class="mb-0">{{ $stats['delete'] ?? 0 }}</h3>
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
                        <th>变更字段</th>
                        <th>旧值</th>
                        <th>新值</th>
                        <th>操作人</th>
                        <th>操作时间</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 操作类型对应的 badge 颜色 --}}
                    @php
                        $operationColors = [
                            'create' => 'success',
                            'update' => 'warning',
                            'delete' => 'danger',
                        ];
                    @endphp
                    @forelse($productChanges as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $productChanges->firstItem() + $index }}</td>
                            <td>{{ $item->product_name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $operationColors[$item->operation_type] ?? 'secondary' }}">
                                    {{ $operationTypeOptions[$item->operation_type] ?? $item->operation_type ?? '-' }}
                                </span>
                                <span class="ms-1 small">{{ $item->changed_field ?? '-' }}</span>
                            </td>
                            <td><code class="small text-danger">{{ $item->old_value ?? '-' }}</code></td>
                            <td><code class="small text-success">{{ $item->new_value ?? '-' }}</code></td>
                            <td>{{ $item->operator ?? '-' }}</td>
                            <td class="small">{{ $item->operated_at ? \Carbon\Carbon::parse($item->operated_at)->format('Y-m-d H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2"></i>
                                <div>暂无数据</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- 分页 --}}
        @if($productChanges->total() > 0)
            <div class="card-footer">{{ $productChanges->links() }}</div>
        @endif
    </div>
@endsection
