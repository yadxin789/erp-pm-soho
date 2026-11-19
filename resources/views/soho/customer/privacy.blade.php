@extends('soho::layouts.app')

@section('title', '客户隐私请求')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">请求总数</div><div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">待受理</div><div class="fs-4 fw-semibold text-warning">{{ $stats['pending'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">处理中</div><div class="fs-4 fw-semibold text-primary">{{ $stats['processing'] }}</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
        <div class="text-muted small mb-1">已完成 / 超期</div><div class="fs-4 fw-semibold"><span class="text-success">{{ $stats['completed'] }}</span> / <span class="text-danger">{{ $stats['overdue'] }}</span></div>
    </div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">登记隐私请求</h5></div>
    <div class="card-body">
        <div class="alert alert-light border small text-muted">
            支持查阅（access）、导出（export）、删除（delete）、撤回授权（withdraw）四类数据主体请求；受理后默认 15 日内处理完毕，超期在列表中红色标出。
        </div>
        <form method="POST" action="{{ route('soho.customer.privacy.store') }}">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">客户</label>
                    <select name="customer_id" class="form-select" required>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}{{ $c->email ? ' · '.$c->email : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">请求类型</label>
                    <select name="request_type" class="form-select" required>
                        @foreach(['access' => '查阅数据', 'export' => '导出数据', 'delete' => '删除数据', 'withdraw' => '撤回授权'] as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">诉求说明</label>
                    <input type="text" name="reason" class="form-control" maxlength="1000" placeholder="客户提出的具体诉求">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus me-1"></i> 登记请求</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">隐私请求清单</h5>
        <span class="small text-muted">共 {{ $rows->total() }} 条</span>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach(['pending' => '待受理', 'processing' => '处理中', 'completed' => '已完成', 'rejected' => '已驳回'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="request_type" class="form-select">
                        <option value="">全部类型</option>
                        @foreach(['access' => '查阅', 'export' => '导出', 'delete' => '删除', 'withdraw' => '撤回授权'] as $k => $v)
                            <option value="{{ $k }}" @selected(request('request_type') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="请求编号 / 客户名 / 联系方式" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-search me-1"></i> 筛选</button>
                    <a href="{{ route('soho.customer.privacy') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th class="font-monospace">请求编号</th>
                        <th>客户</th>
                        <th class="text-center">类型</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">受理时限</th>
                        <th>诉求 / 处理备注</th>
                        <th class="text-center">处理人</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $typeMap = ['access' => '查阅', 'export' => '导出', 'delete' => '删除', 'withdraw' => '撤回授权'];
                            $st = ['pending' => ['待受理', 'warning'], 'processing' => ['处理中', 'primary'], 'completed' => ['已完成', 'success'], 'rejected' => ['已驳回', 'danger']];
                            $overdue = in_array($row->status, ['pending', 'processing'], true) && $row->deadline_at && $row->deadline_at < now();
                        @endphp
                        <tr class="{{ $overdue ? 'table-danger' : '' }}">
                            <td class="small text-muted">{{ $row->request_no }}</td>
                            <td>
                                <div>{{ $row->customer_name ?: '客户#'.$row->customer_id }}</div>
                                <div class="small text-muted">{{ $row->customer_contact }}</div>
                            </td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $typeMap[$row->request_type] ?? $row->request_type }}</span></td>
                            <td class="text-center"><span class="badge bg-{{ $st[$row->status][1] ?? 'secondary' }}">{{ $st[$row->status][0] ?? $row->status }}</span></td>
                            <td class="text-center small {{ $overdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                                {{ $row->deadline_at ? substr($row->deadline_at, 0, 10) : '—' }}
                                @if($overdue)<i class="fa fa-triangle-exclamation ms-1"></i>@endif
                            </td>
                            <td class="small">
                                <div class="text-muted">{{ $row->reason ?: '—' }}</div>
                                @if($row->handle_remark)<div class="text-secondary">处理：{{ $row->handle_remark }}</div>@endif
                            </td>
                            <td class="text-center small text-muted">{{ $row->handled_by_name ?: '—' }}</td>
                            <td class="text-center">
                                @if(in_array($row->status, ['pending', 'processing'], true))
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if($row->status === 'pending')
                                            <form method="POST" action="{{ route('soho.customer.privacy.handle', $row->id) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="process">
                                                <input type="hidden" name="handle_remark" value="已受理，开始处理">
                                                <button class="btn btn-sm btn-outline-primary">受理</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('soho.customer.privacy.handle', $row->id) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="complete">
                                            <input type="hidden" name="handle_remark" value="已按请求处理完成">
                                            <button class="btn btn-sm btn-outline-success">完成</button>
                                        </form>
                                        <form method="POST" action="{{ route('soho.customer.privacy.handle', $row->id) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="handle_remark" value="不符合受理条件">
                                            <button class="btn btn-sm btn-outline-danger">驳回</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="small text-muted">{{ $row->handled_at ? substr($row->handled_at, 0, 16) : '—' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">暂无隐私请求。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $rows->links() }}
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">敏感数据访问日志（近 20 条）</h5>
        <form method="GET" class="d-flex gap-2">
            <select name="data_type" class="form-select form-select-sm" style="width:150px">
                <option value="">全部数据类型</option>
                @foreach($dataTypes as $dt)
                    <option value="{{ $dt }}" @selected(request('data_type') === $dt)>{{ $dt }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">筛选</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center">数据类型</th>
                        <th class="text-center">动作</th>
                        <th class="font-monospace">记录标识</th>
                        <th class="text-center">操作人</th>
                        <th class="font-monospace">IP</th>
                        <th class="text-center">访问时间</th>
                        <th>备注</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accessLogs as $log)
                        <tr>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $log->data_type }}</span></td>
                            <td class="text-center small">{{ $log->action }}</td>
                            <td class="small text-muted">{{ $log->record_identifier }}</td>
                            <td class="text-center">{{ $log->user_id }}</td>
                            <td class="small text-muted">{{ $log->ip_address }}</td>
                            <td class="text-center small text-muted">{{ $log->accessed_at ? substr($log->accessed_at, 0, 16) : '—' }}</td>
                            <td class="small text-muted">{{ $log->remark ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">暂无敏感数据访问记录。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
