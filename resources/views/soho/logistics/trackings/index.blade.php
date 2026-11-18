@extends('soho::layouts.app')
@section('title','物流轨迹')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1">物流轨迹</h4><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('soho.home') }}">首页</a></li><li class="breadcrumb-item active">物流轨迹</li></ol></nav></div></div>
@php $rows = $trackings ?? collect(); @endphp
<div class="card"><div class="card-body"><table class="table table-hover align-middle mb-0"><thead><tr><th>运单号</th><th>承运商</th><th>状态</th><th>更新时间</th></tr></thead><tbody>
@forelse($rows as $row)
<tr><td>{{ $row->waybill_no ?? '-' }}</td><td>{{ $row->carrier ?? '-' }}</td><td>{{ $row->tracking_status ?? $row->status ?? '-' }}</td><td>{{ $row->updated_at ?? '-' }}</td></tr>
@empty
<tr><td colspan="4" class="text-center text-muted py-4">暂无轨迹数据</td></tr>
@endforelse
</tbody></table></div></div>
@endsection