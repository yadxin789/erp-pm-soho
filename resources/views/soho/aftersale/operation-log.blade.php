@extends('soho::layouts.app')

@section('title', '售后操作记录')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">售后操作记录</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('soho.aftersale.communication') }}" class="btn btn-outline-secondary btn-sm">沟通记录</a>
            <a href="{{ route('soho.aftersale.apply') }}" class="btn btn-outline-secondary btn-sm">售后申请</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <select name="action" class="form-select">
                        <option value="">全部操作</option>
                        @foreach($actions as $action => $count)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}（{{ $count }}）</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-filter me-1"></i> 筛选</button>
                    <a href="{{ route('soho.aftersale.operation.log') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>时间</th>
                        <th>操作</th>
                        <th>售后单 ID</th>
                        <th>说明</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="small text-muted">{{ $log->created_at }}</td>
                            <td class="font-monospace small">{{ $log->action }}</td>
                            <td class="text-muted">#{{ $log->model_id }}</td>
                            <td class="small">{{ $log->description }}</td>
                            <td class="small text-muted">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">还没有售后操作记录。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
</div>
@endsection
