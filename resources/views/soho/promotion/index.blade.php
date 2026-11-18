@extends('soho::layouts.app')

@section('title', '促销活动')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">促销活动</li>
    </ol>
</nav>

<!-- 统计卡片 -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <small class="text-muted">全部活动</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-success">
            <div class="card-body">
                <h3 class="mb-0 text-success">{{ $stats['active'] }}</h3>
                <small class="text-muted">进行中</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-warning">
            <div class="card-body">
                <h3 class="mb-0 text-warning">{{ $stats['draft'] }}</h3>
                <small class="text-muted">草稿</small>
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
        <h5 class="mb-0">促销活动列表</h5>
        <a href="{{ route('soho.promotion.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 创建活动
        </a>
    </div>
    <div class="card-body">
        <!-- 筛选表单 -->
        <form method="GET" action="{{ route('soho.promotion.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索活动名称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="fixed_discount" @selected(request('type') == 'fixed_discount')>固定金额</option>
                        <option value="percentage" @selected(request('type') == 'percentage')>百分比折扣</option>
                        <option value="buy_get" @selected(request('type') == 'buy_get')>买赠</option>
                        <option value="flash_sale" @selected(request('type') == 'flash_sale')>限时特惠</option>
                        <option value="bundle" @selected(request('type') == 'bundle')>组合优惠</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="draft" @selected(request('status') == 'draft')>草稿</option>
                        <option value="active" @selected(request('status') == 'active')>启用</option>
                        <option value="inactive" @selected(request('status') == 'inactive')>停用</option>
                        <option value="expired" @selected(request('status') == 'expired')>已过期</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.promotion.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>活动名称</th>
                        <th>类型</th>
                        <th>优惠详情</th>
                        <th>有效期</th>
                        <th>状态</th>
                        <th>叠加</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $promotion)
                    <tr>
                        <td>{{ $promotion->id }}</td>
                        <td>{{ $promotion->name }}</td>
                        <td><span class="badge bg-info">{{ $promotion->type_label }}</span></td>
                        <td>{{ $promotion->description }}</td>
                        <td>
                            <small>
                                {{ $promotion->start_at?->format('Y-m-d') ?? '-' }} ~ {{ $promotion->end_at?->format('Y-m-d') ?? '-' }}
                                @if($promotion->is_active)
                                    <span class="badge bg-success ms-1">进行中</span>
                                @elseif($promotion->status === 'draft')
                                    <span class="badge bg-warning ms-1">未开始</span>
                                @elseif($promotion->status === 'expired')
                                    <span class="badge bg-danger ms-1">已过期</span>
                                @endif
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $promotion->status_color }}">{{ $promotion->status_label }}</span>
                        </td>
                        <td>
                            {{ $promotion->stackable ? '<span class="badge bg-success">可叠加</span>' : '<span class="text-muted">不可叠加</span>' }}
                        </td>
                        <td>
                            <a href="{{ route('soho.promotion.edit', $promotion) }}" class="btn btn-sm btn-outline-primary" title="编辑">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('soho.promotion.toggle', $promotion) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="切换状态">
                                    <i class="fa fa-exchange"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('soho.promotion.destroy', $promotion) }}" class="d-inline" onsubmit="return confirm('确定删除此促销活动？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="删除">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">暂无促销活动</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $promotions->links() }}
    </div>
</div>
@endsection