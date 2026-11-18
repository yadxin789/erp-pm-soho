@extends('soho::layouts.app')
@section('title', '快递公司')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">快递公司列表</h5>
        <a href="{{ route('soho.express.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> 添加</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>ID</th><th>名称</th><th>编码</th><th>电话</th><th>状态</th><th>操作</th></tr></thead>
                <tbody>
                    @forelse($companies as $c)
                    <tr><td>{{ $c->id }}</td><td>{{ $c->name }}</td><td>{{ $c->code }}</td><td>{{ $c->phone ?? '-' }}</td>
                        <td><span class="badge bg-{{ $c->status ? 'success' : 'secondary' }}">{{ $c->status ? '启用' : '禁用' }}</span></td>
                        <td><a href="{{ route('soho.express.edit', $c) }}" class="btn btn-sm btn-outline-primary">编辑</a>
                        <form method="POST" action="{{ route('soho.express.destroy', $c) }}" class="d-inline" onsubmit="return confirm('确定删除？')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">删除</button></form></td>
                    </tr>
                    @empty<tr><td colspan="6" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $companies->links() }}
    </div>
</div>
@endsection