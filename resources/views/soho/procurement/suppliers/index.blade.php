@extends('soho::layouts.app')

@section('title', '供应商管理')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">供应商管理</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li>
                    <li class="breadcrumb-item active">供应商管理</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- 搜索/筛选 -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">供应商名称</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">等级</label>
                        <select class="form-select" name="level">
                            <option value="">全部</option>
                            @foreach($levelOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('level') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> 搜索</button>
                        <a href="{{ route('soho.procurement.suppliers.index') }}" class="btn btn-outline-secondary">重置</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white"><div class="card-body py-3"><small>总供应商</small><h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white"><div class="card-body py-3"><small>合作中</small><h3 class="mb-0">{{ $stats['active'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark"><div class="card-body py-3"><small>A级战略供应商</small><h3 class="mb-0">{{ $stats['level_a'] ?? 0 }}</h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white"><div class="card-body py-3"><small>审核中</small><h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3></div></div>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>供应商编号</th>
                        <th>供应商名称</th>
                        <th>联系人</th>
                        <th>等级</th>
                        <th>状态</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td class="text-muted">{{ $supplier->id }}</td>
                            <td><code>{{ $supplier->supplier_no ?? 'SUP-' . $supplier->id }}</code></td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $levelColors[$supplier->level] ?? 'secondary' }}">
                                    {{ $levelOptions[$supplier->level] ?? $supplier->level }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $supplier->status ? 'success' : 'secondary' }}">
                                    {{ $supplier->status ? '合作中' : '已暂停' }}
                                </span>
                            </td>
                            <td class="small">{{ optional($supplier->created_at)->format('Y-m-d H:i') }}</td>
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
        @if($suppliers->hasPages())
            <div class="card-footer">{{ $suppliers->links() }}</div>
        @endif
    </div>
@endsection
