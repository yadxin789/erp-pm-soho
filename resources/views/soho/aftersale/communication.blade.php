@extends('soho::layouts.app')

@section('title', '售后沟通记录')

@section('content')
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">登记沟通记录</h5>
        <a href="{{ route('soho.aftersale.apply') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> 售后申请
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.aftersale.communication.store') }}">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">售后单</label>
                    <select name="after_sale_id" class="form-select" required>
                        @foreach($rows as $row)
                            <option value="{{ $row->id }}">{{ $row->after_sale_no }} · {{ $row->buyer_nick }} · {{ $row->reason }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">沟通渠道</label>
                    <select name="channel" class="form-select">
                        <option value="站内信">站内信</option>
                        <option value="电话">电话</option>
                        <option value="平台留言">平台留言</option>
                        <option value="微信">微信</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">沟通内容</label>
                    <input type="text" name="content" class="form-control" placeholder="记录与买家的沟通要点" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa fa-comment me-1"></i> 保存</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">最近沟通记录</h5></div>
    <div class="card-body">
        @forelse($logs as $log)
            <div class="border-start border-3 border-primary ps-3 mb-3">
                <div class="d-flex justify-content-between">
                    <div class="small text-muted">#{{ $log->model_id }} · {{ $log->created_at }}</div>
                    <div class="small text-muted">{{ $log->ip_address }}</div>
                </div>
                <div>{{ $log->description }}</div>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <i class="fa fa-comments fs-3 d-block mb-2"></i>
                还没有沟通记录，可在上方登记一条。
            </div>
        @endforelse
    </div>
</div>
@endsection
