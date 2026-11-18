@extends('soho::layouts.app')

{{-- 指标定义管理视图 --}}
@section('title', '指标定义管理')

@section('content')
    {{-- 页面标题与面包屑导航 --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">指标定义管理</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
                    <li class="breadcrumb-item">数据报表</li>
                    <li class="breadcrumb-item active">指标定义</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 搜索/筛选表单 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('soho.report.metrics.definition') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">指标分类</label>
                    <select name="category" class="form-select">
                        <option value="">全部分类</option>
                        @foreach($categoryOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('category') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">关键词</label>
                    <input type="text" name="keyword" class="form-control" placeholder="指标名称 / 代码 / 描述" value="{{ request('keyword') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 查询</button>
                    <a href="{{ route('soho.report.metrics.definition') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 统计卡片 --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <small>指标总数</small>
                    <h3 class="mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <small>销售指标</small>
                    <h3 class="mb-0">{{ number_format($stats['sales'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <small>订单指标</small>
                    <h3 class="mb-0">{{ number_format($stats['order'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <small>售后指标</small>
                    <h3 class="mb-0">{{ number_format($stats['aftersale'] ?? 0) }}</h3>
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
                        <th>指标名称</th>
                        <th>指标代码</th>
                        <th>分类</th>
                        <th>计算公式</th>
                        <th>单位</th>
                        <th>描述</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td><span class="fw-bold">{{ $item->name ?? $item['name'] ?? '-' }}</span></td>
                            <td><code>{{ $item->code ?? $item['code'] ?? '-' }}</code></td>
                            <td>
                                @php
                                    $cat = $item->category ?? $item['category'] ?? '';
                                    $catColor = [
                                        'sales' => 'success', 'order' => 'info',
                                        'aftersale' => 'warning', 'inventory' => 'primary',
                                        'logistics' => 'secondary', 'finance' => 'danger',
                                    ][$cat] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $catColor }}">{{ $categoryOptions[$cat] ?? $cat }}</span>
                            </td>
                            <td><code class="small">{{ $item->formula ?? $item['formula'] ?? '-' }}</code></td>
                            <td>{{ $item->unit ?? $item['unit'] ?? '-' }}</td>
                            <td class="small text-muted">{{ $item->description ?? $item['description'] ?? '-' }}</td>
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
        @if($items->hasPages())
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
