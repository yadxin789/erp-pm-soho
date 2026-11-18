@extends('soho::layouts.app')
@php $recentCallbacks = $recentCallbacks ?? collect(); @endphp


@section('title', '发货回传')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">发货回传</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-sign-in me-2"></i>发货回传</h4>
    <div>
        <a href="{{ route('soho.logistics.batch.callback') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-list me-1"></i>批量回传
        </a>
    </div>
</div>

<div class="row">
    {{-- 左侧：回传单号输入 --}}
    <div class="col-lg-5">
        {{-- 扫码回传卡片 --}}
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fa fa-barcode me-2"></i>扫码回传</h5>
            </div>
            <div class="card-body">
                {{-- 回传说明 --}}
                <div class="alert alert-info">
                    <i class="fa fa-info-circle me-1"></i>
                    <strong>操作说明：</strong>扫描已发货包裹的运单号，系统将自动完成发货回传确认。
                </div>

                {{-- 扫码输入 --}}
                <form id="callbackForm" onsubmit="return handleCallback(event)">
                    <div class="mb-3">
                        <label class="form-label fw-bold">扫描/输入运单号</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="fa fa-barcode"></i></span>
                            <input type="text" id="trackingInput" class="form-control" placeholder="请扫描或输入物流单号" autofocus autocomplete="off">
                            <button type="submit" class="btn btn-info">
                                <i class="fa fa-check"></i> 确认回传
                            </button>
                        </div>
                        <div class="form-text mt-1">支持扫描枪直接输入，回车确认</div>
                    </div>

                    {{-- 手动输入 --}}
                    <div class="mb-3">
                        <label class="form-label">或手动输入包裹信息</label>
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <input type="text" name="parcel_no" class="form-control" placeholder="包裹号">
                            </div>
                            <div class="col-sm-6">
                                <input type="text" name="tracking_no" class="form-control" placeholder="物流单号">
                            </div>
                        </div>
                    </div>

                    {{-- 回传状态选择 --}}
                    <div class="mb-3">
                        <label class="form-label">回传状态</label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="callback_status" id="status_shipped" value="shipped" checked>
                            <label class="btn btn-outline-success" for="status_shipped">已发货</label>

                            <input type="radio" class="btn-check" name="callback_status" id="status_exception" value="exception">
                            <label class="btn btn-outline-warning" for="status_exception">异常</label>

                            <input type="radio" class="btn-check" name="callback_status" id="status_returned" value="returned">
                            <label class="btn btn-outline-danger" for="status_returned">已退回</label>
                        </div>
                    </div>

                    {{-- 异常信息（条件显示）--}}
                    <div id="exceptionInfo" class="mb-3 d-none">
                        <label class="form-label">异常描述</label>
                        <textarea name="exception_desc" class="form-control" rows="2" placeholder="请描述异常情况"></textarea>
                    </div>

                    {{-- 操作按钮 --}}
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearForm()">
                            <i class="fa fa-times me-1"></i>清空
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 右侧：回传记录 --}}
    <div class="col-lg-7">
        {{-- 回传历史 --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-history me-2"></i>今日回传记录</h5>
                <span class="badge bg-info" id="todayCount">0 条</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>包裹号</th>
                                <th>物流单号</th>
                                <th>状态</th>
                                <th>时间</th>
                            </tr>
                        </thead>
                        <tbody id="callbackHistory">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">暂无回传记录</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 最近回传列表 --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fa fa-clock me-2"></i>最近回传包裹</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>包裹号</th>
                                <th>快递公司</th>
                                <th>物流单号</th>
                                <th>回传状态</th>
                                <th>回传时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCallbacks as $record)
                            <tr>
                                <td><code>{{ $record->parcel_no }}</code></td>
                                <td>{{ $record->express_company_name }}</td>
                                <td>{{ $record->tracking_no }}</td>
                                <td>
                                    @if($record->callback_status == 'shipped')
                                        <span class="badge bg-success">已发货</span>
                                    @elseif($record->callback_status == 'exception')
                                        <span class="badge bg-warning text-dark">异常</span>
                                    @elseif($record->callback_status == 'returned')
                                        <span class="badge bg-danger">已退回</span>
                                    @endif
                                </td>
                                <td>{{ optional($record->callbacked_at)->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">暂无回传记录</td>
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
document.querySelectorAll('input[name="callback_status"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const exceptionInfo = document.getElementById('exceptionInfo');
        if (this.value === 'exception' || this.value === 'returned') {
            exceptionInfo.classList.remove('d-none');
        } else {
            exceptionInfo.classList.add('d-none');
        }
    });
});

function handleCallback(e) {
    e.preventDefault();
    const trackingNo = document.getElementById('trackingInput').value.trim();
    if (!trackingNo) {
        alert('请输入物流单号');
        return false;
    }

    const status = document.querySelector('input[name="callback_status"]:checked').value;
    const tbody = document.getElementById('callbackHistory');
    const statusLabel = status === 'shipped' ? '<span class="badge bg-success">已发货</span>' :
                       status === 'exception' ? '<span class="badge bg-warning text-dark">异常</span>' :
                       '<span class="badge bg-danger">已退回</span>';

    const row = document.createElement('tr');
    row.innerHTML = `
        <td><code>P${Date.now().toString().slice(-8)}</code></td>
        <td>${trackingNo}</td>
        <td>${statusLabel}</td>
        <td>${new Date().toLocaleTimeString()}</td>
    `;
    if (tbody.children.length > 0 && tbody.children[0].children.length === 4) {
        tbody.insertBefore(row, tbody.firstChild);
        while (tbody.children.length > 20) tbody.removeChild(tbody.lastChild);
    } else {
        tbody.innerHTML = '';
        tbody.appendChild(row);
    }

    const count = document.getElementById('callbackHistory').children.length;
    document.getElementById('todayCount').textContent = count + ' 条';

    document.getElementById('trackingInput').value = '';
    document.getElementById('trackingInput').focus();
    return false;
}

function clearForm() {
    document.getElementById('trackingInput').value = '';
    document.querySelector('input[name="parcel_no"]').value = '';
    document.querySelector('input[name="tracking_no"]').value = '';
    document.getElementById('trackingInput').focus();
}
</script>
@endsection