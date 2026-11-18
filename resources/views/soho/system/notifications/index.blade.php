@extends('soho::layouts.app')

@section('title', '通知模板管理')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-bell me-2"></i>通知模板管理</h4>
        <p class="text-muted mb-0 small">管理系统各业务场景的通知消息模板，支持变量占位符</p>
    </div>
    <a href="{{ route('soho.system.notifications.create') }}" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>新增模板
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('soho.system.notifications.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">关键词</label>
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="模板编码 / 名称" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">渠道</label>
                <select class="form-select form-select-sm" name="channel">
                    <option value="">全部渠道</option>
                    @foreach($channels as $value => $label)
                        <option value="{{ $value }}" {{ request('channel') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">业务类型</label>
                <select class="form-select form-select-sm" name="business_type">
                    <option value="">全部类型</option>
                    @foreach($businessTypes as $value => $label)
                        <option value="{{ $value }}" {{ request('business_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-primary flex-grow-1">
                    <i class="fa fa-search me-1"></i>筛选
                </button>
                <a href="{{ route('soho.system.notifications.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($templates->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">暂无通知模板</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>模板名称</th>
                            <th>编码</th>
                            <th>渠道</th>
                            <th>业务类型</th>
                            <th>状态</th>
                            <th>更新时间</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $channelColors = \App\Soho\Models\NotificationTemplate::getChannelColors();
                            $channelIcons = \App\Soho\Models\NotificationTemplate::getChannelIcons();
                        @endphp
                        @foreach($templates as $template)
                            <tr>
                                <td class="text-muted">{{ $template->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $template->template_name }}</div>
                                    @if($template->subject)
                                        <div class="small text-muted">标题：{{ $template->subject }}</div>
                                    @endif
                                </td>
                                <td><code class="small">{{ $template->template_code }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $channelColors[$template->channel] ?? 'secondary' }}">
                                        <i class="fa {{ $channelIcons[$template->channel] ?? 'fa-bell' }} me-1"></i>
                                        {{ $channels[$template->channel] ?? $template->channel }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $businessTypes[$template->business_type] ?? $template->business_type }}</span>
                                </td>
                                <td>
                                    @if($template->is_active)
                                        <span class="badge bg-success">启用</span>
                                    @else
                                        <span class="badge bg-secondary">禁用</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $template->updated_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('soho.system.notifications.edit', $template) }}" class="btn btn-outline-primary" title="编辑">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('soho.system.notifications.destroy', $template) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="删除"
                                                    onclick="return confirm('确定删除模板「{{ $template->template_name }}」？')">
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
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">共 {{ $templates->total() }} 条</div>
                    {{ $templates->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
