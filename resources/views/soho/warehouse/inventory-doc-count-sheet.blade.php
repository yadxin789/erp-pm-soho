@extends('soho::layouts.app')

@section('title', '盘点实盘录入 '.$doc->document_no)

@section('content')
@php
    $totalCurrent = 0;
    foreach ($rows as $r) { $totalCurrent += (float) ($r->actual_quantity ?? 0); }
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-1"><i class="fa fa-clipboard-check text-info me-1"></i>盘点实盘录入</h5>
        <div class="text-muted small">
            单据 <span class="font-monospace">{{ $doc->document_no }}</span>
            · 仓库 {{ $warehouse ? $warehouse->code.' '.$warehouse->name : '仓库#'.$doc->warehouse_id }}
            · 状态 {{ $doc->status === 'pending' ? '待审批' : '草稿' }}
        </div>
    </div>
    <a href="{{ route('soho.warehouse.inventory-doc.flow', $doc->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left me-1"></i> 返回单据详情
    </a>
</div>

<div class="alert alert-info py-2 small">
    <i class="fa fa-circle-info me-1"></i>
    表中「账面数」为本仓当前可用库存，请只填写实际盘到的数量；<b>留空的 SKU 视为本次不盘点</b>。
    保存后覆盖本单明细，执行单据时按「实盘数 − 账面数」自动补/扣库存。
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">按 SKU 录入（共 {{ $rows->count() }} 个启用 SKU）</h6>
        <div class="d-flex gap-2 align-items-center">
            <span class="small text-muted">已录入 <span id="filled-count">0</span> 项</span>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-clear">清空</button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-fill-book">全部按账面数填充</button>
        </div>
    </div>
    <div class="card-body p-0">
        <form method="POST" action="{{ route('soho.warehouse.inventory-doc.count-sheet.save', $doc->id) }}" id="count-form">
            @csrf
            <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th style="width:40px;" class="text-center">#</th>
                            <th>SKU 编码</th>
                            <th>商品名称</th>
                            <th class="text-end" style="width:110px;">账面数</th>
                            <th class="text-end" style="width:110px;">锁定</th>
                            <th style="width:170px;">实盘数</th>
                            <th class="text-end" style="width:110px;">差异</th>
                            <th style="width:90px;" class="text-center">上次盘点</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $r)
                            @php
                                $book = (float) ($r->actual_quantity ?? 0);
                                $locked = (float) ($r->locked_quantity ?? 0);
                                $prev = $existing->get($r->id);
                            @endphp
                            <tr>
                                <td class="text-center text-muted small">{{ $i + 1 }}</td>
                                <td class="font-monospace small">{{ $r->sku_code }}</td>
                                <td>{{ $r->name }}</td>
                                <td class="text-end" data-book="{{ $book }}">{{ number_format($book) }}</td>
                                <td class="text-end text-muted">{{ number_format($locked) }}</td>
                                <td>
                                    <input type="number" step="1" min="0" name="items[{{ $r->id }}]"
                                           class="form-control form-control-sm text-end js-qty"
                                           data-book="{{ $book }}"
                                           value="{{ $prev ? (int) $prev->quantity : '' }}"
                                           placeholder="不盘点">
                                </td>
                                <td class="text-end js-diff {{ $prev ? '' : 'text-muted' }}">—</td>
                                <td class="text-center small text-muted">{{ $prev ? number_format((float) $prev->quantity) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    保存后请回到单据详情执行「提交审批」流程；盘点单在「已审批」状态才能执行落账。
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('soho.warehouse.inventory-doc.flow', $doc->id) }}" class="btn btn-outline-secondary">取消</a>
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save me-1"></i> 保存实盘数</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var form = document.getElementById('count-form');
    var inputs = Array.prototype.slice.call(form.querySelectorAll('.js-qty'));
    var filled = document.getElementById('filled-count');

    function diffOf(input) {
        var v = input.value.trim();
        if (v === '') { return null; }
        return parseFloat(v) - parseFloat(input.dataset.book || 0);
    }

    function refresh() {
        var n = 0;
        inputs.forEach(function (input) {
            var d = diffOf(input);
            var cell = input.closest('tr').querySelector('.js-diff');
            if (d === null) {
                cell.textContent = '—';
                cell.className = 'text-end js-diff text-muted';
            } else {
                n++;
                cell.textContent = (d > 0 ? '+' : '') + d.toLocaleString();
                cell.className = 'text-end js-diff fw-semibold ' + (d === 0 ? 'text-muted' : (d > 0 ? 'text-success' : 'text-danger'));
            }
        });
        filled.textContent = n;
    }

    form.addEventListener('input', function (e) { if (e.target.classList.contains('js-qty')) { refresh(); } });
    document.getElementById('btn-clear').addEventListener('click', function () {
        inputs.forEach(function (i) { i.value = ''; });
        refresh();
    });
    document.getElementById('btn-fill-book').addEventListener('click', function () {
        inputs.forEach(function (i) { i.value = parseInt(i.dataset.book || 0, 10); });
        refresh();
    });
    refresh();
})();
</script>
@endsection
