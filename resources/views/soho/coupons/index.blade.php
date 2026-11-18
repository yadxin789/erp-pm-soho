@extends('soho::layouts.app')

@section('title', '优惠券管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">优惠券列表</h5>
        <a href="{{ route('soho.coupon.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 创建优惠券
        </a>
    </div>
    <div class="card-body">
        <!-- 搜索筛选表单 -->
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索券名称/编码" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="fixed" @selected(request('type') == 'fixed')>固定金额券</option>
                        <option value="percent" @selected(request('type') == 'percent')>百分比折扣券</option>
                        <option value="shipping" @selected(request('type') == 'shipping')>包邮券</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="enabled" @selected(request('status') == 'enabled')>启用</option>
                        <option value="disabled" @selected(request('status') == 'disabled')>禁用</option>
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
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>券名称</th>
                        <th>券编码</th>
                        <th>类型</th>
                        <th>面值</th>
                        <th>最低消费</th>
                        <th>有效期</th>
                        <th>领取/总数</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                    <tr>
                        <td>{{ $coupon->id }}</td>
                        <td>{{ $coupon->name }}</td>
                        <td><code>{{ $coupon->code }}</code></td>
                        <td>
                            @php
                                $typeMap = [
                                    'fixed' => ['label' => '固定金额', 'class' => 'primary'],
                                    'percent' => ['label' => '百分比折扣', 'class' => 'info'],
                                    'shipping' => ['label' => '包邮券', 'class' => 'success'],
                                ];
                                $typeInfo = $typeMap[$coupon->type] ?? ['label' => $coupon->type ?? '未知', 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $typeInfo['class'] }}">{{ $typeInfo['label'] }}</span>
                        </td>
                        <td>
                            @if($coupon->type == 'fixed')
                                <span class="text-danger fw-bold">¥{{ number_format($coupon->value, 2) }}</span>
                            @elseif($coupon->type == 'percent')
                                <span class="text-danger fw-bold">{{ number_format($coupon->value, 2) }}%</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $coupon->min_amount > 0 ? '¥' . number_format($coupon->min_amount, 2) : '无门槛' }}</td>
                        <td>
                            {{ $coupon->start_date ? $coupon->start_date->format('Y-m-d') : '-' }}
                            ~
                            {{ $coupon->end_date ? $coupon->end_date->format('Y-m-d') : '-' }}
                        </td>
                        <td>
                            <span class="badge bg-warning">{{ $coupon->received ?? 0 }}</span>
                            /
                            <span>{{ $coupon->total ?? '不限' }}</span>
                        </td>
                        <td>
                            @php
                                $statusMap = [
                                    'enabled' => ['label' => '启用', 'class' => 'success'],
                                    'disabled' => ['label' => '禁用', 'class' => 'secondary'],
                                    'expired' => ['label' => '已过期', 'class' => 'dark'],
                                ];
                                $statusInfo = $statusMap[$coupon->status] ?? ['label' => $coupon->status ?? '未知', 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                        </td>
                        <td>
                            <a href="{{ route('soho.coupon.edit', $coupon) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i> 编辑
                            </a>
                            <form method="POST" action="{{ route('soho.coupon.destroy', $coupon) }}" class="d-inline" onsubmit="return confirm('确定删除此优惠券？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i> 删除
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $coupons->links() }}
    </div>
</div>
@endsection