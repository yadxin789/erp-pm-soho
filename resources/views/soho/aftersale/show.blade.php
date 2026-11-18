@extends('soho::layouts.app')
@section('title', '售后详情')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">售后详情</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th width="120">售后单号</th><td>{{ $aftersale->after_sale_no }}</td></tr>
                    <tr><th>订单号</th><td>{{ $aftersale->platformOrder->platform_order_no ?? '-' }}</td></tr>
                    <tr><th>售后类型</th><td>{{ $aftersale->type }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th width="120">状态</th><td><span class="badge bg-{{ $aftersale->status == 'pending' ? 'warning' : 'success' }}">{{ $aftersale->status }}</span></td></tr>
                    <tr><th>申请时间</th><td>{{ $aftersale->created_at }}</td></tr>
                    <tr><th>处理时间</th><td>{{ $aftersale->handle_time ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
        @if($aftersale->status == 'pending')
        <form method="POST" action="{{ route('soho.aftersale.handle', $aftersale) }}" class="mt-3">@csrf
            <div class="mb-3"><label class="form-label">处理操作</label><select name="action" class="form-select" required><option value="">选择操作</option><option value="approve">批准</option><option value="reject">拒绝</option></select></div>
            <div class="mb-3"><label class="form-label">备注</label><textarea name="remark" class="form-control" rows="2"></textarea></div>
            <button type="submit" class="btn btn-primary">提交处理</button>
        </form>
        @endif
        <a href="{{ route('soho.aftersale.index') }}" class="btn btn-secondary mt-2">返回</a>
    </div>
</div>
@endsection