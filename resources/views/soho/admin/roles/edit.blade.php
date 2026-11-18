@extends('soho::layouts.app')

@section('title', '编辑角色')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">编辑角色</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.admin.roles.update', $role) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">角色名称 <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">权限</label>
                <div class="row">
                    @foreach($permissions as $group => $items)
                    <div class="col-md-3 mb-2">
                        <div class="card">
                            <div class="card-header py-1">
                                <strong>{{ $group }}</strong>
                            </div>
                            <div class="card-body py-2">
                                @foreach($items as $permission)
                                <div class="form-check">
                                    <input type="checkbox" name="permissions[]" class="form-check-input" value="{{ $permission->id }}" id="perm_{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="{{ route('soho.admin.roles.index') }}" class="btn btn-secondary">返回</a>
            </div>
        </form>
    </div>
</div>
@endsection