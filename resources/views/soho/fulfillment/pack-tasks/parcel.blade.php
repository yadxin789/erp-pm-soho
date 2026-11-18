@extends('soho::layouts.app')

@section('title', '包裹生成')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.pack.tasks.index') }}">待打包任务</a></li>
        <li class="breadcrumb-item active">包裹生成</li>
    </ol>
</nav>

<div class="row">
    {{-- 左侧：任务和已录入信息 --}}
    <div class="col-lg-4">
        {{-- 打包任务信息 --}}
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fa fa-box me-2"></i>打包任务</h5>
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
                <hr>
                <h6>已录入信息</h6>
                <div class="mb-1">
                    <small class="text-muted">包材：</small>{{ $task->selected_material_name ?? '未选择' }}
                </div>
                <div class="mb-1">
                    <small class="text-muted">实际重量：</small>{{ $task->actual_weight ?? 0 }} kg
                </div>
                <div class="mb-1">
                    <small class="text-muted">计费重量：</small>{{ $task->charge_weight ?? 0 }} kg
                </div>
                <div class="mb-1">
                    <small class="text-muted">尺寸：</small>{{ $task->length ?? 0 }}×{{ $task->width ?? 0 }}×{{ $task->height ?? 0 }} cm
                </div>
            </div>
        </div>
    </div>

    {{-- 右侧：包裹生成 --}}
    <div class="col-lg-8">
        {{-- 包裹信息卡片 --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-box me-2"></i>生成包裹</h5>
                <span class="badge bg-info">待生成</span>
            </div>
            <div class="card-body">
                <form id="parcelForm" onsubmit="return generateParcel(event)">
                    {{-- 寄收信息 --}}
                    <h6 class="mb-3"><i class="fa fa-truck me-1"></i>收寄信息</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small">收件人</label>
                            <input type="text" class="form-control" value="{{ $task->receiver_name ?? '' }}" readonly>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small">联系电话</label>
                            <input type="text" class="form-control" value="{{ $task->receiver_phone ?? '' }}" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">收货地址</label>
                            <input type="text" class="form-control" value="{{ $task->receiver_address ?? '' }}" readonly>
                        </div>
                    </div>

                    {{-- 物流选择 --}}
                    <h6 class="mb-3"><i class="fa fa-truck me-1"></i>物流选择</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small">快递公司</label>
                            <select name="express_company_id" class="form-select" required>
                                <option value="">请选择快递公司</option>
                                @foreach($expressCompanies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small">物流单号</label>
                            <div class="input-group">
                                <input type="text" name="tracking_no" class="form-control" placeholder="留空自动生成">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateTrackingNo()">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 包裹服务 --}}
                    <h6 class="mb-3"><i class="fa fa-cogs me-1"></i>附加服务</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-sm-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="insured" id="insured">
                                <label class="form-check-label" for="insured">保价服务</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cod" id="cod">
                                <label class="form-check-label" for="cod">代收货款</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="sms_notify" id="sms_notify" checked>
                                <label class="form-check-label" for="sms_notify">短信通知</label>
                            </div>
                        </div>
                    </div>

                    {{-- 商品清单 --}}
                    <h6 class="mb-3"><i class="fa fa-list me-1"></i>包裹商品清单</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>商品</th>
                                    <th>数量</th>
                                    <th>重量</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($task->items ?? [] as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->required_qty }}</td>
                                    <td>{{ $item->weight ?? 0 }} kg</td>
                                    <td><span class="badge bg-success">已打包</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted">-</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- 费用预览 --}}
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="mb-2">费用预览</h6>
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <small class="text-muted">基础运费</small>
                                    <div class="fw-bold">¥{{ $task->base_fee ?? 0 }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted">续重费</small>
                                    <div class="fw-bold">¥{{ $task->续重费 ?? 0 }}</div>
                                </div>
                                <div class="col-sm-4">
                                    <small class="text-muted">合计</small>
                                    <div class="fw-bold text-danger fs-5">¥{{ $task->total_fee ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 操作按钮 --}}
                    <div class="d-flex gap-2">
                        <a href="{{ route('soho.pack.tasks.weight', $task) }}" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left me-1"></i>返回称重
                        </a>
                        <button type="button" class="btn btn-outline-warning" onclick="saveDraft()">
                            <i class="fa fa-save me-1"></i>保存草稿
                        </button>
                        <button type="submit" class="btn btn-success ms-auto">
                            <i class="fa fa-check me-1"></i>确认生成包裹
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 已生成包裹列表 --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fa fa-list me-2"></i>已生成包裹（本任务）</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>包裹号</th>
                                <th>快递公司</th>
                                <th>物流单号</th>
                                <th>重量</th>
                                <th>状态</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($task->parcels ?? [] as $parcel)
                            <tr>
                                <td><code>{{ $parcel->parcel_no }}</code></td>
                                <td>{{ $parcel->express_company_name }}</td>
                                <td>{{ $parcel->tracking_no }}</td>
                                <td>{{ $parcel->charge_weight }} kg</td>
                                <td>
                                    <span class="badge bg-success">待发货</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">暂无已生成包裹</td>
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
function generateTrackingNo() {
    const prefix = 'SF';
    const timestamp = Date.now().toString().slice(-10);
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    document.querySelector('input[name="tracking_no"]').value = prefix + timestamp + random;
}

function saveDraft() {
    alert('草稿已保存！');
}

function generateParcel(e) {
    e.preventDefault();
    const expressCompany = document.querySelector('select[name="express_company_id"]').value;
    if (!expressCompany) {
        alert('请选择快递公司');
        return false;
    }
    alert('包裹生成成功！');
    return false;
}
</script>
@endsection