@extends('soho::layouts.app')

@section('title', '系统参数管理')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-sliders me-2"></i>系统参数管理</h4>
        <p class="text-muted mb-0 small">管理外部平台连接所需的 API Key、密钥、回调地址等配置参数</p>
    </div>
    <a href="{{ route('soho.system.parameters.create') }}" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>新增参数
    </a>
</div>

{{-- 筛选搜索栏 --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('soho.system.parameters.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">关键词搜索</label>
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="参数键 / 参数名称"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">分类</label>
                <select class="form-select form-select-sm" name="category">
                    <option value="">全部分类</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">状态</label>
                <select class="form-select form-select-sm" name="is_active">
                    <option value="">全部状态</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>启用</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>禁用</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-primary flex-grow-1">
                    <i class="fa fa-search me-1"></i>筛选
                </button>
                <a href="{{ route('soho.system.parameters.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 参数列表 --}}
<div class="card">
    <div class="card-body p-0">
        @if($parameters->isEmpty())
            {{-- 空状态 --}}
            <div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">暂无系统参数，请点击「新增参数」添加</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>参数名称</th>
                            <th>参数键</th>
                            <th>参数值</th>
                            <th>分类</th>
                            <th>类型</th>
                            <th>加密</th>
                            <th>状态</th>
                            <th>更新时间</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parameters as $parameter)
                            <tr>
                                <td class="text-muted">{{ $parameter->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $parameter->param_name }}</div>
                                    @if($parameter->description)
                                        <div class="small text-muted">{{ $parameter->description }}</div>
                                    @endif
                                </td>
                                <td><code class="small">{{ $parameter->param_key }}</code></td>
                                <td>
                                    {{-- 加密参数脱敏显示 --}}
                                    @if($parameter->is_encrypted)
                                        <span class="text-muted" title="加密存储">{{ $parameter->masked_value }}</span>
                                    @else
                                        <span class="text-break" style="max-width: 300px; display: inline-block;">{{ Str::limit($parameter->param_value, 50) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $categoryLabels = \App\Soho\Models\SystemParameter::getCategoryLabels();
                                        $categoryColors = ['platform_api' => 'primary', 'callback_url' => 'info', 'secret_key' => 'danger', 'other' => 'secondary'];
                                    @endphp
                                    <span class="badge bg-{{ $categoryColors[$parameter->category] ?? 'secondary' }}">
                                        {{ $categoryLabels[$parameter->category] ?? $parameter->category }}
                                    </span>
                                </td>
                                <td><span class="small text-muted">{{ $parameter->value_type }}</span></td>
                                <td>
                                    @if($parameter->is_encrypted)
                                        <i class="fa fa-lock text-warning" title="已加密"></i>
                                    @else
                                        <i class="fa fa-unlock text-muted" title="未加密"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($parameter->is_active)
                                        <span class="badge bg-success">启用</span>
                                    @else
                                        <span class="badge bg-secondary">禁用</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $parameter->updated_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        {{-- 快速切换启用/禁用 --}}
                                        <form method="POST" action="{{ route('soho.system.parameters.toggle', $parameter) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-{{ $parameter->is_active ? 'warning' : 'success' }}"
                                                    title="{{ $parameter->is_active ? '禁用' : '启用' }}"
                                                    onclick="return confirm('确定{{ $parameter->is_active ? '禁用' : '启用' }}此参数？')">
                                                <i class="fa fa-{{ $parameter->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('soho.system.parameters.edit', $parameter) }}" class="btn btn-outline-primary" title="编辑">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('soho.system.parameters.destroy', $parameter) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="删除"
                                                    onclick="return confirm('确定删除参数「{{ $parameter->param_name }}」？此操作不可恢复！')">
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

            {{-- 分页 --}}
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        共 {{ $parameters->total() }} 条记录，当前第 {{ $parameters->currentPage() }} / {{ $parameters->lastPage() }} 页
                    </div>
                    {{ $parameters->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
