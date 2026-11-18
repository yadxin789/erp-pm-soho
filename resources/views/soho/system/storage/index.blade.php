@extends('soho::layouts.app')

@section('title', '文件存储配置')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-database me-2"></i>文件存储配置</h4>
        <p class="text-muted mb-0 small">管理系统文件存储磁盘（本地、阿里云OSS、腾讯云COS、七牛云等），敏感字段自动加密</p>
    </div>
    <a href="{{ route('soho.system.storage.create') }}" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>新增存储
    </a>
</div>

{{-- 筛选区 --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('soho.system.storage.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">关键词</label>
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="磁盘标识 / 名称" value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">驱动类型</label>
                <select class="form-select form-select-sm" name="driver">
                    <option value="">全部驱动</option>
                    @foreach($drivers as $value => $label)
                        <option value="{{ $value }}" {{ request('driver') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-primary flex-grow-1">
                    <i class="fa fa-search me-1"></i>筛选
                </button>
                <a href="{{ route('soho.system.storage.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- 列表 --}}
<div class="card">
    <div class="card-body p-0">
        @if($configs->isEmpty())
            <div class="text-center py-5">
                <i class="fa fa-database fa-3x text-muted mb-3"></i>
                <p class="text-muted">暂无文件存储配置</p>
            </div>
        @else
            @php
                $driverIcons = \App\Soho\Models\FileStorageConfig::getDriverIcons();
                $driverColors = \App\Soho\Models\FileStorageConfig::getDriverColors();
            @endphp
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>配置名称</th>
                            <th>磁盘标识</th>
                            <th>驱动类型</th>
                            <th>访问域名</th>
                            <th>状态</th>
                            <th>更新时间</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($configs as $config)
                            <tr>
                                <td class="text-muted">{{ $config->id }}</td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $config->name }}
                                        @if($config->is_default)
                                            <span class="badge bg-primary ms-1">默认</span>
                                        @endif
                                    </div>
                                </td>
                                <td><code class="small">{{ $config->disk_name }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $driverColors[$config->driver] ?? 'secondary' }}">
                                        <i class="fa {{ $driverIcons[$config->driver] ?? 'fa-hdd' }} me-1"></i>
                                        {{ $drivers[$config->driver] ?? $config->driver }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    @if($config->domain)
                                        <a href="{{ $config->domain }}" target="_blank" class="text-decoration-none">
                                            {{ $config->domain }} <i class="fa fa-external-link fa-xs"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($config->is_active)
                                        <span class="badge bg-success">启用</span>
                                    @else
                                        <span class="badge bg-secondary">禁用</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $config->updated_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info" title="测试连接"
                                                onclick="testConnection({{ $config->id }})">
                                            <i class="fa fa-plug"></i>
                                        </button>
                                        <a href="{{ route('soho.system.storage.edit', $config) }}" class="btn btn-outline-primary" title="编辑">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('soho.system.storage.destroy', $config) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="删除"
                                                    onclick="return confirm('确定删除存储「{{ $config->name }}」？')">
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
                <div class="small text-muted">共 {{ $configs->count() }} 条记录</div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
// 测试存储连接
function testConnection(id) {
    // 调用测试连接接口
    fetch('{{ route("soho.system.storage.index") }}/' + id + '/test', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message + '\n驱动: ' + data.driver + '\nBucket: ' + data.bucket + '\nRegion: ' + data.region);
        } else {
            alert('❌ 连接失败: ' + (data.message || '未知错误'));
        }
    })
    .catch(() => alert('❌ 请求失败，请重试'));
}
</script>
@endsection
