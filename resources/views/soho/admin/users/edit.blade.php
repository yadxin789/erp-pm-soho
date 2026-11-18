@extends('soho::layouts.app')

@section('title', '编辑用户')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">编辑用户</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.admin.users.update', $user) }}">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">用户名 <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">邮箱 <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">密码 <small class="text-muted">(留空则不修改)</small></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">确认密码</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">真实姓名</label>
                        <input type="text" name="real_name" class="form-control" value="{{ old('real_name', $user->real_name) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">手机号</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="status" class="form-check-input" id="status" value="1" {{ old('status', $user->status) ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">启用账号</label>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">保存修改</button>
                <a href="{{ route('soho.admin.users.index') }}" class="btn btn-secondary">返回</a>
            </div>
        </form>
    </div>
</div>
@endsection