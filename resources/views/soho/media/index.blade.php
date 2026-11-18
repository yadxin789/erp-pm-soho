@extends('soho::layouts.app')

@section('title', '媒体库')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">媒体库</li>
    </ol>
</nav>

<div class="row">
    <!-- 左侧分类栏 -->
    <div class="col-md-2">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fa fa-folder me-1"></i>媒体分类</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item {{ !request('type') ? 'active' : '' }}">
                        <a href="{{ route('soho.media.index') }}" class="text-decoration-none text-dark">
                            <i class="fa fa-th-large me-2"></i>全部文件
                            <span class="badge bg-secondary float-end">{{ $typeStats['total'] }}</span>
                        </a>
                    </li>
                    <li class="list-group-item {{ request('type') == 'image' ? 'active' : '' }}">
                        <a href="{{ route('soho.media.index', ['type' => 'image']) }}" class="text-decoration-none text-dark">
                            <i class="fa fa-picture-o me-2"></i>图片
                            <span class="badge bg-info float-end">{{ $typeStats['image'] }}</span>
                        </a>
                    </li>
                    <li class="list-group-item {{ request('type') == 'video' ? 'active' : '' }}">
                        <a href="{{ route('soho.media.index', ['type' => 'video']) }}" class="text-decoration-none text-dark">
                            <i class="fa fa-video-camera me-2"></i>视频
                            <span class="badge bg-danger float-end">{{ $typeStats['video'] }}</span>
                        </a>
                    </li>
                    <li class="list-group-item {{ request('type') == 'document' ? 'active' : '' }}">
                        <a href="{{ route('soho.media.index', ['type' => 'document']) }}" class="text-decoration-none text-dark">
                            <i class="fa fa-file-text-o me-2"></i>文档
                            <span class="badge bg-warning float-end">{{ $typeStats['document'] }}</span>
                        </a>
                    </li>
                    <li class="list-group-item {{ request('type') == 'audio' ? 'active' : '' }}">
                        <a href="{{ route('soho.media.index', ['type' => 'audio']) }}" class="text-decoration-none text-dark">
                            <i class="fa fa-music me-2"></i>音频
                            <span class="badge bg-success float-end">{{ $typeStats['audio'] }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- 容量统计 -->
        <div class="card mt-3">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">存储空间</h6>
                <div class="progress mb-2">
                    <div class="progress-bar {{ $storage['percent'] > 90 ? 'bg-danger' : ($storage['percent'] > 70 ? 'bg-warning' : 'bg-success') }}" 
                         style="width: {{ $storage['percent'] }}%"></div>
                </div>
                <small class="text-muted">
                    {{ $storage['used'] }} / {{ $storage['total'] }} GB
                </small>
            </div>
        </div>
    </div>

    <!-- 右侧内容区 -->
    <div class="col-md-10">
        <!-- 操作工具栏 -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex gap-2">
                        <!-- 上传按钮 -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fa fa-upload"></i> 上传文件
                        </button>
                        <!-- 批量删除 -->
                        <button type="button" class="btn btn-outline-danger" id="batchDeleteBtn" disabled onclick="batchDelete()">
                            <i class="fa fa-trash"></i> 批量删除
                        </button>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <!-- 搜索 -->
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="搜索文件名..." value="{{ request('search') }}" onkeyup="handleSearch(event)">
                    </div>
                </div>
            </div>
        </div>

        <!-- 网格视图 -->
        <div class="card">
            <div class="card-body">
                <div class="row" id="mediaGrid">
                    @forelse($mediaItems as $media)
                    <div class="col-md-3 col-sm-4 col-6 mb-4 media-item" data-id="{{ $media->id }}">
                        <div class="card h-100 media-card">
                            <div class="position-relative">
                                <!-- 缩略图/图标 -->
                                <div class="media-thumb">
                                    @if($media->file_type == 'image')
                                        <img src="{{ $media->thumb_url ?? $media->url }}" alt="{{ $media->file_name }}" class="img-fluid" loading="lazy">
                                    @elseif($media->file_type == 'video')
                                        <div class="d-flex align-items-center justify-content-center bg-dark text-white" style="height: 150px;">
                                            <i class="fa fa-play-circle fa-3x"></i>
                                        </div>
                                    @elseif($media->file_type == 'document')
                                        <div class="d-flex align-items-center justify-content-center bg-light text-primary" style="height: 150px;">
                                            <i class="fa fa-file-text fa-3x"></i>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 150px;">
                                            <i class="fa fa-file fa-3x"></i>
                                        </div>
                                    @endif
                                </div>
                                <!-- 类型标签 -->
                                <span class="badge bg-{{ $media->file_type == 'image' ? 'info' : ($media->file_type == 'video' ? 'danger' : ($media->file_type == 'document' ? 'warning' : 'secondary')) }} position-absolute top-0 end-0 m-2">
                                    {{ strtoupper($media->file_type) }}
                                </span>
                            </div>
                            <div class="card-body p-2">
                                <small class="text-truncate d-block" title="{{ $media->file_name }}">{{ $media->file_name }}</small>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted">{{ $media->size_text }}</small>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="{{ $media->url }}" target="_blank"><i class="fa fa-eye me-2"></i>预览</a></li>
                                            <li><a class="dropdown-item" href="{{ route('soho.media.download', $media) }}"><i class="fa fa-download me-2"></i>下载</a></li>
                                            <li>
                                                <form method="POST" action="{{ route('soho.media.destroy', $media) }}" onsubmit="return confirm('确定删除此文件？')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fa fa-trash me-2"></i>删除
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted py-5">
                        <i class="fa fa-inbox fa-3x mb-3"></i>
                        <p>暂无媒体文件，点击「上传文件」开始</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 分页 -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                共 {{ $mediaItems->total() }} 个文件
            </div>
            {{ $mediaItems->links() }}
        </div>
    </div>
</div>

<!-- 上传文件弹窗 -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('soho.media.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-upload me-2"></i>上传媒体文件</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">选择文件</label>
                        <input type="file" name="file" class="form-control" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx" required>
                        <div class="form-text">支持的格式：图片(jpg/png/gif/webp)、视频(mp4/avi)、文档(pdf/doc/xls)等，单个文件最大 5MB</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">开始上传</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .media-card { cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
    .media-card:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .media-thumb { overflow: hidden; }
    .media-thumb img { width: 100%; height: 150px; object-fit: cover; }
</style>
@endsection