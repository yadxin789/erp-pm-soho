@extends('soho::layouts.app')
@php $task = $task ?? null; @endphp

@section('title', '扫码打包')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.pack.tasks.index') }}">待打包任务</a></li>
        <li class="breadcrumb-item active">扫码打包</li>
    </ol>
</nav>

<div class="row">
    {{-- 左侧：扫码输入区 --}}
    <div class="col-lg-6">
        {{-- 打包任务信息卡片 --}}
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fa fa-box me-2"></i>扫码打包</h5>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <small class="text-muted">打包单号</small>
                        <div><code>{{ optional($task)->pack_no ?? '' }}</code></div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">订单号</small>
                        <div>{{ optional($task)->order_no ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">仓库</small>
                        <div>{{ optional($task)->warehouse->name ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">打包类型</small>
                        <div>
                            @if((optional($task)->pack_type ?? '') == 'normal')
                                <span class="badge bg-info">普通</span>
                            @elseif((optional($task)->pack_type ?? '') == 'gift')
                                <span class="badge bg-warning text-dark">礼品装</span>
                            @elseif((optional($task)->pack_type ?? '') == 'fragile')
                                <span class="badge bg-danger">易碎品</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 打包进度 --}}
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar bg-warning text-dark" role="progressbar" style="width: {{ optional($task)->progress_percent ?? 0 }}%;">
                        已扫描 {{ optional($task)->packed_count ?? 0 }} / {{ optional($task)->items_count ?? 0 }}
                    </div>
                </div>

                {{-- 扫码输入框 --}}
                <form id="scanForm" onsubmit="return handleScan(event)">
                    <div class="mb-3">
                        <label class="form-label fw-bold">扫描商品条码</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="fa fa-barcode"></i></span>
                            <input type="text" id="scanInput" class="form-control" placeholder="请扫描或输入商品条码/SKU" autofocus autocomplete="off">
                            <button type="submit" class="btn btn-warning">
                                <i class="fa fa-check"></i> 确认
                            </button>
                        </div>
                        <div class="form-text mt-1">扫描枪直接输入，回车确认</div>
                    </div>
                </form>

                {{-- 快捷操作 --}}
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearScan()">
                        <i class="fa fa-times me-1"></i>清空
                    </button>
                    <a href="{{ route('soho.pack.tasks.material', $task) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-box me-1"></i>选择包材
                    </a>
                    <button type="button" class="btn btn-success btn-sm ms-auto" onclick="goToWeight()">
                        <i class="fa fa-weight me-1"></i>称重
                    </button>
                </div>
            </div>
        </div>

        {{-- 扫描历史 --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fa fa-history me-2"></i>扫描历史</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>条码/SKU</th>
                                <th>商品名称</th>
                                <th>数量</th>
                                <th>时间</th>
                            </tr>
                        </thead>
                        <tbody id="scanHistory">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">暂无扫描记录</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 右侧：待打包商品清单 --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-list me-2"></i>待打包商品清单</h5>
                <span class="badge bg-info">{{ optional($task)->items_count ?? 0 }} 项</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>条码</th>
                                <th>商品</th>
                                <th>数量</th>
                                <th>已扫</th>
                                <th>状态</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(optional($task)->items ?? [] as $item)
                            <tr>
                                <td><code>{{ $item->barcode }}</code></td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->required_qty }}</td>
                                <td>{{ $item->packed_qty ?? 0 }}</td>
                                <td>
                                    @if(($item->packed_qty ?? 0) >= $item->required_qty)
                                        <span class="badge bg-success">已扫描</span>
                                    @else
                                        <span class="badge bg-warning text-dark">待扫描</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">无待打包商品</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function handleScan(e) {
    e.preventDefault();
    const input = document.getElementById('scanInput');
    const code = input.value.trim();
    if (!code) return;

    const tbody = document.getElementById('scanHistory');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><code>${code}</code></td>
        <td>商品-${code}</td>
        <td>1</td>
        <td>${new Date().toLocaleTimeString()}</td>
    `;
    if (tbody.children.length > 0 && tbody.children[0].children.length === 4) {
        tbody.insertBefore(row, tbody.firstChild);
        while (tbody.children.length > 10) tbody.removeChild(tbody.lastChild);
    } else {
        tbody.innerHTML = '';
        tbody.appendChild(row);
    }

    input.value = '';
    input.focus();
    return false;
}

function clearScan() {
    document.getElementById('scanInput').value = '';
    document.getElementById('scanInput').focus();
}

function goToWeight() {
    // 跳转到称重页面
}
</script>
@endsection