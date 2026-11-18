@extends('soho::layouts.app')

@section('title', '促销活动管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">促销活动列表</h5>
        <a href="{{ route('promotions.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 创建促销活动
        </a>
    </div>
    <div class="card-body">
        <!-- 搜索筛选表单 -->
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索活动名称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">全部类型</option>
                        <option value="full_reduction" @selected(request('type') == 'full_reduction')>满减促销</option>
                        <option value="discount" @selected(request('type') == 'discount')>折扣促销</option>
                        <option value="buy_gift" @selected(request('type') == 'buy_gift')>买赠促销</option>
                        <option value="flash_sale" @selected(request('type') == 'flash_sale')>限时秒杀</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="draft" @selected(request('status') == 'draft')>草稿</option>
                        <option value="active" @selected(request('status') == 'active')>进行中</option>
                        <option value="ended" @selected(request('status') == 'ended')>已结束</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>活动名称</th>
                        <th>类型</th>
                        <th>开始时间</th>
                        <th>结束时间</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $promotion)
                    <tr>
                        <td>{{ $promotion->id }}</td>
                        <td>{{ $promotion->name }}</td>
                        <td>
                            @php
                                $typeMap = [
                                    'full_reduction' => ['label' => '满减促销', 'class' => 'primary'],
                                    'discount' => ['label' => '折扣促销', 'class' => 'info'],
                                    'buy_gift' => ['label' => '买赠促销', 'class' => 'success'],
                                    'flash_sale' => ['label' => '限时秒杀', 'class' => 'danger'],
                                ];
                                $typeInfo = $typeMap[$promotion->type] ?? ['label' => $promotion->type ?? '未知', 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $typeInfo['class'] }}">{{ $typeInfo['label'] }}</span>
                        </td>
                        <td>{{ $promotion->start_time ? $promotion->start_time->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ $promotion->end_time ? $promotion->end_time->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            @php
                                $statusMap = [
                                    'draft' => ['label' => '草稿', 'class' => 'secondary'],
                                    'active' => ['label' => '进行中', 'class' => 'success'],
                                    'ended' => ['label' => '已结束', 'class' => 'dark'],
                                ];
                                $statusInfo = $statusMap[$promotion->status] ?? ['label' => $promotion->status ?? '未知', 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                        </td>
                        <td>
                            <a href="{{ route('promotions.edit', $promotion) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i> 编辑
                            </a>
                            <form method="POST" action="{{ route('promotions.destroy', $promotion) }}" class="d-inline" onsubmit="return confirm('确定删除此促销活动？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i> 删除
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $promotions->links() }}
    </div>
</div>
@endsection