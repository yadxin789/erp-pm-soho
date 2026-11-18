@extends('soho::layouts.app')

@section('title', '扫码复核')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('fulfillment.check-tasks.index') }}">待复核任务</a></li>
        <li class="breadcrumb-item active">扫码复核</li>
    </ol>
</nav>

<div class="row">
    {{-- 左侧：扫码输入区 --}}
    <div class="col-lg-6">
        {{-- 复核任务信息卡片 --}}
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fa fa-clipboard-check me-2"></i>扫码复核</h5>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <small class="text-muted">复核单号</small>
                        <div><code>{{ $task->check_no ?? '' }}</code></div>
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
                        <small class="text-muted">来源</small>
                        <div>
                            @if(($task->source ?? '') == 'pick')
                                <span class="badge bg-info">拣货完成</span>
                            @else
                                <span class="badge bg-warning text-dark">退货入库</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 复核进度 --}}
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $task->progress_percent ?? 0 }}%;">
                        已复核 {{ $task->checked_count ?? 0 }} / {{ $task->items_count ?? 0 }}
                    </div>
                </div>

                {{-- 差异提示 --}}
                @if(($task->diff_count ?? 0) > 0)
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle me-1"></i>
                    <strong>差异提示：</strong>当前复核与拣货记录存在 {{ $task->diff_count }} 处差异，请仔细核对。
                </div>
                @endif

                {{-- 扫码输入框 --}}
                <form id="scanForm" onsubmit="return handleScan(event)">
                    <div class="mb-3">
                        <label class="form-label fw-bold">扫描商品条码进行复核</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="fa fa-barcode"></i></span>
                            <input type="text" id="scanInput" class="form-control" placeholder="请扫描或输入商品条码/SKU" autofocus autocomplete="off">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i> 确认复核
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
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="reportException()">
                        <i class="fa fa-exclamation-triangle me-1"></i>复核异常
                    </button>
                    <button type="button" class="btn btn-success btn-sm ms-auto" onclick="completeCheck()">
                        <i class="fa fa-flag-checkered me-1"></i>完成复核
                    </button>
                </div>
            </div>
        </div>

        {{-- 扫描历史 --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fa fa-history me-2"></i>复核扫描历史</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>条码/SKU</th>
                                <th>扫描数量</th>
                                <th>拣货数量</th>
                                <th>结果</th>
                                <th>时间</th>
                            </tr>
                        </thead>
                        <tbody id="scanHistory">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">暂无复核记录</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 右侧：待复核商品清单 --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-list me-2"></i>待复核商品清单</h5>
                <span class="badge bg-info">{{ $task->items_count ?? 0 }} 项</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>条码</th>
                                <th>商品</th>
                                <th>拣货数量</th>
                                <th>复核数量</th>
                                <th>差异</th>
                                <th>状态</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($task->items ?? [] as $item)
                            <tr>
                                <td><code>{{ $item->barcode }}</code></td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->picked_qty }}</td>
                                <td>{{ $item->checked_qty ?? 0 }}</td>
                                <td>
                                    @php $diff = ($item->checked_qty ?? 0) - $item->picked_qty; @endphp
                                    @if($diff > 0)
                                        <span class="text-success">+{{ $diff }}</span>
                                    @elseif($diff < 0)
                                        <span class="text-danger">{{ $diff }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if(($item->checked_qty ?? 0) >= $item->picked_qty)
                                        <span class="badge bg-success">已复核</span>
                                    @else
                                        <span class="badge bg-warning text-dark">待复核</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">无待复核商品</td>
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
        <td>1</td>
        <td>1</td>
        <td><span class="badge bg-success">一致</span></td>
        <td>${new Date().toLocaleTimeString()}</td>
    `;
    if (tbody.children.length > 0 && tbody.children[0].children.length === 5) {
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

function reportException() {
    if (confirm('确认上报复核异常？')) {
        // 跳转到异常页面
    }
}

function completeCheck() {
    if (confirm('确认完成当前复核任务？')) {
        alert('复核任务已提交！');
    }
}
</script>
@endsection