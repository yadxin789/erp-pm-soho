@extends('soho::layouts.app')

@section('title', '部门管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">部门列表</h5>
        <a href="{{ route('soho.admin.departments.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 添加部门
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>部门名称</th>
                        <th>部门编码</th>
                        <th>上级部门</th>
                        <th>排序</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                    <tr>
                        <td>{{ $dept->id }}</td>
                        <td>{{ $dept->name }}</td>
                        <td>{{ $dept->code ?? '-' }}</td>
                        <td>{{ $dept->parent->name ?? '-' }}</td>
                        <td>{{ $dept->sort }}</td>
                        <td>
                            <span class="badge bg-{{ $dept->status ? 'success' : 'secondary' }}">
                                {{ $dept->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('soho.admin.departments.edit', $dept) }}" class="btn btn-sm btn-outline-primary">编辑</a>
                            <form method="POST" action="{{ route('soho.admin.departments.destroy', $dept) }}" class="d-inline" onsubmit="return confirm('确定删除？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">删除</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">暂无数据</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $departments->links() }}
    </div>
</div>
@endsection