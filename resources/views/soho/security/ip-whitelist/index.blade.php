@extends('soho::layouts.app')

@section('title', 'IP 白名单')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-shield-alt me-2"></i>IP 白名单管理</h4>
        <p class="text-muted mb-0 small">管理允许或禁止访问的 IP 地址和网段</p>
    </div>
    <a href="{{ route('soho.security.ip-whitelist.create') }}" class="btn btn-primary btn-sm">
        <i class="fa fa-plus me-1"></i>新增规则
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($items->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-shield-alt fa-3x text-muted mb-3"></i>
                <p class="text-muted">暂无 IP 规则</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>IP地址/网段</th>
                            <th>类型</th>
                            <th>适用范围</th>
                            <th>描述</th>
                            <th>状态</th>
                            <th>创建人</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td class="text-muted">{{ $item->id }}</td>
                                <td><code>{{ $item->ip_address }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $typeColors[$item->type] ?? 'secondary' }}">
                                        {{ $typeLabels[$item->type] ?? $item->type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $scopeLabels[$item->scope] ?? $item->scope }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $item->description ?? '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('soho.security.ip-whitelist.toggle', $item) }}" class="d-inline">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                            @if($item->is_active)
                                                <span class="badge bg-success">启用</span>
                                            @else
                                                <span class="badge bg-secondary">禁用</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="small">
                                    {{ $item->creator?->name ?? '-' }}
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('soho.security.ip-whitelist.edit', $item) }}" class="btn btn-outline-primary" title="编辑">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('soho.security.ip-whitelist.destroy', $item) }}" class="d-inline"
                                              onsubmit="return confirm('确定删除该规则？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="删除">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="small text-muted">共 {{ $items->count() }} 条规则</div>
            </div>
        @endif
    </div>
</div>
@endsection