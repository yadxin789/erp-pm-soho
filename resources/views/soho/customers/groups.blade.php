@extends('soho::layouts.app')

{{-- 页面标题 --}}
@section('title', '客户分组管理')

@section('content')
<div class="card mb-3">
    {{-- 卡片头部 --}}
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa fa-tags me-2"></i>客户分组管理
        </h5>
        <div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                <i class="fa fa-plus me-1"></i>新建分组
            </button>
            <a href="{{ route('soho.customer.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-users me-1"></i>客户列表
            </a>
        </div>
    </div>

    <div class="card-body">
        {{-- 搜索框 --}}
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                        placeholder="搜索分组名称 / 编码"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i>搜索
                    </button>
                    <a href="{{ route('soho.customer.groups') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-refresh me-1"></i>重置
                    </a>
                </div>
            </div>
        </form>

        {{-- 分组卡片展示 --}}
        @if($groups->count() > 0)
            <div class="row g-3">
                @foreach($groups as $group)
                    <div class="col-md-6 col-lg-4">
                        <div class="card group-card h-100">
                            {{-- 颜色条 --}}
                            <div class="group-color-bar" style="background-color: {{ $group->color ?? '#6c757d' }};"></div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1">
                                            <span class="badge me-1" style="background-color: {{ $group->color ?? '#6c757d' }};">
                                                {{ $group->name }}
                                            </span>
                                        </h5>
                                        <small class="text-muted">编码：{{ $group->code ?? '-' }}</small>
                                    </div>
                                    <span class="badge bg-info fs-6">
                                        <i class="fa fa-user me-1"></i>{{ $group->customers_count }} 位客户
                                    </span>
                                </div>

                                {{-- 排序 --}}
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fa fa-sort-numeric-asc me-1"></i>
                                        排序：{{ $group->sort ?? 0 }}
                                    </small>
                                </div>

                                {{-- 备注 --}}
                                @if($group->remark)
                                    <p class="text-muted small mb-3 border rounded p-2 bg-light">
                                        {{ $group->remark }}
                                    </p>
                                @endif

                                {{-- 操作按钮：通过 JS 弹窗操作，预留后端接口 --}}
                                <div class="d-flex gap-1 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editGroupModal"
                                        data-id="{{ $group->id }}"
                                        data-name="{{ $group->name }}"
                                        data-code="{{ $group->code }}"
                                        data-color="{{ $group->color }}"
                                        data-sort="{{ $group->sort }}"
                                        data-remark="{{ $group->remark }}">
                                        <i class="fa fa-edit me-1"></i>编辑
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="deleteGroup({{ $group->id }}, '{{ $group->name }}')">
                                        <i class="fa fa-trash me-1"></i>删除
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="fa fa-tags fa-2x mb-2 d-block"></i>
                <p class="mb-3">暂无分组，请点击右上角「新建分组」创建</p>
            </div>
        @endif
    </div>
</div>

{{-- 新建分组弹窗（使用命名路由 customer.groups.store） --}}
<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('soho.customer.groups.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-plus me-2"></i>新建分组</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">分组名称 <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                            placeholder="如：VIP 客户、普通客户">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">分组编码</label>
                        <input type="text" name="code" class="form-control"
                            placeholder="英文标识，如：vip">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">标识颜色</label>
                        <input type="color" name="color" class="form-control form-control-color"
                            value="#667eea">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">排序</label>
                        <input type="number" name="sort" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>创建分组
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 编辑分组弹窗（通过 JS 填充数据，使用命名路由 customer.groups.update） --}}
<div class="modal fade" id="editGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editGroupForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-edit me-2"></i>编辑分组</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">分组名称 <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="groupName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">分组编码</label>
                        <input type="text" name="code" id="groupCode" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">标识颜色</label>
                        <input type="color" name="color" id="groupColor" class="form-control form-control-color">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">排序</label>
                        <input type="number" name="sort" id="groupSort" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" id="groupRemark" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>保存修改
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- 页面脚本：编辑/删除分组操作 --}}
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModal = document.getElementById('editGroupModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id     = button.getAttribute('data-id');
                const name   = button.getAttribute('data-name');
                const code   = button.getAttribute('data-code');
                const color  = button.getAttribute('data-color');
                const sort   = button.getAttribute('data-sort');
                const remark = button.getAttribute('data-remark');

                // 根据分组 ID 动态设置表单 action
                const form = document.getElementById('editGroupForm');
                form.action = '/customer/groups/' + id;

                document.getElementById('groupName').value   = name;
                document.getElementById('groupCode').value   = code || '';
                document.getElementById('groupColor').value  = color || '#667eea';
                document.getElementById('groupSort').value   = sort || 0;
                document.getElementById('groupRemark').value = remark || '';
            });
        }
    });

    // 删除分组：提交 DELETE 表单
    function deleteGroup(id, name) {
        if (!confirm('确定删除分组「' + name + '」？分组下客户将变为未分组状态。')) return false;

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/customer/groups/' + id;
        form.innerHTML = '<input type="hidden" name="_token" value="' + token + '">'
            + '<input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection

{{-- 页面样式 --}}
@section('head')
<style>
    .group-card {
        border: 1px solid #e4e7ed;
        transition: box-shadow 0.2s;
    }

    .group-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .group-color-bar {
        height: 4px;
        border-radius: 0;
    }
</style>
@endsection
