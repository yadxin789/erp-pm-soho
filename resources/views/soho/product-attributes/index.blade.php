@extends('soho::layouts.app')

@section('title', '商品属性')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">商品属性</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">商品属性列表</h5>
        <a href="{{ route('soho.product-attribute.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 添加属性
        </a>
    </div>
    <div class="card-body">
        <!-- 筛选表单 -->
        <form method="GET" action="{{ route('soho.product-attribute.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索属性名称/编码" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="text" @selected(request('type') == 'text')>文本输入</option>
                        <option value="number" @selected(request('type') == 'number')>数字输入</option>
                        <option value="select" @selected(request('type') == 'select')>下拉选择</option>
                        <option value="multi_select" @selected(request('type') == 'multi_select')>多选下拉</option>
                        <option value="date" @selected(request('type') == 'date')>日期选择</option>
                    </select>
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
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.product-attribute.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>属性名称</th>
                        <th>属性编码</th>
                        <th>类型</th>
                        <th>选项</th>
                        <th>属性值</th>
                        <th>排序</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attributes as $attr)
                    <tr>
                        <td>{{ $attr->id }}</td>
                        <td>{{ $attr->name }}</td>
                        <td><code>{{ $attr->code }}</code></td>
                        <td><span class="badge bg-info">{{ $attr->type_label }}</span></td>
                        <td>{{ $attr->option_count > 0 ? $attr->option_count . ' 个' : '-' }}</td>
                        <td>
                            @if($attr->value_count > 0)
                                <ul class="list-inline mb-0">
                                    @foreach($attr->values as $val)
                                        <li class="list-inline-item"><span class="badge bg-light text-dark border">{{ $val->value_name }}</span></li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $attr->sort }}</td>
                        <td>
                            <span class="badge bg-{{ $attr->status ? 'success' : 'secondary' }}">
                                {{ $attr->status_label }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('soho.product-attribute.edit', $attr) }}" class="btn btn-sm btn-outline-primary" title="编辑">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('soho.product-attribute.destroy', $attr) }}" class="d-inline" onsubmit="return confirm('确定删除此属性？关联的属性值也将被删除！')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="删除">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $attributes->links() }}
    </div>
</div>
@endsection