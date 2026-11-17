@extends('core::layouts.base')

@section('title', '在线编辑 · '.$file->original_name)

@section('content')
<div class="dw-page">
    <header class="dw-page-head">
        <div>
            <p class="dw-eyebrow">{{ strtoupper($file->module) }} · OnlyOffice 协同编辑</p>
            <h1 class="dw-page-title">{{ $file->original_name }}</h1>
            <p class="dw-page-desc">v{{ $file->version }} · {{ $file->mime_type }} · 保存由 OnlyOffice 回调写回，旧版本自动进历史。</p>
        </div>
        <div class="dw-page-actions">
            <a class="dw-btn" href="{{ route('core.files.index') }}">返回文件中心</a>
        </div>
    </header>

    @if (! $config)
        <section class="dw-card dw-empty">
            <p class="title">还没有配置 OnlyOffice 文档服务</p>
            <p>设置 <code>ONLYOFFICE_URL</code>（例如 http://127.0.0.1:8080）后，这里会直接内嵌编辑器；回调地址固定为
                <code>/files/onlyoffice/callback/{{ $file->id }}</code>，按 document key 校验，无需登录态。</p>
        </section>
    @else
        <section class="dw-card" style="padding:0;overflow:hidden">
            <div id="onlyoffice-placeholder" style="height:640px"></div>
        </section>

        @push('scripts')
        <script src="{{ $serverUrl }}/web-apps/apps/api/documents/api.js"></script>
        <script>
            (function () {
                var config = @json($config);
                if (window.DocsAPI) {
                    new DocsAPI.DocEditor('onlyoffice-placeholder', config);
                } else {
                    document.getElementById('onlyoffice-placeholder').textContent = '文档服务脚本加载失败，请检查 ONLYOFFICE_URL。';
                }
            })();
        </script>
        @endpush
    @endif
</div>
@endsection
