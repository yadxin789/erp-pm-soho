@extends('soho::layouts.app')
@section('title','履约异常')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1">履约异常</h4><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li><li class="breadcrumb-item active">履约异常</li></ol></nav></div></div>
@php $rows = $exceptions ?? collect(); @endphp
<div class="card"><div class="card-body"><table class="table table-hover align-middle mb-0"><thead><tr><th>订单号</th><th>仓库</th><th>状态</th><th>说明</th><th>时间</th></tr></thead><tbody>
@forelse($rows as $row)
<tr><td>{{ $row->order_no ?? $row->id }}</td><td>{{ $row->warehouse_name ?? '-' }}</td><td>{{ $row->status ?? '-' }}</td><td>{{ $row->remark ?? '-' }}</td><td>{{ $row->created_at ?? '-' }}</td></tr>
@empty
<tr><td colspan="5" class="text-center text-muted py-4">暂无履约异常记录</td></tr>
@endforelse
</tbody></table></div></div>
@endsection