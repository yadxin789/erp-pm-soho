@extends('soho::layouts.app')
@section('title', '分类管理')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">分类列表</h5>
        <a href="{{ route('soho.category.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> 添加分类</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>ID</th><th>名称</th><th>上级分类</th><th>排序</th><th>状态</th><th>操作</th></tr></thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td>{{ $cat->id }}</td><td>{{ $cat->name }}</td><td>{{ $cat->parent->name ?? '-' }}</td><td>{{ $cat->sort }}</td>
                        <td><span class="badge bg-{{ $cat->status ? 'success' : 'secondary' }}">{{ $cat->status ? '启用' : '禁用' }}</span></td>
                        <td><a href="{{ route('soho.category.edit', $cat) }}" class="btn btn-sm btn-outline-primary">编辑</a>
                        <form method="POST" action="{{ route('soho.category.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('确定删除？')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">删除</button></form></td>
                    </tr>
                    @empty<tr><td colspan="6" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $categories->links() }}
    </div>
</div>
@endsection