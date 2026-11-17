@extends('core::layouts.base')

@section('title', '文件中心 · '.config('app.name'))

@section('content')
@include('core::partials.topbar')
<div class="dw-page">
    <header class="dw-page-head">
        <div>
            <p class="dw-eyebrow">三模块共用 · 一份文件库</p>
            <h1 class="dw-page-title">文件中心</h1>
            <p class="dw-page-desc">
                当前模块 {{ strtoupper($module) }} · 在用 {{ $items->count() }} 个 · 回收站 {{ $trash->count() }} 个
                · 单文件 ≤ {{ $maxKb / 1024 }} MB
            </p>
        </div>
        <div class="dw-page-actions">
            <a class="dw-btn {{ $tab === 'active' ? 'is-current' : '' }}" href="{{ route('core.files.index') }}">在用文件</a>
            <a class="dw-btn {{ $tab === 'trash' ? 'is-current' : '' }}" href="{{ route('core.files.index', ['tab' => 'trash']) }}">回收站</a>
        </div>
    </header>

    @if (session('success'))
        <section class="dw-card dw-alert dw-alert-ok" role="status"><p class="dw-hint">{{ session('success') }}</p></section>
    @endif
    @if (session('error'))
        <section class="dw-card dw-alert" role="alert"><p class="dw-hint">{{ session('error') }}</p></section>
    @endif

    @if ($tab === 'active')
        <section class="dw-card">
            <h2 class="dw-card-title">上传文件</h2>
            <form method="POST" action="{{ route('core.files.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="dw-field">
                    <label for="files">选择文件<span class="req">*</span></label>
                    <input class="dw-input" id="files" name="files[]" type="file" multiple required
                           accept=".{{ implode(',.', $allowedExt) }}">
                    <span class="dw-help">最多 5 个，单文件 ≤ {{ $maxKb / 1024 }} MB；支持 {{ implode(' / ', $allowedExt) }}。中文文件名原样保留。</span>
                </div>
                <div class="dw-field">
                    <label for="visibility">可见范围</label>
                    <select class="dw-input" id="visibility" name="visibility">
                        <option value="module">本模块所有登录用户可见</option>
                        <option value="private">仅我和审核人可见</option>
                    </select>
                </div>
                <div class="dw-field">
                    <label for="category">分类</label>
                    <input class="dw-input" id="category" name="category" type="text" maxlength="32"
                           value="attachment" placeholder="attachment / contract / drawing / voucher">
                </div>
                <div class="dw-page-actions">
                    <button type="submit" class="dw-btn">上传</button>
                </div>
            </form>
        </section>

        <section class="dw-card" style="margin-top:18px">
            <div class="dw-page-head" style="margin-bottom:10px">
                <h2 class="dw-card-title" style="margin:0">文件列表</h2>
                <form method="GET" action="{{ route('core.files.index') }}" class="dw-filter">
                    <div class="dw-field">
                        <label for="q" class="dw-sr">搜索文件名</label>
                        <input class="dw-input" id="q" name="q" type="search" value="{{ $keyword }}" placeholder="搜文件名…">
                    </div>
                    <button type="submit" class="dw-btn">搜索</button>
                </form>
            </div>

            @if ($items->isEmpty())
                <p class="dw-hint">这个模块还没有文件，先在上面传一个。</p>
            @else
                <table class="dw-table">
                    <caption class="dw-sr">在用文件</caption>
                    <thead>
                        <tr>
                            <th scope="col">文件名</th>
                            <th scope="col">大小</th>
                            <th scope="col">版本</th>
                            <th scope="col">上传人</th>
                            <th scope="col">上传时间</th>
                            <th scope="col">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($items as $file)
                        <tr>
                            <td style="min-width:220px">
                                <strong>{{ $file->original_name }}</strong>
                                <div class="dw-hint">
                                    {{ $file->category }} · {{ $file->visibility === 'private' ? '仅我与审核人' : '模块内可见' }}
                                    @if ($file->versions->isNotEmpty())
                                        · 历史 {{ $file->versions->count() }} 个版本
                                    @endif
                                </div>
                            </td>
                            <td style="white-space:nowrap">{{ app(\App\Core\Services\FileService::class)->formatSize($file->size) }}</td>
                            <td style="white-space:nowrap">v{{ $file->version }}</td>
                            <td style="white-space:nowrap">{{ $file->uploader?->displayName() ?? '—' }}</td>
                            <td style="white-space:nowrap">{{ $file->created_at?->format('Y-m-d H:i') }}</td>
                            <td style="white-space:nowrap">
                                <div class="dw-page-actions" style="flex-wrap:nowrap;gap:6px">
                                    <a class="dw-btn" style="padding:6px 10px" href="{{ route('core.files.download', $file->id) }}"
                                       aria-label="下载 {{ $file->original_name }}" title="下载"><i class="fas fa-download" aria-hidden="true"></i></a>
                                    @if (in_array($file->mime_type, \App\Core\Services\FileService::PREVIEWABLE_MIME, true))
                                        <a class="dw-btn" style="padding:6px 10px" href="{{ route('core.files.preview', $file->id) }}"
                                           target="_blank" rel="noopener" aria-label="预览 {{ $file->original_name }}" title="预览"><i class="fas fa-eye" aria-hidden="true"></i></a>
                                    @endif
                                    @if (in_array($file->extension(), ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv']))
                                        <a class="dw-btn" style="padding:6px 10px" href="{{ route('core.files.office', $file->id) }}"
                                           aria-label="在线编辑 {{ $file->original_name }}" title="在线编辑"><i class="fas fa-edit" aria-hidden="true"></i></a>
                                    @endif
                                    <form method="POST" action="{{ route('core.files.destroy', $file->id) }}"
                                          onsubmit="return confirm('移入回收站后仍可恢复，确定删除？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dw-btn dw-btn-danger" style="padding:6px 10px"
                                                aria-label="删除 {{ $file->original_name }}" title="移入回收站"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                    </form>
                                </div>

                                <details style="margin-top:8px">
                                    <summary class="dw-hint" style="cursor:pointer">上传新版本</summary>
                                    <form method="POST" action="{{ route('core.files.versions.store', $file->id) }}"
                                          enctype="multipart/form-data" class="dw-filter" style="margin-top:8px">
                                        @csrf
                                        <input class="dw-input" name="file" type="file" required style="max-width:260px">
                                        <button type="submit" class="dw-btn">提交 v{{ $file->version + 1 }}</button>
                                    </form>
                                </details>

                                @if ($file->versions->isNotEmpty())
                                    <details style="margin-top:6px">
                                        <summary class="dw-hint" style="cursor:pointer">历史版本（{{ $file->versions->count() }}）</summary>
                                        <ul class="dw-plain-list">
                                            @foreach ($file->versions as $version)
                                                <li>
                                                    <code style="white-space:nowrap">v{{ $version->version }}</code>
                                                    <span class="dw-hint">{{ $version->created_at?->format('Y-m-d H:i') }}</span>
                                                    <span class="dw-hint">上传人 {{ $version->uploader?->displayName() ?? '—' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    @else
        <section class="dw-card">
            <h2 class="dw-card-title">回收站</h2>
            @if ($trash->isEmpty())
                <p class="dw-hint">回收站是空的。</p>
            @else
                <ul class="dw-notice-list">
                    @foreach ($trash as $file)
                        <li class="dw-notice">
                            <div class="dw-notice-main">
                                <div class="dw-notice-head">
                                    <strong>{{ $file->original_name }}</strong>
                                    <span class="dw-badge dw-badge--muted">v{{ $file->version }}</span>
                                    <time class="dw-hint" datetime="{{ $file->deleted_at?->toIso8601String() }}">
                                        删除于 {{ $file->deleted_at?->format('Y-m-d H:i') }}
                                    </time>
                                </div>
                                <p class="dw-hint">
                                    {{ $file->uploader?->displayName() ?? '—' }} 上传 ·
                                    {{ app(\App\Core\Services\FileService::class)->formatSize($file->size) }} ·
                                    {{ $file->category }}
                                </p>
                            </div>
                            <div class="dw-notice-actions">
                                <form method="POST" action="{{ route('core.files.restore', $file->id) }}">
                                    @csrf
                                    <button type="submit" class="dw-btn">恢复</button>
                                </form>
                                <form method="POST" action="{{ route('core.files.purge', $file->id) }}"
                                      onsubmit="return confirm('彻底删除会同时清掉物理文件与历史版本，无法恢复，确定继续？');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dw-btn dw-btn-danger">彻底删除</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif
</div>
@endsection
