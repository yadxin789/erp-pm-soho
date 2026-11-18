@extends('soho::layouts.app')

@section('title', '优惠券管理')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">优惠券管理</li>
    </ol>
</nav>

<!-- 统计卡片 -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <small class="text-muted">全部优惠券</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-success">
            <div class="card-body">
                <h3 class="mb-0 text-success">{{ $stats['active'] }}</h3>
                <small class="text-muted">启用中</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-secondary">
            <div class="card-body">
                <h3 class="mb-0 text-secondary">{{ $stats['inactive'] }}</h3>
                <small class="text-muted">已停用</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-danger">
            <div class="card-body">
                <h3 class="mb-0 text-danger">{{ $stats['expired'] }}</h3>
                <small class="text-muted">已过期</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">优惠券列表</h5>
        <div>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="fa fa-random"></i> 批量生成
            </button>
            <a href="{{ route('soho.coupon.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> 创建优惠券
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- 筛选表单 -->
        <form method="GET" action="{{ route('soho.coupon.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索优惠券码" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="fixed_discount" @selected(request('type') == 'fixed_discount')>固定金额</option>
                        <option value="percentage" @selected(request('type') == 'percentage')>百分比折扣</option>
                        <option value="shipping" @selected(request('type') == 'shipping')>包邮</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="active" @selected(request('status') == 'active')>启用</option>
                        <option value="inactive" @selected(request('status') == 'inactive')>停用</option>
                        <option value="expired" @selected(request('status') == 'expired')>已过期</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.coupon.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>优惠券码</th>
                        <th>类型</th>
                        <th>优惠详情</th>
                        <th>有效期</th>
                        <th>库存</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                    <tr>
                        <td>{{ $coupon->id }}</td>
                        <td><code>{{ $coupon->code }}</code></td>
                        <td><span class="badge bg-info">{{ $coupon->type_label }}</span></td>
                        <td>{{ $coupon->description }}</td>
                        <td>
                            <small>
                                {{ $coupon->start_at?->format('Y-m-d') ?? '-' }} ~ {{ $coupon->end_at?->format('Y-m-d') ?? '-' }}
                            </small>
                        </td>
                        <td>
                            <small>
                                {{ $coupon->used_quantity }} / {{ $coupon->total_quantity }}
                                <span class="text-muted">(剩余 {{ $coupon->remaining_quantity }})</span>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $coupon->status_color }}">{{ $coupon->status_label }}</span>
                            @if($coupon->is_active)
                                <span class="badge bg-success ms-1">有效</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('soho.coupon.edit', $coupon) }}" class="btn btn-sm btn-outline-primary" title="编辑">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('soho.coupon.destroy', $coupon) }}" class="d-inline" onsubmit="return confirm('确定删除此优惠券？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="删除" {{ $coupon->used_quantity > 0 ? 'disabled' : '' }}>
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">暂无优惠券</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $coupons->links() }}
    </div>
</div>

<!-- 批量生成弹窗 -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('soho.coupon.generate') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-random me-2"></i>批量生成优惠券</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">生成数量 <span class="text-danger">*</span></label>
                            <input type="number" name="count" class="form-control" min="1" max="1000" value="10" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">前缀</label>
                            <input type="text" name="prefix" class="form-control" value="CPN" maxlength="20">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">类型 <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="fixed_discount">固定金额</option>
                                <option value="percentage">百分比折扣</option>
                                <option value="shipping">包邮</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">优惠值 <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="value" class="form-control" required placeholder="金额或百分比">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">最低消费</label>
                            <input type="number" step="0.01" name="min_amount" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">每张发行量 <span class="text-danger">*</span></label>
                            <input type="number" name="total_quantity" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">开始时间 <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_at" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">结束时间 <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_at" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">状态</label>
                            <select name="status" class="form-select">
                                <option value="active">启用</option>
                                <option value="inactive">停用</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-random"></i> 批量生成</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection