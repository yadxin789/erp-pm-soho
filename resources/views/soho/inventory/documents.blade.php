@extends('soho::layouts.app')

@section('title', '库存单据')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.inventory.index') }}">库存管理</a></li>
        <li class="breadcrumb-item active">库存单据</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">库存单据列表</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
            <i class="fa fa-plus"></i> 创建单据
        </button>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('soho.inventory.documents') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索单据号/备注" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="document_type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="inbound" @selected(request('document_type') == 'inbound')>入库单</option>
                        <option value="outbound" @selected(request('document_type') == 'outbound')>出库单</option>
                        <option value="transfer" @selected(request('document_type') == 'transfer')>调拨单</option>
                        <option value="stocktake" @selected(request('document_type') == 'stocktake')>盘点单</option>
                        <option value="init" @selected(request('document_type') == 'init')>期初单</option>
                        <option value="adjust" @selected(request('document_type') == 'adjust')>调整单</option>
                        <option value="scrap" @selected(request('document_type') == 'scrap')>报废单</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="draft" @selected(request('status') == 'draft')>草稿</option>
                        <option value="approved" @selected(request('status') == 'approved')>已审核</option>
                        <option value="completed" @selected(request('status') == 'completed')>已完成</option>
                        <option value="cancelled" @selected(request('status') == 'cancelled')>已取消</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.inventory.documents') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>单据号</th>
                        <th>类型</th>
                        <th>仓库</th>
                        <th>总数量</th>
                        <th>总金额</th>
                        <th>状态</th>
                        <th>操作人</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    <tr>
                        <td>{{ $doc->document_no }}</td>
                        <td>
                            @if($doc->document_type == 'inbound')
                                <span class="badge bg-success">入库单</span>
                            @elseif($doc->document_type == 'outbound')
                                <span class="badge bg-danger">出库单</span>
                            @elseif($doc->document_type == 'transfer')
                                <span class="badge bg-info">调拨单</span>
                            @elseif($doc->document_type == 'stocktake')
                                <span class="badge bg-warning text-dark">盘点单</span>
                            @elseif($doc->document_type == 'init')
                                <span class="badge bg-primary">期初单</span>
                            @elseif($doc->document_type == 'adjust')
                                <span class="badge bg-secondary">调整单</span>
                            @elseif($doc->document_type == 'scrap')
                                <span class="badge bg-dark">报废单</span>
                            @else
                                <span class="badge bg-light text-dark">{{ $doc->document_type }}</span>
                            @endif
                        </td>
                        <td>{{ $doc->warehouse->name ?? '-' }}</td>
                        <td>{{ $doc->total_quantity ?? 0 }}</td>
                        <td>{{ $doc->total_amount ? '¥' . number_format($doc->total_amount, 2) : '-' }}</td>
                        <td>
                            @if($doc->status == 'draft')
                                <span class="badge bg-secondary">草稿</span>
                            @elseif($doc->status == 'approved')
                                <span class="badge bg-info">已审核</span>
                            @elseif($doc->status == 'completed')
                                <span class="badge bg-success">已完成</span>
                            @elseif($doc->status == 'cancelled')
                                <span class="badge bg-danger">已取消</span>
                            @else
                                <span class="badge bg-light text-dark">{{ $doc->status }}</span>
                            @endif
                        </td>
                        <td>{{ $doc->operator->name ?? '-' }}</td>
                        <td>{{ $doc->created_at ? $doc->created_at->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $documents->links() }}
    </div>
</div>

<div class="modal fade" id="createDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('soho.inventory.documents.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">创建库存单据</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">单据类型 <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select" required>
                            <option value="inbound">入库单</option>
                            <option value="outbound">出库单</option>
                            <option value="transfer">调拨单</option>
                            <option value="stocktake">盘点单</option>
                            <option value="init">期初单</option>
                            <option value="adjust">调整单</option>
                            <option value="scrap">报废单</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">仓库 <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value="">选择仓库</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">总数量</label>
                        <input type="number" name="total_quantity" class="form-control" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">总金额</label>
                        <input type="number" step="0.01" name="total_amount" class="form-control" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check"></i> 创建
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
