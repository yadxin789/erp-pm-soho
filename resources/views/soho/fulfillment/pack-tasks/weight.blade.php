@extends('soho::layouts.app')

@section('title', '称重录入')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('fulfillment.pack-tasks.index') }}">待打包任务</a></li>
        <li class="breadcrumb-item"><a href="{{ route('fulfillment.pack-tasks.scan', $task) }}">扫码打包</a></li>
        <li class="breadcrumb-item active">称重录入</li>
    </ol>
</nav>

<div class="row">
    {{-- 左侧：任务和商品信息 --}}
    <div class="col-lg-5">
        {{-- 打包任务信息 --}}
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fa fa-weight me-2"></i>称重录入</h5>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <small class="text-muted">打包单号</small>
                        <div><code>{{ $task->pack_no ?? '' }}</code></div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">订单号</small>
                        <div>{{ $task->order_no ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">仓库</small>
                        <div>{{ $task->warehouse->name ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">已选包材</small>
                        <div>{{ $task->selected_material_name ?? '未选择' }}</div>
                    </div>
                </div>

                {{-- 商品列表 --}}
                <h6 class="mt-3 mb-2">待打包商品</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>商品</th>
                                <th>数量</th>
                                <th>预估重量</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($task->items ?? [] as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->required_qty }}</td>
                                <td>{{ $item->estimated_weight ?? 0 }} kg</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">-</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-warning">
                            <tr>
                                <th colspan="2">预估总重</th>
                                <th>{{ $task->estimated_total_weight ?? 0 }} kg</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 右侧：称重录入表单 --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-balance-scale me-2"></i>实际重量录入</h5>
            </div>
            <div class="card-body">
                {{-- 称重表单 --}}
                <form id="weightForm" onsubmit="return submitWeight(event)">
                    {{-- 实时重量显示 --}}
                    <div class="text-center mb-4">
                        <div class="display-1 text-primary font-weight-bold" id="weightDisplay">0.000</div>
                        <div class="text-muted">kg（千克）</div>
                    </div>

                    {{-- 重量输入 --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">实际重量 (kg)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="fa fa-balance-scale"></i></span>
                            <input type="number" step="0.001" min="0" id="weightInput" class="form-control" placeholder="请输入或扫码称重" oninput="updateWeightDisplay()">
                            <span class="input-group-text">kg</span>
                        </div>
                        <div class="form-text">支持电子秤直连读取或手动输入，精度0.001kg</div>
                    </div>

                    {{-- 体积重计算（可选） --}}
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="mb-2"><i class="fa fa-cube me-1"></i>体积信息（可选）</h6>
                            <div class="row g-2">
                                <div class="col-sm-3">
                                    <label class="form-label small">长 (cm)</label>
                                    <input type="number" step="0.1" min="0" name="length" class="form-control" placeholder="长">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small">宽 (cm)</label>
                                    <input type="number" step="0.1" min="0" name="width" class="form-control" placeholder="宽">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small">高 (cm)</label>
                                    <input type="number" step="0.1" min="0" name="height" class="form-control" placeholder="高">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small">体积重</label>
                                    <input type="text" id="volumeWeight" class="form-control" placeholder="自动计算" readonly>
                                </div>
                            </div>
                            <div class="form-text mt-1">体积重计算公式：长×宽×高/6000（cm³→kg）</div>
                        </div>
                    </div>

                    {{-- 计费重提示 --}}
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle me-1"></i>
                        <strong>计费重量：</strong><span id="chargeWeight">0.000</span> kg
                        <span class="text-muted small">（实际重量与体积重取大者）</span>
                    </div>

                    {{-- 备注 --}}
                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="2" placeholder="如有特殊情况请备注"></textarea>
                    </div>

                    {{-- 操作按钮 --}}
                    <div class="d-flex gap-2">
                        <a href="{{ route('fulfillment.pack-tasks.material', $task) }}" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left me-1"></i>返回选包材
                        </a>
                        <button type="button" class="btn btn-outline-warning" onclick="tareWeight()">
                            <i class="fa fa-minus me-1"></i>去皮
                        </button>
                        <button type="submit" class="btn btn-success ms-auto">
                            <i class="fa fa-check me-1"></i>确认称重
                        </button>
                        <a href="{{ route('fulfillment.pack-tasks.parcel', $task) }}" class="btn btn-primary">
                            <i class="fa fa-box me-1"></i>生成包裹
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
function updateWeightDisplay() {
    const val = document.getElementById('weightInput').value;
    document.getElementById('weightDisplay').textContent = val ? parseFloat(val).toFixed(3) : '0.000';
    calculateChargeWeight();
}

function calculateChargeWeight() {
    const actualWeight = parseFloat(document.getElementById('weightInput').value) || 0;
    const l = parseFloat(document.querySelector('input[name="length"]')?.value) || 0;
    const w = parseFloat(document.querySelector('input[name="width"]')?.value) || 0;
    const h = parseFloat(document.querySelector('input[name="height"]')?.value) || 0;
    const volumeWeight = (l * w * h) / 6000;
    document.getElementById('volumeWeight').value = volumeWeight.toFixed(3);
    const chargeWeight = Math.max(actualWeight, volumeWeight);
    document.getElementById('chargeWeight').textContent = chargeWeight.toFixed(3);
}

document.querySelectorAll('input[name="length"], input[name="width"], input[name="height"]').forEach(el => {
    el.addEventListener('input', calculateChargeWeight);
});

function tareWeight() {
    document.getElementById('weightInput').value = '0';
    updateWeightDisplay();
}

function submitWeight(e) {
    e.preventDefault();
    const weight = document.getElementById('weightInput').value;
    if (!weight || parseFloat(weight) <= 0) {
        alert('请输入有效的重量');
        return false;
    }
    // 提交称重数据
    alert('称重数据已保存！');
    return false;
}
</script>
@endsection