@extends('core::layouts.base')

@section('title', '团队聊天 · ' . config('app.name'))

@push('styles')
<style>
    .dw-chat { display: grid; grid-template-columns: 240px minmax(0, 1fr); gap: 18px; }
    @media (max-width: 900px) { .dw-chat { grid-template-columns: 1fr; } }
    .dw-chat-side .dw-card { margin-bottom: 12px; }
    .dw-chat-room { font-size: 13px; }
    .dw-chat-list { display: flex; flex-direction: column; gap: 12px; min-height: 320px; max-height: 58vh; overflow-y: auto; padding: 14px; background: #fff; border: 1px solid var(--dw-line); border-radius: var(--dw-radius); }
    .dw-chat-msg { display: flex; gap: 10px; align-items: flex-start; }
    .dw-chat-msg.is-mine { flex-direction: row-reverse; }
    .dw-chat-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--dw-brand-soft, #eef2ff);
        color: var(--dw-brand); display: grid; place-items: center; font-weight: 700; font-size: 13px; flex: 0 0 32px; }
    .dw-chat-bubble { max-width: 72%; padding: 9px 12px; border-radius: 10px; background: #f5f6fa; font-size: 13px; line-height: 1.55; }
    .dw-chat-msg.is-mine .dw-chat-bubble { background: #e9efff; }
    .dw-chat-meta { font-size: 11px; color: var(--dw-muted); margin-bottom: 3px; display: flex; gap: 8px; align-items: center; }
    .dw-chat-attach { margin-top: 6px; display: flex; flex-direction: column; gap: 4px; }
    .dw-chat-attach a { font-size: 12px; }
    .dw-chat-img { max-width: 220px; border-radius: 8px; margin-top: 6px; }
    .dw-chat-compose { margin-top: 12px; background: #fff; border: 1px solid var(--dw-line); border-radius: var(--dw-radius); padding: 12px; }
    .dw-chat-emoji { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px; }
    .dw-chat-emoji button { border: 0; background: none; font-size: 17px; line-height: 1; padding: 2px; cursor: pointer; }
    .dw-chat-hint { font-size: 12px; color: var(--dw-muted); }
    .dw-chat-empty { text-align: center; color: var(--dw-muted); font-size: 13px; padding: 40px 0; }
</style>
@endpush

@section('content')
@include('core::partials.topbar')
<div class="dw-page">
    <header class="dw-page-head">
        <div>
            <p class="dw-eyebrow">三模块共用 · 同一套消息表</p>
            <h1 class="dw-page-title">团队聊天</h1>
            <p class="dw-page-desc">
                当前房间：{{ $module }} / {{ $roomKey }} · 共 {{ $messages->count() }} 条
                @if ($peer) · 与 {{ $peer->displayName() }} 私聊 @endif
                @if ($roomType === 'project') · 项目 #{{ $roomId }} 留言板 @endif
            </p>
        </div>
        <div class="dw-page-actions">
            <form method="POST" action="{{ route('core.chat.clear') }}">
                @csrf
                <input type="hidden" name="module" value="{{ $module }}">
                <input type="hidden" name="room" value="{{ $roomType }}">
                <input type="hidden" name="to" value="{{ $roomType === 'private' ? $roomId : '' }}">
                <input type="hidden" name="project" value="{{ $roomType === 'project' ? $roomId : '' }}">
                <button type="submit" class="dw-btn">清空我的显示记录</button>
            </form>
        </div>
    </header>

    @if (session('status'))
        <section class="dw-card dw-alert dw-alert-ok" role="status">
            <p class="dw-hint">{{ session('status') }}</p>
        </section>
    @endif
    @if (session('error'))
        <section class="dw-card dw-alert" role="alert">
            <p class="dw-hint">{{ session('error') }}</p>
        </section>
    @endif

    <div class="dw-chat">
        <aside class="dw-chat-side">
            <div class="dw-card">
                <div class="dw-card-body dw-chat-room">
                    <p class="fw-semibold mb-2">切换房间</p>
                    <nav class="d-flex flex-column gap-1" aria-label="房间类型">
                        <a class="dw-btn {{ $roomType === 'group' ? 'is-current' : '' }}"
                           href="{{ route('core.chat.index', ['module' => $module, 'room' => 'group']) }}">模块群聊</a>
                        @if ($roomType === 'project')
                            <a class="dw-btn {{ $roomType === 'project' ? 'is-current' : '' }}"
                               href="{{ route('core.chat.index', ['module' => $module, 'room' => 'project', 'project' => $roomId]) }}">项目留言板 #{{ $roomId }}</a>
                        @endif
                    </nav>
                </div>
            </div>

            <div class="dw-card">
                <div class="dw-card-body dw-chat-room">
                    <p class="fw-semibold mb-2">发起私聊</p>
                    <form method="GET" action="{{ route('core.chat.index') }}" class="d-flex flex-column gap-2">
                        <input type="hidden" name="module" value="{{ $module }}">
                        <input type="hidden" name="room" value="private">
                        <label class="dw-chat-hint" for="peer-select">选择同事</label>
                        <select class="form-select form-select-sm" id="peer-select" name="to" required>
                            <option value="">— 请选择 —</option>
                            @foreach ($members as $m)
                                <option value="{{ $m['id'] }}" @selected($roomType === 'private' && $roomId === $m['id'])>{{ $m['name'] }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="dw-btn">进入私聊</button>
                    </form>
                </div>
            </div>

            <div class="dw-card">
                <div class="dw-card-body dw-chat-room">
                    <p class="fw-semibold mb-2">语音会议</p>
                    <p class="dw-chat-hint mb-2">需要当面说的时候，开一间语音房（浏览器自带 WebRTC，不依赖外部服务）。</p>
                    <a class="dw-btn" href="{{ route('core.voice.index', ['module' => $module]) }}">进入语音房</a>
                </div>
            </div>
        </aside>

        <section>
            <div class="dw-chat-list" id="chat-list" aria-live="polite">
                @forelse ($messages as $message)
                    <article class="dw-chat-msg {{ $message->sender_id === auth()->id() ? 'is-mine' : '' }}"
                             data-id="{{ $message->id }}">
                        <div class="dw-chat-avatar">{{ mb_substr($message->sender?->displayName() ?? '?', 0, 1) }}</div>
                        <div>
                            <div class="dw-chat-meta">
                                <span>{{ $message->sender?->displayName() ?? '未知用户' }}</span>
                                <span>{{ $message->created_at?->format('H:i') }}</span>
                                @if ($message->trashed()) <span class="dw-badge dw-badge--muted">已撤回</span> @endif
                            </div>
                            <div class="dw-chat-bubble">
                                @if ($message->content)
                                    <div>{!! nl2br(e($message->content)) !!}</div>
                                @endif
                                @if ($message->hasAttachments())
                                    <div class="dw-chat-attach">
                                        @foreach ($message->attachments as $i => $file)
                                            @if (preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $file['name'] ?? ''))
                                                <a href="{{ route('core.chat.file', ['id' => $message->id, 'index' => $i]) }}" target="_blank" rel="noopener">
                                                    <img class="dw-chat-img" src="{{ route('core.chat.file', ['id' => $message->id, 'index' => $i]) }}" alt="{{ $file['name'] }}">
                                                </a>
                                            @else
                                                <a href="{{ route('core.chat.file', ['id' => $message->id, 'index' => $i]) }}" target="_blank" rel="noopener">
                                                    <i class="fa-solid fa-paperclip"></i> {{ $file['name'] }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="dw-chat-empty">这个房间还没有消息</div>
                @endforelse
            </div>

            <form class="dw-chat-compose" id="chat-form" method="POST" action="{{ route('core.chat.send') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="module" value="{{ $module }}">
                <input type="hidden" name="room" value="{{ $roomType }}">
                <input type="hidden" name="to" value="{{ $roomType === 'private' ? $roomId : '' }}">
                <input type="hidden" name="project" value="{{ $roomType === 'project' ? $roomId : '' }}">

                <div class="dw-chat-emoji" aria-label="快捷表情">
                    @foreach (['👍','🙏','🎉','😄','😅','🤔','✅','❗','🚀','📌'] as $emoji)
                        <button type="button" data-emoji="{{ $emoji }}" aria-label="插入表情 {{ $emoji }}">{{ $emoji }}</button>
                    @endforeach
                </div>

                <label class="visually-hidden" for="chat-content">消息内容</label>
                <textarea class="form-control" id="chat-content" name="content" rows="2" maxlength="2000"
                          placeholder="说点什么…（Enter 发送，Shift+Enter 换行）"></textarea>

                <div class="d-flex align-items-center justify-content-between gap-2 mt-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <label class="dw-chat-hint mb-0" for="chat-files">附件（≤{{ intdiv($maxFileKb, 1024) }}MB，最多 5 个）</label>
                        <input class="form-control form-control-sm" type="file" id="chat-files" name="files[]" multiple
                               accept=".{{ implode(',.', $allowedExt) }}">
                    </div>
                    <div class="d-flex gap-2">
                        <span class="dw-chat-hint align-self-center" id="chat-status"></span>
                        <button class="btn btn-primary btn-sm" type="submit">发送</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var list = document.getElementById('chat-list');
    var form = document.getElementById('chat-form');
    var input = document.getElementById('chat-content');
    var statusEl = document.getElementById('chat-status');
    var lastId = {{ (int) ($messages->last()?->id ?? 0) }};
    @php
        $pollRoute = route('core.chat.messages', array_filter([
            'module' => $module,
            'room' => $roomType,
            'to' => $roomType === 'private' ? $roomId : null,
            'project' => $roomType === 'project' ? $roomId : null,
        ]));
    @endphp
    var pollUrl = @json($pollRoute);

    function render(msg) {
        var article = document.createElement('article');
        article.className = 'dw-chat-msg' + (msg.is_my_message ? ' is-mine' : '');
        article.dataset.id = msg.id;

        var avatar = document.createElement('div');
        avatar.className = 'dw-chat-avatar';
        avatar.textContent = (msg.sender_name || '?').slice(0, 1);

        var wrap = document.createElement('div');
        var meta = document.createElement('div');
        meta.className = 'dw-chat-meta';
        var n = document.createElement('span'); n.textContent = msg.sender_name;
        var t = document.createElement('span'); t.textContent = msg.created_at;
        meta.appendChild(n); meta.appendChild(t);

        var bubble = document.createElement('div');
        bubble.className = 'dw-chat-bubble';
        if (msg.content) {
            var body = document.createElement('div');
            body.textContent = msg.content;
            bubble.appendChild(body);
        }
        (msg.attachments || []).forEach(function (f) {
            var a = document.createElement('a');
            a.href = f.url; a.target = '_blank'; a.rel = 'noopener';
            a.textContent = f.name;
            a.style.display = 'block';
            bubble.appendChild(a);
        });

        wrap.appendChild(meta);
        wrap.appendChild(bubble);
        article.appendChild(avatar);
        article.appendChild(wrap);
        list.appendChild(article);
        list.scrollTop = list.scrollHeight;
    }

    function poll() {
        if (document.hidden) { return; }
        fetch(pollUrl + '&after=' + lastId, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data || !data.messages || !data.messages.length) { return; }
                var empty = list.querySelector('.dw-chat-empty');
                if (empty) { empty.remove(); }
                data.messages.forEach(function (m) {
                    if (!list.querySelector('[data-id="' + m.id + '"]')) { render(m); }
                    lastId = Math.max(lastId, m.id);
                });
            })
            .catch(function () { /* 静默，下次继续 */ });
    }
    setInterval(poll, 5000);

    document.querySelectorAll('[data-emoji]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            input.value += btn.dataset.emoji;
            input.focus();
        });
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        statusEl.textContent = '发送中…';
        fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                if (!res.ok) {
                    statusEl.textContent = res.body.error || '发送失败';
                    return;
                }
                var empty = list.querySelector('.dw-chat-empty');
                if (empty) { empty.remove(); }
                if (!list.querySelector('[data-id="' + res.body.message.id + '"]')) { render(res.body.message); }
                lastId = Math.max(lastId, res.body.message.id);
                input.value = '';
                form.querySelector('#chat-files').value = '';
                statusEl.textContent = '已发送';
                setTimeout(function () { statusEl.textContent = ''; }, 1500);
            })
            .catch(function () { statusEl.textContent = '网络异常，请重试'; });
    });
})();
</script>
@endpush
