@extends('soho::layouts.app')

@section('title', '配送方式管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">配送方式列表</h5>
        <a href="{{ route('shipping-methods.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 添加配送方式
        </a>
    </div>
    <div class="card-body">
        <!-- 搜索筛选表单 -->
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索名称/编码" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="1" @selected(request('status') == '1')>启用</option>
                        <option value="0" @selected(request('status') == '0')>禁用</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('shipping-methods.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <!-- 配送方式数据表格 -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>配送方式名称</th>
                        <th>编码</th>
                        <th>类型</th>
                        <th>关联物流公司</th>
                        <th>计费方式</th>
                        <th>排序</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shippingMethods as $method)
                    <tr>
                        <td>{{ $method->id }}</td>
                        <td>{{ $method->name }}</td>
                        <td>{{ $method->code ?? '-' }}</td>
                        <td>
                            @php
                                $typeMap = [
                                    'express' => ['label' => '快递配送', 'class' => 'primary'],
                                    'sms' => ['label' => '短信配送', 'class' => 'info'],
                                    'virtual' => ['label' => '虚拟发货', 'class' => 'warning'],
                                    'selfpickup' => ['label' => '到店自提', 'class' => 'success'],
                                    'local' => ['label' => '同城配送', 'class' => 'dark'],
                                ];
                                $typeInfo = $typeMap[$method->type] ?? ['label' => $method->type ?? '未知', 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $typeInfo['class'] }}">{{ $typeInfo['label'] }}</span>
                        </td>
                        <td>{{ $method->expressCompany->name ?? '无' }}</td>
                        <td>
                            @php
                                $billingMap = [
                                    'fixed' => '固定运费',
                                    'weight' => '按重量计费',
                                    'volume' => '按体积计费',
                                    'quantity' => '按件数计费',
                                ];
                                echo $billingMap[$method->billing_type] ?? ($method->billing_type ?? '-');
                            @endphp
                        </td>
                        <td>{{ $method->sort ?? 0 }}</td>
                        <td>
                            <span class="badge bg-{{ $method->status ? 'success' : 'secondary' }}">
                                {{ $method->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('shipping-methods.edit', $method) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i> 编辑
                            </a>
                            <form method="POST" action="{{ route('shipping-methods.destroy', $method) }}" class="d-inline" onsubmit="return confirm('确定删除此配送方式？删除后相关订单的配送信息将受影响。')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i> 删除
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2"></i>
                            <p>暂无配送方式数据</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                共 {{ $shippingMethods->total() }} 条数据
            </div>
            {{ $shippingMethods->links() }}
        </div>
    </div>
</div>
@endsection