@extends('soho::layouts.app')
@section('title', '仓库管理')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">仓库列表</h5>
        <a href="{{ route('soho.warehouse.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> 添加仓库</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>ID</th><th>名称</th><th>编码</th><th>类型</th><th>地址</th><th>状态</th><th>操作</th></tr></thead>
                <tbody>
                    @forelse($warehouses as $wh)
                    <tr>
                        <td>{{ $wh->id }}</td><td>{{ $wh->name }}</td><td>{{ $wh->code }}</td><td>{{ $wh->type }}</td><td>{{ $wh->province }}{{ $wh->city }}{{ $wh->district }}{{ $wh->address }}</td>
                        <td><span class="badge bg-{{ $wh->status ? 'success' : 'secondary' }}">{{ $wh->status ? '启用' : '禁用' }}</span></td>
                        <td><a href="{{ route('soho.warehouse.edit', $wh) }}" class="btn btn-sm btn-outline-primary">编辑</a>
                        <form method="POST" action="{{ route('soho.warehouse.destroy', $wh) }}" class="d-inline" onsubmit="return confirm('确定删除？')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">删除</button></form></td>
                    </tr>
                    @empty<tr><td colspan="7" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $warehouses->links() }}
    </div>
</div>
@endsection