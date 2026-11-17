@extends('core::layouts.base')
@section('title', '组件示例 · '.config('app.name'))

@section('content')
    <h1 class="dw-page-title">共享组件示例</h1>
    <p class="dw-page-sub">T0-10 前端基座自检页：9 个组件在同一页渲染，用于视觉与无障碍回归。</p>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            @include('core::components.stat-card', ['label' => '待处理订单', 'value' => '128', 'delta' => '+12 较昨日', 'icon' => 'fa-receipt'])
        </div>
        <div class="col-md-4">
            @include('core::components.stat-card', ['label' => '在途项目', 'value' => '36', 'delta' => '3 个逾期', 'trend' => 'down', 'icon' => 'fa-diagram-project'])
        </div>
        <div class="col-md-4">
            @include('core::components.stat-card', ['label' => '库存预警', 'value' => '7', 'icon' => 'fa-boxes-stacked'])
        </div>
    </div>

    <div class="mb-3">
        @include('core::components.filter-bar', [
            'fields' => [
                ['name' => 'keyword', 'label' => '关键词', 'placeholder' => '订单号 / 客户', 'value' => ''],
                ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['' => '全部', 'paid' => '已付款', 'shipped' => '已发货']],
            ],
        ])
    </div>

    <div class="mb-3">
        @include('core::components.data-table', [
            'title' => '最近订单',
            'columns' => ['no' => '订单号', 'customer' => '客户', 'amount' => '金额', 'status' => '状态'],
            'rows' => [
                ['no' => 'SO-2026-0042', 'customer' => '华强电子', 'amount' => '¥ 12,800', 'status' => '<span class="dw-badge dw-badge--ok">已发货</span>'],
                ['no' => 'SO-2026-0041', 'customer' => '立创供应链', 'amount' => '¥ 6,300', 'status' => '<span class="dw-badge dw-badge--warn">待出库</span>'],
            ],
        ])
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            @include('core::components.chart', ['title' => '近 5 日订单量', 'labels' => ['11-13', '11-14', '11-15', '11-16', '11-17'], 'values' => [42, 55, 48, 70, 61]])
        </div>
        <div class="col-md-6">
            <div class="dw-card">
                <div class="dw-card-title">表单与校验</div>
                @include('core::components.form-field', ['name' => 'email', 'label' => '邮箱', 'type' => 'email', 'required' => true, 'error' => '请输入有效的邮箱地址', 'help' => '用于接收审批通知'])
                @include('core::components.form-field', ['name' => 'remark', 'label' => '备注', 'type' => 'textarea'])
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" type="button" data-dialog-open="demo-dialog">打开弹窗</button>
                    @include('core::components.badge', ['type' => 'info', 'text' => '原生 dialog'])
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        @php
            $demoPaginator = new \Illuminate\Pagination\LengthAwarePaginator(range(1, 50), 50, 10, 2, ['path' => url('/core/ui')]);
        @endphp
        @include('core::components.pagination', ['paginator' => $demoPaginator])
    </div>

    <div class="mt-3">
        @include('core::components.empty-state', ['title' => '还没有数据', 'desc' => '导入或新建后可在此查看'])
    </div>

    @include('core::components.modal', ['id' => 'demo-dialog', 'title' => '确认删除该记录？', 'body' => '<p>删除后不可恢复，请确认。</p>', 'confirmText' => '删除', 'confirmType' => 'danger'])
@endsection