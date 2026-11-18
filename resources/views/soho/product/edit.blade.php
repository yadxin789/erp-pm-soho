@extends('soho::layouts.app')

@section('title', '编辑商品')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">编辑商品 SPU</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.product.update', $product) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">商品名称 <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">商品编码 <span class="text-danger">*</span></label>
                        <input type="text" name="product_code" class="form-control" value="{{ old('product_code', $product->product_code) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">商品副标题</label>
                        <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $product->subtitle) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">分类</label>
                        <select name="category_id" class="form-select">
                            <option value="">请选择分类</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">品牌</label>
                        <select name="brand_id" class="form-select">
                            <option value="">请选择品牌</option>
                            @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">成本价</label>
                                <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price) }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">零售价</label>
                                <input type="number" step="0.01" name="retail_price" class="form-control" value="{{ old('retail_price', $product->retail_price) }}" min="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <div class="row"><div class="col-md-6"><div class="mb-3"><label class="form-label">重量(kg)</label><input type="number" step="0.001" name="weight" class="form-control" value="{{ old('weight', $product->weight) }}"></div></div><div class="col-md-6"><div class="mb-3"><label class="form-label">属性(JSON)</label><input type="text" name="attributes" class="form-control" value="{{ old('attributes', is_array($product->attributes) ? json_encode($product->attributes, JSON_UNESCAPED_UNICODE) : $product->attributes) }}"></div></div></div>
                <label class="form-label">商品描述</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <label class="form-label mb-0">SKU 列表</label>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSkuRow()">+ 添加 SKU</button>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm" id="skuTable">
                        <thead>
                            <tr>
                                <th>SKU编码</th>
                                <th>SKU名称</th>
                                <th>规格</th>
                                <th>成本价</th>
                                <th>零售价</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="skuBody">
                            @php
                                $existingSkus = old('skus', $product->skus->toArray());
                            @endphp
                            @foreach($existingSkus as $index => $sku)
                            <tr class="sku-row">
                                <td><input type="text" name="skus[{{ $index }}][sku_code]" class="form-control form-control-sm" value="{{ $sku['sku_code'] ?? '' }}" required></td>
                                <td><input type="text" name="skus[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $sku['name'] ?? '' }}"></td>
                                <td><input type="text" name="skus[{{ $index }}][specification]" class="form-control form-control-sm" value="{{ is_array($sku['specification'] ?? null) ? json_encode($sku['specification']) : ($sku['specification'] ?? '') }}"></td>
                                <td><input type="number" step="0.01" name="skus[{{ $index }}][cost_price]" class="form-control form-control-sm" value="{{ $sku['cost_price'] ?? '' }}"></td>
                                <td><input type="number" step="0.01" name="skus[{{ $index }}][sale_price]" class="form-control form-control-sm" value="{{ $sku['sale_price'] ?? '' }}"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSkuRow(this)">删除</button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">状态</label>
                <select name="status" class="form-select">
                    <option value="draft" @selected(old('status', $product->status) == 'draft')>草稿</option>
                    <option value="pending" @selected(old('status', $product->status) == 'pending')>待审核</option>
                    <option value="active" @selected(old('status', $product->status) == 'active')>启用</option>
                    <option value="inactive" @selected(old('status', $product->status) == 'inactive')>停用</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="{{ route('soho.product.index') }}" class="btn btn-secondary">返回</a>
        </form>
    </div>
</div>

<script>
let skuIndex = {{ count($existingSkus) }};

function addSkuRow() {
    const tbody = document.getElementById('skuBody');
    const row = document.createElement('tr');
    row.className = 'sku-row';
    row.innerHTML = `
        <td><input type="text" name="skus[${skuIndex}][sku_code]" class="form-control form-control-sm" required></td>
        <td><input type="text" name="skus[${skuIndex}][name]" class="form-control form-control-sm"></td>
        <td><input type="text" name="skus[${skuIndex}][specification]" class="form-control form-control-sm"></td>
        <td><input type="number" step="0.01" name="skus[${skuIndex}][cost_price]" class="form-control form-control-sm"></td>
        <td><input type="number" step="0.01" name="skus[${skuIndex}][sale_price]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSkuRow(this)">删除</button></td>
    `;
    tbody.appendChild(row);
    skuIndex++;
}

function removeSkuRow(btn) {
    btn.closest('tr').remove();
}
</script>
@endsection