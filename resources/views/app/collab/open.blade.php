@extends('layouts.business')

@section('title', $project->name . ' · ' . $doc->title)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="page-title mb-1"><i class="fa-regular fa-file-lines"></i>{{ $doc->title }}</h2>
            <div class="small text-muted">
                {{ $project->name }} · {{ ['doc' => '文档', 'sheet' => '表格', 'slide' => '演示'][$doc->ext] ?? $doc->ext }}
                · 最近保存 <span id="saved-at">{{ optional($doc->last_saved_at)->format('m-d H:i:s') ?: '—' }}</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-primary btn-sm" id="btn-save"><i class="fa-solid fa-floppy-disk me-1"></i>立即保存</button>
            <a href="{{ erp_route('app.projects.collab.download', [$project, $doc]) }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download me-1"></i>下载</a>
            <a href="{{ erp_route('app.projects.collab.index', $project) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回列表</a>
        </div>
    </div>

    <div class="alert alert-light border d-flex justify-content-between align-items-center py-2">
        <span class="small"><i class="fa-solid fa-cloud me-1"></i>内容保存在本机，断网不影响编辑；每 5 秒自动保存一次。</span>
        <span class="small text-success" id="save-state">已同步</span>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($doc->ext === 'doc')
                <div id="toolbar" class="border-bottom p-2 d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-sm btn-light" data-cmd="bold"><i class="fa-solid fa-bold"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-cmd="italic"><i class="fa-solid fa-italic"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-cmd="underline"><i class="fa-solid fa-underline"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-cmd="formatBlock" data-value="h3">标题</button>
                    <button type="button" class="btn btn-sm btn-light" data-cmd="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-cmd="insertOrderedList"><i class="fa-solid fa-list-ol"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-cmd="removeFormat">清除格式</button>
                </div>
                <div id="editor" contenteditable="true" style="min-height:520px;padding:24px;outline:none;background:#fff;">
                    {!! $content !!}
                </div>
            @else
                <textarea id="editor" class="form-control border-0" style="min-height:540px;font-family:Consolas,monospace;font-size:14px;border-radius:0;">{{ $content }}</textarea>
                <div class="small text-muted p-2 border-top">
                    @if($doc->ext === 'sheet')
                        表格以制表符分隔列、换行分隔行，可直接粘贴 Excel 内容。
                    @else
                        演示稿使用 Markdown：# 一级标题、## 二级标题、- 列表。
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script>
        (function () {
            var editor = document.getElementById('editor');
            var saveUrl = @json(erp_route('app.projects.collab.save', [$project, $doc]));
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var stateEl = document.getElementById('save-state');
            var savedAtEl = document.getElementById('saved-at');
            var dirty = false;
            var lastSaved = editor.innerText || editor.value || '';

            function currentContent() {
                return editor.isContentEditable ? editor.innerHTML : editor.value;
            }

            function markDirty() { dirty = true; stateEl.textContent = '有未保存修改'; stateEl.className = 'small text-warning'; }

            editor.addEventListener('input', markDirty);
            document.querySelectorAll('#toolbar [data-cmd]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var cmd = btn.getAttribute('data-cmd');
                    var val = btn.getAttribute('data-value') || null;
                    document.execCommand(cmd, false, val);
                    editor.focus();
                    markDirty();
                });
            });

            function save(force) {
                var html = currentContent();
                if (!force && !dirty) { return; }
                fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ content: html })
                }).then(function (r) { return r.json(); }).then(function (d) {
                    dirty = false;
                    stateEl.textContent = '已同步';
                    stateEl.className = 'small text-success';
                    savedAtEl.textContent = d.saved_at + '（' + d.saved_by + '）';
                }).catch(function () {
                    stateEl.textContent = '保存失败，将重试';
                    stateEl.className = 'small text-danger';
                });
            }

            document.getElementById('btn-save').addEventListener('click', function () { save(true); });
            setInterval(function () { save(false); }, 5000);
            window.addEventListener('beforeunload', function () { if (dirty) { save(true); } });
        })();
    </script>

    @if($onlyofficeUrl)
        <script>
            // 若配置了 OnlyOffice 文档服务（本地/内网容器），优先用专业协同编辑器；加载失败自动保留上面的内置编辑器
            (function () {
                var script = document.createElement('script');
                script.src = @json($onlyofficeUrl . '/web-apps/apps/api/documents/api.js');
                script.onload = function () {
                    var holder = document.createElement('div');
                    holder.style.height = '600px';
                    document.querySelector('.card-body').appendChild(holder);
                    var builtin = document.getElementById('editor');
                    var toolbar = document.getElementById('toolbar');
                    if (builtin) { builtin.style.display = 'none'; }
                    if (toolbar) { toolbar.style.display = 'none'; }
                    new DocsAPI.DocEditor(holder.id = 'onlyoffice-editor', {
                        documentType: @json($doc->doc_type),
                        document: {
                            title: @json($doc->title),
                            key: @json($doc->doc_key . '-' . optional($doc->last_saved_at)->timestamp),
                            url: @json(erp_route('app.collab.download', ['doc' => $doc->doc_key]))
                        },
                        editorConfig: {
                            lang: 'zh',
                            callbackUrl: @json(erp_route('app.collab.callback', ['doc' => $doc->doc_key]))
                        },
                        height: '600px',
                        width: '100%'
                    });
                };
                script.onerror = function () { /* 容器未运行：保持内置编辑器 */ };
                document.head.appendChild(script);
            })();
        </script>
    @endif
@endsection
