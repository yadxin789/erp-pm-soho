@extends('soho::layouts.app')

@section('title', '包材选择')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.pack.tasks.index') }}">待打包任务</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.pack.tasks.scan', $task) }}">扫码打包</a></li>
        <li class="breadcrumb-item active">包材选择</li>
    </ol>
</nav>

<div class="row">
    {{-- 左侧：任务信息 --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fa fa-box me-2"></i>打包任务信息</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">打包单号</small>
                    <div><code>{{ $task->pack_no ?? '' }}</code></div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">订单号</small>
                    <div>{{ $task->order_no ?? '-' }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">仓库</small>
                    <div>{{ $task->warehouse->name ?? '-' }}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">商品数</small>
                    <div>{{ $task->items_count ?? 0 }} 件</div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">推荐包材</small>
                    <div>
                        @if($task->recommended_material)
                            <span class="badge bg-info">{{ $task->recommended_material->name }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                {{-- 商品预览 --}}
                <h6 class="mt-3 mb-2">打包商品预览</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>商品</th>
                                <th>数量</th>
                                <th>尺寸(长×宽×高)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($task->items ?? [] as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->required_qty }}</td>
                                <td>{{ $item->length ?? 0 }}×{{ $item->width ?? 0 }}×{{ $item->height ?? 0 }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">-</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 右侧：包材选择 --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-boxes me-2"></i>选择包材</h5>
                <span class="badge bg-info">共 {{ $materials->count() }} 种包材</span>
            </div>
            <div class="card-body">
                {{-- 包材分类标签 --}}
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#all" role="tab">全部</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#carton" role="tab">纸箱</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#bubble" role="tab">气泡膜</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#express" role="tab">快递袋</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#other" role="tab">其他</a>
                    </li>
                </ul>

                {{-- 包材选择表单 --}}
                <form id="materialForm" onsubmit="return selectMaterial(event)">
                    <div class="row g-3" id="materialList">
                        @forelse($materials as $material)
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="card material-card cursor-pointer h-100" data-material-id="{{ $material->id }}">
                                <div class="card-body text-center">
                                    <i class="fa fa-box fa-3x text-warning mb-2"></i>
                                    <h6 class="card-title">{{ $material->name }}</h6>
                                    <div class="text-muted small mb-1">{{ $material->specification }}</div>
                                    <div class="mb-2">
                                        <span class="badge bg-secondary">{{ $material->category_label }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center text-muted small">
                                        <span>库存: {{ $material->stock }}</span>
                                        <span>¥{{ number_format($material->price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                            暂无可用包材
                        </div>
                        @endforelse
                    </div>

                    {{-- 已选包材 --}}
                    <div class="mt-3 p-3 border rounded bg-light">
                        <h6>已选择包材</h6>
                        <div id="selectedMaterial" class="text-muted">请从上方选择一个包材</div>
                    </div>

                    {{-- 操作按钮 --}}
                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('soho.pack.tasks.scan', $task) }}" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left me-1"></i>返回扫码
                        </a>
                        <button type="submit" class="btn btn-success ms-auto">
                            <i class="fa fa-check me-1"></i>确认选择
                        </button>
                        <a href="{{ route('soho.pack.tasks.weight', $task) }}" class="btn btn-primary">
                            <i class="fa fa-weight me-1"></i>下一步：称重
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedMaterialId = null;

document.querySelectorAll('.material-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.material-card').forEach(c => {
            c.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        });
        this.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        selectedMaterialId = this.dataset.materialId;

        const name = this.querySelector('.card-title').textContent;
        const spec = this.querySelector('.text-muted.small').textContent;
        document.getElementById('selectedMaterial').innerHTML =
            `<span class="badge bg-primary me-2">已选</span>${name} (${spec})`;
    });
});

function selectMaterial(e) {
    e.preventDefault();
    if (!selectedMaterialId) {
        alert('请先选择一个包材');
        return false;
    }
    // 确认选择逻辑
    return false;
}
</script>
@endsection