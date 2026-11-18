@extends('soho::layouts.app')

{{-- 页面标题 --}}
@section('title', '客户详情 - ' . $customer->name)

@section('content')
    {{-- 顶部操作栏 --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('soho.customer.index') }}">客户列表</a></li>
                <li class="breadcrumb-item active">{{ $customer->name }}</li>
            </ol>
        </nav>
        <div>
            <a href="{{ route('soho.customer.edit', $customer) }}" class="btn btn-sm btn-primary">
                <i class="fa fa-edit me-1"></i>编辑
            </a>
            <form method="POST" action="{{ route('soho.customer.destroy', $customer) }}"
                class="d-inline"
                onsubmit="return confirm('确定删除客户「{{ $customer->name }}」？')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fa fa-trash me-1"></i>删除
                </button>
            </form>
            <a href="{{ route('soho.customer.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    {{-- 客户基本信息 + 消费统计 --}}
    <div class="row g-3 mb-3">
        {{-- 左侧：客户资料卡片 --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-user me-2"></i>客户资料</h5>
                </div>
                <div class="card-body text-center">
                    {{-- 客户头像 --}}
                    <div class="user-avatar-lg mx-auto mb-3">
                        {{ mb_substr($customer->name, 0, 1) }}
                    </div>
                    <h4 class="mb-1">{{ $customer->name }}</h4>

                    {{-- 状态标签 --}}
                    @if($customer->status)
                        <span class="badge bg-success mb-2">启用</span>
                    @else
                        <span class="badge bg-secondary mb-2">停用</span>
                    @endif

                    {{-- 分组 --}}
                    @if($customer->group)
                        <div class="mb-3">
                            <span class="badge" style="background-color: {{ $customer->group->color ?? '#6c757d' }};">
                                <i class="fa fa-tag me-1"></i>{{ $customer->group->name }}
                            </span>
                        </div>
                    @endif

                    {{-- 联系信息列表 --}}
                    <ul class="list-group list-group-flush text-start mt-3">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fa fa-envelope text-primary me-2"></i>
                            <span class="text-muted me-2">邮箱：</span>
                            <span>{{ $customer->email ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fa fa-phone text-primary me-2"></i>
                            <span class="text-muted me-2">手机：</span>
                            <span>{{ $customer->phone ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fa fa-clock-o text-primary me-2"></i>
                            <span class="text-muted me-2">创建时间：</span>
                            <span>{{ $customer->created_at?->format('Y-m-d H:i') }}</span>
                        </li>
                    </ul>

                    {{-- 备注 --}}
                    @if($customer->notes)
                        <div class="mt-3 text-start">
                            <small class="text-muted">备注</small>
                            <p class="text-muted border rounded p-2 bg-light">{{ $customer->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 右侧：消费统计 --}}
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-bar-chart me-2"></i>消费统计</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- 消费总额 --}}
                        <div class="col-sm-4">
                            <div class="stat-card stat-danger">
                                <div class="stat-icon"><i class="fa fa-money"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">累计消费</div>
                                    <div class="stat-value">¥{{ number_format($stats['total_spent'], 2) }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- 订单数 --}}
                        <div class="col-sm-4">
                            <div class="stat-card stat-info">
                                <div class="stat-icon"><i class="fa fa-shopping-cart"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">订单总数</div>
                                    <div class="stat-value">{{ $stats['order_count'] }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- 客单价 --}}
                        <div class="col-sm-4">
                            <div class="stat-card stat-success">
                                <div class="stat-icon"><i class="fa fa-line-chart"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">客单价</div>
                                    <div class="stat-value">
                                        ¥{{ number_format($stats['order_count'] > 0 ? $stats['total_spent'] / $stats['order_count'] : 0, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 最近下单 --}}
                        <div class="col-sm-12">
                            <div class="stat-card stat-warning">
                                <div class="stat-icon"><i class="fa fa-calendar"></i></div>
                                <div class="stat-content">
                                    <div class="stat-label">最近下单时间</div>
                                    <div class="stat-value fs-5">
                                        {{ $stats['last_order_at'] ? $stats['last_order_at']->format('Y-m-d H:i') : '暂无订单记录' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 订单历史 --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-history me-2"></i>订单历史（最近 20 条）</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>订单号</th>
                            <th>平台</th>
                            <th>店铺</th>
                            <th>买家昵称</th>
                            <th class="text-end">订单金额</th>
                            <th>状态</th>
                            <th>下单时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orderHistory as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('soho.order.show', $order) }}">{{ $order->order_number }}</a>
                                </td>
                                <td>
                                    @if($order->platform_label)
                                        <span class="badge bg-info">{{ $order->platform_label }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $order->shop->name ?? '-' }}</td>
                                <td>{{ $order->buyer_nickname ?? ($order->buyer_nick ?? '-') }}</td>
                                <td class="text-end">
                                    <span class="text-danger">¥{{ number_format($order->total_amount ?? 0, 2) }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusBadgeMap = [
                                            'pending'   => 'warning',
                                            'paid'      => 'primary',
                                            'shipped'   => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'secondary',
                                            'frozen'    => 'danger',
                                        ];
                                        $statusClass = $statusBadgeMap[$order->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ $order->order_status_label ?? ($order->status ?? '未知') }}
                                    </span>
                                </td>
                                <td>{{ $order->order_created_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('soho.order.show', $order) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i>详情
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa fa-shopping-cart fa-2x mb-2 d-block"></i>
                                    暂无订单历史记录
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

{{-- 页面样式：头像、统计卡片 --}}
@section('head')
<style>
    .user-avatar-lg {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 36px;
        font-weight: bold;
    }

    .stat-card {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 8px;
        gap: 15px;
        height: 100%;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #fff;
        flex-shrink: 0;
    }

    .stat-danger  { background: #fff5f5; }
    .stat-danger .stat-icon { background: #e74a3b; }
    .stat-info   { background: #eef7ff; }
    .stat-info .stat-icon  { background: #398af7; }
    .stat-success{ background: #f0faf3; }
    .stat-success .stat-icon { background: #21a468; }
    .stat-warning{ background: #fff8ec; }
    .stat-warning .stat-icon { background: #f5a623; }

    .stat-label {
        color: #666;
        font-size: 0.85rem;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
    }
</style>
@endsection
