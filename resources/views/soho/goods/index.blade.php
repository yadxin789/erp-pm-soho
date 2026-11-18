@extends('soho::layouts.app')

@section('title', '商品管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">商品列表</h5>
        <div>
            <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#batchPublishModal">
                <i class="fa fa-rocket me-1"></i> 批量发布
            </button>
            <a href="{{ route('soho.goods.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> 添加商品
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索商品名称/SKU/条码" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <input type="text" name="brand" class="form-control" placeholder="品牌" value="{{ request('brand') }}">
                </div>
                <div class="col-md-2">
                    <input type="text" name="category" class="form-control" placeholder="分类" value="{{ request('category') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.goods.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID</th>
                        <th>商品名称</th>
                        <th>SKU</th>
                        <th>品牌</th>
                        <th>分类</th>
                        <th>成本价</th>
                        <th>零售价</th>
                        <th>库存</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goods as $item)
                    <tr>
                        <td><input type="checkbox" class="goods-checkbox" value="{{ $item->id }}"></td>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->sku }}</td>
                        <td>{{ $item->brand ?? '-' }}</td>
                        <td>{{ $item->category ?? '-' }}</td>
                        <td>¥{{ number_format($item->cost_price, 2) }}</td>
                        <td>¥{{ number_format($item->selling_price, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $item->system_stock <= $item->min_stock_warn && $item->min_stock_warn > 0 ? 'danger' : 'success' }}">
                                {{ $item->system_stock ?? 0 }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $item->status ? 'success' : 'secondary' }}">
                                {{ $item->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('soho.goods.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('soho.goods.destroy', $item) }}" class="d-inline" onsubmit="return confirm('确定删除？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $goods->links() }}
    </div>
</div>

<!-- 批量发布弹窗 -->
<div class="modal fade" id="batchPublishModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('soho.goods.batch-publish') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">批量发布商品</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>使用说明：</strong>
                        <br>1. 在商品列表中勾选要发布的商品
                        <br>2. 选择要发布到的店铺
                        <br>3. 点击确认发布
                    </div>
                    <div class="mb-3">
                        <label class="form-label">已选商品</label>
                        <div id="selectedGoods" class="text-muted">未选择商品，请先在商品列表中勾选</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">发布到店铺 <span class="text-danger">*</span></label>
                        <div>
                            @foreach(\App\Soho\Models\Shop::where('status', true)->get() as $shop)
                            <div class="form-check">
                                <input type="checkbox" name="shop_ids[]" value="{{ $shop->id }}" class="form-check-input" id="shop_{{ $shop->id }}">
                                <label class="form-check-label" for="shop_{{ $shop->id }}">{{ $shop->name }} ({{ $shop->platform_label }})</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success" onclick="prepareGoodsIds()">确认发布</button>
                </div>
                <input type="hidden" name="goods_ids[]" id="goodsIdsInput">
            </form>
        </div>
    </div>
</div>

<script>
    // 全选
    document.getElementById('selectAll')?.addEventListener('change', function(e) {
        document.querySelectorAll('.goods-checkbox').forEach(cb => cb.checked = e.target.checked);
        updateSelectedGoods();
    });

    // 更新已选商品显示
    function updateSelectedGoods() {
        const checked = document.querySelectorAll('.goods-checkbox:checked');
        const container = document.getElementById('selectedGoods');
        if (checked.length === 0) {
            container.innerHTML = '未选择商品，请先在商品列表中勾选';
        } else {
            const ids = Array.from(checked).map(cb => cb.value);
            container.innerHTML = `已选择 <strong>${ids.length}</strong> 个商品 (ID: ${ids.join(', ')})`;
        }
    }

    // 显示当前已选
    document.getElementById('batchPublishModal').addEventListener('show.bs.modal', updateSelectedGoods);

    // 准备 goods_ids 提交
    function prepareGoodsIds() {
        const ids = Array.from(document.querySelectorAll('.goods-checkbox:checked')).map(cb => cb.value);
        document.getElementById('goodsIdsInput').value = ids.join(',');
        // 将逗号分隔的转换为实际的数组提交
        const form = document.querySelector('#batchPublishModal form');
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'goods_ids[]';
            input.value = id;
            form.appendChild(input);
        });
    }

    // 监听勾选变化
    document.querySelectorAll('.goods-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            document.getElementById('selectAll').checked = 
                document.querySelectorAll('.goods-checkbox').length === 
                document.querySelectorAll('.goods-checkbox:checked').length;
        });
    });
</script>
@endsection