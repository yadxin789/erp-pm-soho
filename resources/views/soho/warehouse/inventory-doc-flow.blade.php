@extends('soho::layouts.app')

@section('title', '库存单据 '.$doc->document_no)

@section('content')
@php
    $steps = ['draft' => '草稿', 'pending' => '待审批', 'approved' => '已审批', 'completed' => '已执行'];
    $idxMap = ['draft' => 0, 'pending' => 1, 'approved' => 2, 'completed' => 3];
    $current = $idxMap[$doc->status] ?? -1;
    $typeMap = ['inbound' => ['入库单', 'success'], 'outbound' => ['出库单', 'danger'], 'transfer' => ['调拨单', 'primary'], 'stocktake' => ['盘点单', 'info'], 'adjust' => ['调整单', 'secondary']];
    [$typeLabel, $typeColor] = $typeMap[$doc->document_type] ?? [$doc->document_type, 'secondary'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">
            <span class="badge bg-{{ $typeColor }}">{{ $typeLabel }}</span>
            <span class="font-monospace">{{ $doc->document_no }}</span>
        </h4>
        <div class="text-muted small">
            仓库 {{ $warehouse ? $warehouse->code.' '.$warehouse->name : '仓库#'.$doc->warehouse_id }}
            @if($doc->document_type === 'transfer')
                → {{ $target ? $target->code.' '.$target->name : '未指定目标仓' }}
            @endif
            ／ 制单人 {{ $operator ?: '-' }}
            ／ 审批人 {{ $approver ?: '-' }}
        </div>
    </div>
    <div class="d-flex gap-2">
        @if($doc->document_type === 'stocktake' && in_array($doc->status, ['draft', 'pending'], true) && can_perm('inventory.stocktake'))
            <a href="{{ route('soho.warehouse.inventory-doc.count-sheet', $doc->id) }}" class="btn btn-info btn-sm text-white">
                <i class="fa fa-clipboard-check me-1"></i> 批量录入实盘数
            </a>
        @endif
        <a href="{{ route('soho.warehouse.inventory-doc') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 返回单据
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            @foreach($steps as $key => $label)
                <div class="text-center flex-fill">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1"
                         style="width:34px;height:34px;{{ $current >= $idxMap[$key] ? 'background:#0d6efd;color:#fff;' : 'background:#e9ecef;color:#6c757d;' }}">
                        <i class="fa {{ $current >= $idxMap[$key] ? 'fa-check' : 'fa-circle-o' }}"></i>
                    </div>
                    <div class="small {{ $current >= $idxMap[$key] ? 'fw-semibold' : 'text-muted' }}">{{ $label }}</div>
                </div>
            @endforeach
        </div>
        @if($doc->status === 'cancelled')
            <div class="alert alert-secondary mt-3 mb-0 py-2"><i class="fa fa-ban me-1"></i> 该单据已取消：{{ $doc->remark ?: '无备注' }}</div>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">单据明细</h6>
                <span class="text-muted small">合计 {{ number_format((float) $doc->total_quantity) }} 件 / ¥{{ number_format((float) $doc->total_amount, 2) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SKU / 商品</th>
                                <th class="text-end">数量</th>
                                <th class="text-end">单位成本</th>
                                <th class="text-end">金额</th>
                                <th>批次号</th>
                                <th class="text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>
                                        <div>{{ $item->product_name ?: '-' }}</div>
                                        <div class="text-muted small font-monospace">SKU#{{ $item->sku_id }}</div>
                                    </td>
                                    <td class="text-end">{{ number_format((float) $item->quantity) }}</td>
                                    <td class="text-end">¥{{ number_format((float) $item->unit_cost, 2) }}</td>
                                    <td class="text-end">¥{{ number_format((float) $item->quantity * (float) $item->unit_cost, 2) }}</td>
                                    <td class="small font-monospace text-muted">{{ $item->batch_no ?: '-' }}</td>
                                    <td class="text-center">
                                        @if(in_array($doc->status, ['draft', 'pending'], true) && can_perm('inventory.document.create'))
                                            <form method="POST" action="{{ route('soho.warehouse.inventory-doc.item.remove', [$doc->id, $item->id]) }}">
                                                @csrf
                                                <button class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button>
                                            </form>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4"><i class="fa fa-inbox fa-2x d-block mb-2"></i>单据还没有明细行，请先在下方追加。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(in_array($doc->status, ['draft', 'pending'], true) && can_perm('inventory.document.create'))
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">追加明细</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('soho.warehouse.inventory-doc.item.add', $doc->id) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label small text-muted">SKU <span class="text-danger">*</span></label>
                            <select name="sku_id" class="form-select" required>
                                <option value="">请选择 SKU</option>
                                @foreach($skus as $sku)
                                    <option value="{{ $sku->id }}">{{ $sku->sku_code }}　{{ $sku->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">数量 <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="quantity" class="form-control" required placeholder="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">单位成本</label>
                            <input type="number" step="0.0001" name="unit_cost" class="form-control" placeholder="默认 SKU 成本">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">批次号</label>
                            <input type="text" name="batch_no" class="form-control" placeholder="可选">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100"><i class="fa fa-plus me-1"></i> 追加</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header"><h6 class="mb-0">单据操作</h6></div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    @if($doc->status === 'draft' && can_perm('inventory.document.create'))
                        <form method="POST" action="{{ route('soho.warehouse.inventory-doc.submit', $doc->id) }}">
                            @csrf
                            <button class="btn btn-primary"><i class="fa fa-paper-plane me-1"></i> 提交审批</button>
                        </form>
                    @elseif($doc->status === 'pending' && can_perm('inventory.approve'))
                        <form method="POST" action="{{ route('soho.warehouse.inventory-doc.approve', $doc->id) }}">
                            @csrf
                            <button class="btn btn-primary"><i class="fa fa-check me-1"></i> 审批通过</button>
                        </form>
                    @elseif($doc->status === 'approved' && can_perm('inventory.adjust'))
                        <form method="POST" action="{{ route('soho.warehouse.inventory-doc.execute', $doc->id) }}"
                              onsubmit="return confirm('确认执行？将立即变更库存并写库存事务/流水。')">
                            @csrf
                            <button class="btn btn-success"><i class="fa fa-play me-1"></i> 执行单据</button>
                        </form>
                    @elseif($doc->status === 'completed')
                        <div class="alert alert-success mb-0 py-2"><i class="fa fa-check-circle me-1"></i> 已执行完成（{{ $doc->completed_at }}），库存已更新。</div>
                    @endif

                    @if(! in_array($doc->status, ['completed', 'cancelled'], true) && can_perm('inventory.document.create'))
                        <form method="POST" action="{{ route('soho.warehouse.inventory-doc.cancel', $doc->id) }}"
                              onsubmit="return confirm('确认取消该单据？')">
                            @csrf
                            <button class="btn btn-outline-danger"><i class="fa fa-ban me-1"></i> 取消单据</button>
                        </form>
                    @endif
                </div>
                @if($doc->document_type === 'stocktake' && $doc->status !== 'completed')
                    <div class="form-text mt-2">盘点单执行时以「明细数量」作为实盘数，与当前实际库存的差异将自动生成调整（正差补、负差扣）。</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">单据信息</h6></div>
            <div class="card-body small">
                <div class="mb-1"><span class="text-muted">单据号：</span><span class="font-monospace">{{ $doc->document_no }}</span></div>
                <div class="mb-1"><span class="text-muted">类型：</span>{{ $typeLabel }}</div>
                <div class="mb-1"><span class="text-muted">状态：</span>{{ $steps[$doc->status] ?? ($doc->status === 'cancelled' ? '已取消' : $doc->status) }}</div>
                <div class="mb-1"><span class="text-muted">创建：</span>{{ $doc->created_at }}</div>
                <div class="mb-1"><span class="text-muted">审批：</span>{{ $doc->approved_at ?: '-' }}</div>
                <div><span class="text-muted">完成：</span>{{ $doc->completed_at ?: '-' }}</div>
                @if($doc->remark)<div class="mt-2 text-muted">{{ $doc->remark }}</div>@endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">库存流水（本单）</h6></div>
            <div class="card-body small">
                @forelse($flows as $f)
                    <div class="border-bottom pb-2 mb-2">
                        <div>{{ $f->type }}　<span class="fw-semibold">{{ number_format((float) $f->quantity) }}</span> 件</div>
                        <div class="text-muted">SKU#{{ $f->sku_id }}　仓#{{ $f->warehouse_id }}</div>
                        <div class="text-muted">{{ $f->before_quantity }} → {{ $f->after_quantity }}</div>
                        <div class="text-muted">{{ $f->created_at }}</div>
                    </div>
                @empty
                    <div class="text-muted">执行后生成库存流水。</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection


