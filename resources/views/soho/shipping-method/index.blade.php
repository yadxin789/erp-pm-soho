@extends('soho::layouts.app')

@section('title', '配送方式管理')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">配送方式管理</li>
    </ol>
</nav>

<!-- 统计卡片 -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <small class="text-muted">全部配送方式</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-success">
            <div class="card-body">
                <h3 class="mb-0 text-success">{{ $stats['active'] }}</h3>
                <small class="text-muted">启用中</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-secondary">
            <div class="card-body">
                <h3 class="mb-0 text-secondary">{{ $stats['inactive'] }}</h3>
                <small class="text-muted">已禁用</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">配送方式列表</h5>
        <div>
            <a href="{{ route('soho.shipping-method.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> 新建配送方式
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- 筛选表单 -->
        <form method="GET" action="{{ route('soho.shipping-method.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索名称或编码" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="fixed" @selected(request('type') == 'fixed')>固定运费</option>
                        <option value="weight" @selected(request('type') == 'weight')>按重量计费</option>
                        <option value="quantity" @selected(request('type') == 'quantity')>按件数计费</option>
                        <option value="region" @selected(request('type') == 'region')>按地区计费</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="1" @selected(request('status') === '1')>启用</option>
                        <option value="0" @selected(request('status') === '0')>禁用</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.shipping-method.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>编码</th>
                        <th>名称</th>
                        <th>计费类型</th>
                        <th>排序</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shippingMethods as $method)
                    <tr>
                        <td>{{ $method->id }}</td>
                        <td><code>{{ $method->code }}</code></td>
                        <td>{{ $method->name }}</td>
                        <td><span class="badge bg-info">{{ $method->type_label }}</span></td>
                        <td>{{ $method->sort }}</td>
                        <td>
                            <span class="badge bg-{{ $method->status_color }}">{{ $method->status_label }}</span>
                        </td>
                        <td>
                            <small>{{ $method->created_at?->format('Y-m-d H:i:s') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('soho.shipping-method.edit', $method) }}" class="btn btn-sm btn-outline-primary" title="编辑">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('soho.shipping-method.destroy', $method) }}" class="d-inline" onsubmit="return confirm('确定删除此配送方式？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="删除">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">暂无配送方式</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $shippingMethods->links() }}
    </div>
</div>
@endsection
