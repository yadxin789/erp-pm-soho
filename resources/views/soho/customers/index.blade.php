@extends('soho::layouts.app')

{{-- 页面标题 --}}
@section('title', '客户管理')

@section('content')
<div class="card mb-3">
    {{-- 卡片头部：标题 + 创建按钮 --}}
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa fa-users me-2"></i>客户列表
        </h5>
        <div>
            <a href="{{ route('soho.customer.groups') }}" class="btn btn-outline-secondary btn-sm me-1">
                <i class="fa fa-tags me-1"></i>分组管理
            </a>
            <a href="{{ route('soho.customer.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus me-1"></i>新建客户
            </a>
        </div>
    </div>

    <div class="card-body">
        {{-- 搜索与筛选表单 --}}
        <form method="GET" class="mb-3">
            <div class="row g-2">
                {{-- 关键词搜索 --}}
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control"
                        placeholder="姓名 / 邮箱 / 手机号"
                        value="{{ request('search') }}">
                </div>

                {{-- 分组筛选 --}}
                <div class="col-md-2">
                    <select name="group_id" class="form-select">
                        <option value="">全部分组</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}"
                                @selected(request('group_id') == $group->id)>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 状态筛选 --}}
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="1" @selected(request('status') === '1')>启用</option>
                        <option value="0" @selected(request('status') === '0')>停用</option>
                    </select>
                </div>

                {{-- 排序 --}}
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="">默认排序</option>
                        <option value="total_spent_desc" @selected(request('sort') == 'total_spent_desc')>消费金额：从高到低</option>
                        <option value="total_spent_asc" @selected(request('sort') == 'total_spent_asc')>消费金额：从低到高</option>
                        <option value="order_count_desc" @selected(request('sort') == 'order_count_desc')>订单数：从多到少</option>
                        <option value="last_order_desc" @selected(request('sort') == 'last_order_desc')>最近下单时间</option>
                    </select>
                </div>

                {{-- 操作按钮 --}}
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i>筛选
                    </button>
                    <a href="{{ route('soho.customer.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-refresh me-1"></i>重置
                    </a>
                </div>
            </div>
        </form>

        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">ID</th>
                        <th>客户信息</th>
                        <th>联系方式</th>
                        <th>分组</th>
                        <th class="text-end">消费总额</th>
                        <th class="text-center">订单数</th>
                        <th>最近下单</th>
                        <th>状态</th>
                        <th width="180">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>

                            {{-- 客户姓名 --}}
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-sm me-2">
                                        {{ mb_substr($customer->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $customer->name }}</div>
                                        @if($customer->notes)
                                            <small class="text-muted">{{ mb_strimwidth($customer->notes, 0, 20) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- 联系方式 --}}
                            <td>
                                <div><i class="fa fa-envelope text-muted me-1"></i>{{ $customer->email ?? '-' }}</div>
                                <div><i class="fa fa-phone text-muted me-1"></i>{{ $customer->phone ?? '-' }}</div>
                            </td>

                            {{-- 分组 --}}
                            <td>
                                @if($customer->group)
                                    <span class="badge"
                                        style="background-color: {{ $customer->group->color ?? '#6c757d' }};">
                                        {{ $customer->group->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- 消费总额 --}}
                            <td class="text-end">
                                <span class="text-danger fw-bold">¥{{ number_format($customer->total_spent ?? 0, 2) }}</span>
                            </td>

                            {{-- 订单数 --}}
                            <td class="text-center">
                                <span class="badge bg-info">{{ $customer->order_count ?? 0 }}</span>
                            </td>

                            {{-- 最近下单时间 --}}
                            <td>
                                {{ $customer->last_order_at ? $customer->last_order_at->format('Y-m-d H:i') : '-' }}
                            </td>

                            {{-- 状态 --}}
                            <td>
                                @if($customer->status)
                                    <span class="badge bg-success">启用</span>
                                @else
                                    <span class="badge bg-secondary">停用</span>
                                @endif
                            </td>

                            {{-- 操作按钮 --}}
                            <td>
                                <a href="{{ route('soho.customer.show', $customer) }}"
                                    class="btn btn-sm btn-outline-info" title="详情">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('soho.customer.edit', $customer) }}"
                                    class="btn btn-sm btn-outline-primary" title="编辑">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form method="POST"
                                    action="{{ route('soho.customer.destroy', $customer) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('确定删除客户「{{ $customer->name }}」？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="删除">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                暂无客户数据
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                共 {{ $customers->total() }} 条记录，当前第 {{ $customers->currentPage() }} / {{ $customers->lastPage() }} 页
            </small>
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection

{{-- 内联样式：头像 sm --}}
@section('head')
<style>
    .user-avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: bold;
        flex-shrink: 0;
    }
</style>
@endsection
