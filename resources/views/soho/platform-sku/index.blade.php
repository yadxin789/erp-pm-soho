@extends('soho::layouts.app')

@section('title', '平台 SKU 映射')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">平台 SKU 映射列表</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#skuImportModal">
                <i class="fa fa-upload me-1"></i> 批量导入
            </button>
            <a href="{{ route('soho.platform-sku.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> 添加映射
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索平台SKU编码/平台商品ID" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="shop_id" class="form-select">
                        <option value="">全部店铺</option>
                        @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" @selected(request('shop_id') == $shop->id)>{{ $shop->shop_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.platform-sku.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>店铺</th>
                        <th>系统商品</th>
                        <th>平台SKU</th>
                        <th>平台价格</th>
                        <th>平台库存</th>
                        <th>库存同步</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($skuMappings as $mapping)
                    <tr>
                        <td>{{ $mapping->id }}</td>
                        <td>{{ $mapping->shop->shop_name ?? '-' }}</td>
                        <td>{{ $mapping->systemGoods->goods_name ?? '-' }}</td>
                        <td>{{ $mapping->platform_sku }}</td>
                        <td>¥{{ number_format($mapping->platform_price ?? 0, 2) }}</td>
                        <td>{{ $mapping->platform_stock ?? 0 }}</td>
                        <td>
                            @if($mapping->stock_sync_enabled)
                                <span class="badge bg-success">已启用</span>
                            @else
                                <span class="badge bg-secondary">已禁用</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('soho.platform-sku.edit', $mapping) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('soho.platform-sku.destroy', $mapping) }}" class="d-inline" onsubmit="return confirm('确定删除此映射？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无映射数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $skuMappings->links() }}
    </div>
</div>
<div class="modal fade" id="skuImportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('soho.platform-sku.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">批量导入平台 SKU</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">上传 CSV/TXT 文件</label>
                        <input type="file" name="import_file" class="form-control" accept=".csv,.txt">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">或粘贴导入内容</label>
                        <textarea name="import_content" class="form-control" rows="6" placeholder="每行一条：店铺ID,系统商品ID,平台SKU编码,平台价格,平台库存"></textarea>
                        <small class="text-muted">列顺序：店铺ID, 系统商品ID, 平台SKU编码, 平台价格, 平台库存；以 # 开头的行会被忽略。</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">开始导入</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection