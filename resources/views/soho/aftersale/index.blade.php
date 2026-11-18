@extends('soho::layouts.app')
@section('title', '售后管理')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">售后列表</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>售后单号</th><th>订单号</th><th>类型</th><th>状态</th><th>申请时间</th><th>操作</th></tr></thead>
                <tbody>
                    @forelse($aftersales as $item)
                    <tr>
                        <td>{{ $item->after_sale_no }}</td>
                        <td>{{ $item->platformOrder->platform_order_no ?? '-' }}</td>
                        <td>{{ $item->type }}</td>
                        <td><span class="badge bg-{{ $item->status == 'pending' ? 'warning' : ($item->status == 'approved' ? 'success' : 'danger') }}">{{ $item->status }}</span></td>
                        <td>{{ $item->created_at }}</td>
                        <td><a href="{{ route('soho.aftersale.show', $item) }}" class="btn btn-sm btn-outline-primary">详情</a></td>
                    </tr>
                    @empty<tr><td colspan="6" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $aftersales->links() }}
    </div>
</div>
@endsection