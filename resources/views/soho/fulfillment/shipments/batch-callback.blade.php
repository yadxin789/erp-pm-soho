@extends('soho::layouts.app')

@section('title', '批量回传')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">批量回传</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-list me-2"></i>批量回传</h4>
    <div>
        <a href="{{ route('fulfillment.shipments.callback') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-arrow-left me-1"></i>单个回传
        </a>
    </div>
</div>

<div class="row">
    {{-- 左侧：批量回传输入 --}}
    <div class="col-lg-6">
        {{-- 批量回传卡片 --}}
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-list me-2"></i>批量回传数据</h5>
            </div>
            <div class="card-body">
                {{-- 格式说明 --}}
                <div class="alert alert-info">
                    <strong>格式说明：</strong>每行一条记录，支持以下格式：<br>
                    1. <code>包裹号,物流单号</code>（两列）<br>
                    2. <code>包裹号,物流单号,状态</code>（三列，状态：shipped/exception/returned）<br>
                    支持 CSV 文件上传或直接粘贴文本。
                </div>

                {{-- 回传方式 --}}
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#paste" role="tab">粘贴文本</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#upload" role="tab">上传文件</a>
                    </li>
                </ul>

                {{-- 粘贴文本方式 --}}
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="paste" role="tabpanel">
                        <form id="pasteForm" onsubmit="return handlePaste(event)">
                            <div class="mb-3">
                                <label class="form-label fw-bold">粘贴回传数据</label>
                                <textarea id="batchData" class="form-control" rows="8" placeholder="包裹号1,物流单号1&#10;包裹号2,物流单号2&#10;包裹号3,物流单号3,shipped"></textarea>
                            </div>

                            {{-- 默认回传状态 --}}
                            <div class="mb-3">
                                <label class="form-label">默认回传状态</label>
                                <select name="default_status" class="form-select">
                                    <option value="shipped">已发货</option>
                                    <option value="exception">异常</option>
                                    <option value="returned">已退回</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="clearBatch()">
                                    <i class="fa fa-times me-1"></i>清空
                                </button>
                                <button type="submit" class="btn btn-primary ms-auto">
                                    <i class="fa fa-check me-1"></i>解析数据
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- 上传文件方式 --}}
                    <div class="tab-pane fade" id="upload" role="tabpanel">
                        <form id="uploadForm" onsubmit="return handleUpload(event)">
                            <div class="mb-3">
                                <label class="form-label fw-bold">上传 CSV 文件</label>
                                <input type="file" id="csvFile" class="form-control" accept=".csv,.txt">
                                <div class="form-text">支持 .csv 和 .txt 格式，单文件最多 1000 条记录</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary ms-auto">
                                    <i class="fa fa-upload me-1"></i>上传并解析
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 右侧：预览和确认 --}}
    <div class="col-lg-6">
        {{-- 数据预览 --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-eye me-2"></i>数据预览</h5>
                <span class="badge bg-info" id="previewCount">待解析</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>序号</th>
                                <th>包裹号</th>
                                <th>物流单号</th>
                                <th>状态</th>
                                <th>校验</th>
                            </tr>
                        </thead>
                        <tbody id="previewTable">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">请先在左侧解析数据</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 统计汇总 --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fa fa-bar-chart me-2"></i>校验结果</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4 text-center">
                        <div class="text-muted small">总记录数</div>
                        <div class="fs-4" id="totalCount">0</div>
                    </div>
                    <div class="col-sm-4 text-center">
                        <div class="text-muted small">有效记录</div>
                        <div class="fs-4 text-success" id="validCount">0</div>
                    </div>
                    <div class="col-sm-4 text-center">
                        <div class="text-muted small">异常记录</div>
                        <div class="fs-4 text-danger" id="invalidCount">0</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 确认回传 --}}
        <div class="card">
            <div class="card-body text-center">
                <button type="button" class="btn btn-success btn-lg" onclick="confirmBatchCallback()">
                    <i class="fa fa-check-double me-2"></i>确认批量回传
                </button>
                <div class="form-text mt-2">确认后将批量回传所有有效记录到对应平台</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let parsedData = [];

function handlePaste(e) {
    e.preventDefault();
    const data = document.getElementById('batchData').value.trim();
    const defaultStatus = document.querySelector('select[name="default_status"]').value;

    if (!data) {
        alert('请输入回传数据');
        return false;
    }

    const lines = data.split('\n');
    parsedData = [];

    lines.forEach((line, index) => {
        const parts = line.trim().split(',');
        if (parts.length >= 2) {
            parsedData.push({
                index: index + 1,
                parcel_no: parts[0].trim(),
                tracking_no: parts[1].trim(),
                status: parts[2] ? parts[2].trim() : defaultStatus,
                valid: true
            });
        }
    });

    renderPreview();
    return false;
}

function handleUpload(e) {
    e.preventDefault();
    const file = document.getElementById('csvFile').files[0];
    if (!file) {
        alert('请选择文件');
        return false;
    }

    const reader = new FileReader();
    reader.onload = function(event) {
        const data = event.target.result;
        const lines = data.split('\n');
        parsedData = [];

        lines.forEach((line, index) => {
            if (line.trim()) {
                const parts = line.trim().split(',');
                if (parts.length >= 2) {
                    parsedData.push({
                        index: index + 1,
                        parcel_no: parts[0].trim(),
                        tracking_no: parts[1].trim(),
                        status: parts[2] ? parts[2].trim() : 'shipped',
                        valid: true
                    });
                }
            }
        });

        renderPreview();
    };
    reader.readAsText(file);
    return false;
}

function renderPreview() {
    const tbody = document.getElementById('previewTable');
    const valid = parsedData.filter(d => d.valid);
    const invalid = parsedData.filter(d => !d.valid);

    document.getElementById('previewCount').textContent = parsedData.length + ' 条记录';
    document.getElementById('totalCount').textContent = parsedData.length;
    document.getElementById('validCount').textContent = valid.length;
    document.getElementById('invalidCount').textContent = invalid.length;

    if (parsedData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">暂无数据</td></tr>';
        return;
    }

    tbody.innerHTML = parsedData.slice(0, 50).map(d => `
        <tr>
            <td>${d.index}</td>
            <td><code>${d.parcel_no}</code></td>
            <td>${d.tracking_no}</td>
            <td>
                ${d.status === 'shipped' ? '<span class="badge bg-success">已发货</span>' : ''}
                ${d.status === 'exception' ? '<span class="badge bg-warning text-dark">异常</span>' : ''}
                ${d.status === 'returned' ? '<span class="badge bg-danger">已退回</span>' : ''}
            </td>
            <td>${d.valid ? '<span class="badge bg-success">有效</span>' : '<span class="badge bg-danger">无效</span>'}</td>
        </tr>
    `).join('');

    if (parsedData.length > 50) {
        tbody.innerHTML += `<tr><td colspan="5" class="text-center text-muted py-2">... 仅显示前50条，共${parsedData.length}条</td></tr>`;
    }
}

function clearBatch() {
    document.getElementById('batchData').value = '';
    parsedData = [];
    document.getElementById('previewTable').innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">请先在左侧解析数据</td></tr>';
    document.getElementById('previewCount').textContent = '待解析';
    document.getElementById('totalCount').textContent = '0';
    document.getElementById('validCount').textContent = '0';
    document.getElementById('invalidCount').textContent = '0';
}

function confirmBatchCallback() {
    if (parsedData.length === 0) {
        alert('请先解析回传数据');
        return;
    }
    const valid = parsedData.filter(d => d.valid);
    if (!confirm(`确认批量回传 ${valid.length} 条有效记录？`)) {
        return;
    }
    // 批量回传逻辑
    alert('批量回传已提交！');
}
</script>
@endsection