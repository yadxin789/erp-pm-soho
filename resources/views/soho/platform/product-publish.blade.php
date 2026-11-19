@extends('soho::layouts.app')

@section('title', '平台商品发布')

@section('content')
<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">批量发布到平台</h5></div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            选择商品与目标店铺后提交，系统生成「上架任务」；任务由平台运营在上架任务页跟进执行结果。
        </div>
        <form method="POST" action="{{ route('soho.platform.product.publish') }}">
            @csrf
            <div class="row g-2 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">目标店铺</label>
                    <select name="shop_id" class="form-select" required>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}">{{ $shop->shop_name }}（{{ strtoupper($shop->platform) }}）</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">平台</label>
                    <select name="platform" class="form-select" required>
                        @foreach(['taobao' => '淘宝', 'jd' => '京东', 'pinduoduo' => '拼多多', 'amazon' => 'Amazon'] as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-cloud-upload me-1"></i> 提交发布</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width:48px" class="text-center">
                                <input type="checkbox" class="form-check-input" onclick="document.querySelectorAll('.pick').forEach(c=>c.checked=this.checked)">
                            </th>
                            <th>商品编码</th>
                            <th>商品名称</th>
                            <th class="text-end">零售价</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="text-center"><input type="checkbox" class="form-check-input pick" name="product_ids[]" value="{{ $product->id }}"></td>
                                <td class="font-monospace small">{{ $product->product_code }}</td>
                                <td>{{ $product->name }}</td>
                                <td class="text-end">¥{{ number_format((float) $product->retail_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">没有可发布的商品（需先在售或待审核状态）。</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">可用刊登模板</h5>
        <a href="{{ route('soho.platform.listing') }}" class="btn btn-outline-secondary btn-sm">Listing 管理</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>模板名称</th><th class="text-center">平台</th><th>店铺</th><th class="text-center">状态</th></tr></thead>
                <tbody>
                    @forelse($templates as $tpl)
                        <tr>
                            <td>{{ $tpl->name }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ strtoupper($tpl->platform) }}</span></td>
                            <td>{{ $shops[$tpl->shop_id]->shop_name ?? '店铺#'.$tpl->shop_id }}</td>
                            <td class="text-center"><span class="badge bg-success">启用</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">暂无启用中的刊登模板。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
