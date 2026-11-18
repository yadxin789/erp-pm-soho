@extends('soho::layouts.app')

@section('title', '功能开关管理')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-toggle-on me-2"></i>功能开关管理</h4>
        <p class="text-muted mb-0 small">控制系统各功能模块的启用/禁用状态，支持灰度发布和紧急关闭</p>
    </div>
    <a href="{{ route('soho.system.features.create') }}" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>新增功能开关
    </a>
</div>

{{-- 筛选搜索栏 --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('soho.system.features.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">关键词搜索</label>
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="功能标识 / 功能名称"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">模块</label>
                <select class="form-select form-select-sm" name="module">
                    <option value="">全部模块</option>
                    @foreach($modules as $value => $label)
                        <option value="{{ $value }}" {{ request('module') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">状态</label>
                <select class="form-select form-select-sm" name="is_enabled">
                    <option value="">全部状态</option>
                    <option value="1" {{ request('is_enabled') === '1' ? 'selected' : '' }}>已启用</option>
                    <option value="0" {{ request('is_enabled') === '0' ? 'selected' : '' }}>已禁用</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-primary flex-grow-1">
                    <i class="fa fa-search me-1"></i>筛选
                </button>
                <a href="{{ route('soho.system.features.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 功能开关列表 --}}
<div class="card">
    <div class="card-body p-0">
        @if($features->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">暂无功能开关，请点击「新增功能开关」添加</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>功能名称</th>
                            <th>功能标识</th>
                            <th>模块</th>
                            <th>状态</th>
                            <th>启用人</th>
                            <th>启用时间</th>
                            <th>更新时间</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($features as $feature)
                            @php
                                $moduleColors = \App\Soho\Models\FeatureSwitch::getModuleColors();
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $feature->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $feature->feature_name }}</div>
                                    @if($feature->description)
                                        <div class="small text-muted">{{ $feature->description }}</div>
                                    @endif
                                </td>
                                <td><code class="small">{{ $feature->feature_key }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $moduleColors[$feature->module] ?? 'secondary' }}">
                                        {{ $modules[$feature->module] ?? $feature->module }}
                                    </span>
                                </td>
                                <td>
                                    {{-- 大开关按钮 --}}
                                    <form method="POST" action="{{ route('soho.system.features.toggle', $feature) }}" class="d-inline">
                                        @csrf
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   style="cursor: pointer; width: 3em; height: 1.5em;"
                                                   onchange="this.form.submit()"
                                                   {{ $feature->is_enabled ? 'checked' : '' }}>
                                        </div>
                                    </form>
                                </td>
                                <td class="small">
                                    @if($feature->enabler)
                                        {{ $feature->enabler->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    @if($feature->enabled_at)
                                        {{ $feature->enabled_at->format('Y-m-d H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $feature->updated_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('soho.system.features.edit', $feature) }}" class="btn btn-outline-primary" title="编辑">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('soho.system.features.destroy', $feature) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="删除"
                                                    onclick="return confirm('确定删除功能开关「{{ $feature->feature_name }}」？')">
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
                    <div class="small text-muted">
                        共 {{ $features->total() }} 条记录，当前第 {{ $features->currentPage() }} / {{ $features->lastPage() }} 页
                    </div>
                    {{ $features->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
