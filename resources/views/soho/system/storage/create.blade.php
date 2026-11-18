@extends('soho::layouts.app')

@section('title', '新增文件存储')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fa fa-database me-2"></i>新增文件存储配置</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('soho.system.storage.store') }}">
                    @csrf

                    {{-- 基础信息 --}}
                    <h6 class="mb-3 text-primary"><i class="fa fa-info-circle me-2"></i>基础信息</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">配置名称 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="如：阿里云OSS主存储">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="disk_name" class="form-label">磁盘标识 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('disk_name') is-invalid @enderror"
                                       id="disk_name" name="disk_name" value="{{ old('disk_name') }}" required
                                       pattern="[a-z_]+" placeholder="小写字母与下划线，如 oss_main">
                                <div class="form-text">仅支持小写字母和下划线</div>
                                @error('disk_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="driver" class="form-label">驱动类型 <span class="text-danger">*</span></label>
                                <select class="form-select @error('driver') is-invalid @enderror" id="driver" name="driver" required>
                                    <option value="">请选择驱动</option>
                                    @foreach($drivers as $value => $label)
                                        <option value="{{ $value }}" {{ old('driver') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('driver')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="domain" class="form-label">访问域名</label>
                                <input type="url" class="form-control @error('domain') is-invalid @enderror"
                                       id="domain" name="domain" value="{{ old('domain') }}"
                                       placeholder="https://cdn.example.com">
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
                                           value="{{ old('root') }}" placeholder="如：/data/storage">
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
                                           value="{{ old('bucket') }}" placeholder="存储空间名称">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="region" class="form-label">Region 区域</label>
                                    <input type="text" class="form-control" id="region" name="region"
                                           value="{{ old('region') }}" placeholder="如：oss-cn-hangzhou">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="endpoint" class="form-label">Endpoint 端点</label>
                                    <input type="text" class="form-control" id="endpoint" name="endpoint"
                                           value="{{ old('endpoint') }}" placeholder="如：oss-cn-hangzhou.aliyuncs.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="access_key" class="form-label">AccessKey</label>
                                    <input type="text" class="form-control" id="access_key" name="access_key"
                                           value="{{ old('access_key') }}" placeholder="访问密钥（加密存储）">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="secret_key" class="form-label">SecretKey</label>
                                    <input type="password" class="form-control" id="secret_key" name="secret_key"
                                           value="{{ old('secret_key') }}" placeholder="私密密钥（加密存储）">
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
                                       {{ old('is_default') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">设为默认磁盘</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
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

    // 隐藏所有配置区域
    localConfig.style.display = 'none';
    cloudConfig.style.display = 'none';

    // 根据驱动类型显示对应区域
    if (driver === 'local') {
        localConfig.style.display = 'block';
    } else if (driver && driver !== 'local') {
        cloudConfig.style.display = 'block';
    }
}

document.getElementById('driver').addEventListener('change', toggleDriverConfig);
// 页面加载时根据旧值显示
toggleDriverConfig();
</script>
@endsection
