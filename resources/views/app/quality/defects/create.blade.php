@extends('layouts.business')

@section('title', '登记缺陷记录')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-triangle-exclamation"></i>登记缺陷记录</h2>
        <a href="{{ erp_route('app.quality.defects.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ erp_route('app.quality.defects.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="defect_type">缺陷类型 <span class="text-danger">*</span></label>
                        <select id="defect_type" name="defect_type" class="form-select" required>
                            @foreach($types as $key => $value)
                                <option value="{{ $key }}" @selected(old('defect_type') === $key)>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="defect_code_id">缺陷代码</label>
                        <select id="defect_code_id" name="defect_code_id" class="form-select">
                            <option value="">不指定</option>
                            @foreach($codes as $code)
                                <option value="{{ $code->id }}" @selected(old('defect_code_id') == $code->id)>{{ $code->category }} / {{ $code->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="item_id">物料</label>
                        <select id="item_id" name="item_id" class="form-select">
                            <option value="">不指定</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="production_order_id">生产订单</label>
                        <select id="production_order_id" name="production_order_id" class="form-select">
                            <option value="">不指定</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" @selected(old('production_order_id') == $order->id)>{{ $order->order_no ?? ('#'.$order->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="batch_number">批次号</label>
                        <input type="text" id="batch_number" name="batch_number" class="form-control" value="{{ old('batch_number') }}" maxlength="50">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="quantity">缺陷数量 <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" min="0.0001" id="quantity" name="quantity" class="form-control" required value="{{ old('quantity') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="disposition">处置方式 <span class="text-danger">*</span></label>
                        <select id="disposition" name="disposition" class="form-select" required>
                            @foreach(['pending' => '待处置', 'rework' => '返工', 'reject' => '退货', 'concession' => '让步接收', 'scrap' => '报废'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('disposition', 'pending') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">缺陷描述 <span class="text-danger">*</span></label>
                    <textarea id="description" name="description" class="form-control" rows="3" required maxlength="2000">{{ old('description') }}</textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="root_cause">原因分析</label>
                        <textarea id="root_cause" name="root_cause" class="form-control" rows="2" maxlength="2000">{{ old('root_cause') }}</textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="corrective_action">纠正措施</label>
                        <textarea id="corrective_action" name="corrective_action" class="form-control" rows="2" maxlength="2000">{{ old('corrective_action') }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ erp_route('app.quality.defects.index') }}" class="btn btn-outline-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
