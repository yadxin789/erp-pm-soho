@extends('soho::layouts.app')

@section('title', '编辑文件存储')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-database me-2"></i>编辑文件存储配置</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('soho.system.storage.update', $storage) }}">
                    @csrf
                    @method('PUT')

                    {{-- 基础信息 --}}
                    <h6 class="mb-3 text-primary"><i class="fa fa-info-circle me-2"></i>基础信息</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">配置名称 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $storage->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">磁盘标识</label>
                                <input type="text" class="form-control" value="{{ $storage->disk_name }}" disabled>
                                <div class="form-text">磁盘标识创建后不可修改</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="driver" class="form-label">驱动类型 <span class="text-danger">*</span></label>
                                <select class="form-select @error('driver') is-invalid @enderror" id="driver" name="driver" required>
                                    @foreach($drivers as $value => $label)
                                        <option value="{{ $value }}" {{ old('driver', $storage->driver) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('driver')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="domain" class="form-label">访问域名</label>
                                <input type="url" class="form-control @error('domain') is-invalid @enderror"
                                       id="domain" name="domain" value="{{ old('domain', $storage->domain) }}">
                                @error('domain')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- 本地存储配置 --}}
                    <div id="local-config" class="driver-config" style="display:none;">
                        <h6 class="mb-3 text-primary"><i class="fa fa-hdd me-2"></i>本地存储配置</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="root" class="form-label">存储根目录</label>
                                    <input type="text" class="form-control" id="root" name="root"
                                           value="{{ old('root', $config['root'] ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 云存储配置 --}}
                    <div id="cloud-config" class="driver-config" style="display:none;">
                        <h6 class="mb-3 text-primary"><i class="fa fa-cloud me-2"></i>云存储配置</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bucket" class="form-label">Bucket 名称</label>
                                    <input type="text" class="form-control" id="bucket" name="bucket"
                                           value="{{ old('bucket', $config['bucket'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="region" class="form-label">Region 区域</label>
                                    <input type="text" class="form-control" id="region" name="region"
                                           value="{{ old('region', $config['region'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="endpoint" class="form-label">Endpoint 端点</label>
                                    <input type="text" class="form-control" id="endpoint" name="endpoint"
                                           value="{{ old('endpoint', $config['endpoint'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="access_key" class="form-label">AccessKey</label>
                                    <input type="text" class="form-control" id="access_key" name="access_key"
                                           value="{{ old('access_key', $config['access_key'] ?? '') }}"
                                           placeholder="留空则保持原值">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="secret_key" class="form-label">SecretKey</label>
                                    <input type="password" class="form-control" id="secret_key" name="secret_key"
                                           value="{{ old('secret_key', $config['secret_key'] ?? '') }}"
                                           placeholder="留空则保持原值">
                                    <div class="form-text"><i class="fa fa-lock me-1"></i>敏感字段将自动加密存储</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 状态设置 --}}
                    <h6 class="mb-3 text-primary"><i class="fa fa-toggle-on me-2"></i>状态设置</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1"
                                       {{ old('is_default', $storage->is_default) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">设为默认磁盘</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $storage->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">启用</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('soho.system.storage.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-1"></i>取消
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i>保存配置
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// 根据驱动类型显示对应配置区域
function toggleDriverConfig() {
    const driver = document.getElementById('driver').value;
    const localConfig = document.getElementById('local-config');
    const cloudConfig = document.getElementById('cloud-config');

    localConfig.style.display = 'none';
    cloudConfig.style.display = 'none';

    if (driver === 'local') {
        localConfig.style.display = 'block';
    } else if (driver && driver !== 'local') {
        cloudConfig.style.display = 'block';
    }
}

document.getElementById('driver').addEventListener('change', toggleDriverConfig);
toggleDriverConfig();
</script>
@endsection
