@extends('core::layouts.base')

@section('title', '团队聊天 · ' . config('app.name'))

@push('styles')
<style>
    .dw-chat-panel { background:#fff; border:1px solid var(--dw-line); border-radius:8px; overflow:hidden; }
    .dw-chat-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 18px; border-bottom:1px solid var(--dw-line); }
    .dw-chat-head h2 { margin:0; font-size:16px; font-weight:700; }
    .dw-chat-head h2 i { color:var(--dw-brand); margin-right:8px; }
    .dw-chat-clear { border:1px solid #f0aab4; color:#d63b52; background:#fff; border-radius:6px; padding:5px 12px; font-size:12px; }
    .dw-chat-body { display:grid; grid-template-columns:238px minmax(0,1fr); min-height:520px; }
    @media (max-width:900px){ .dw-chat-body{ grid-template-columns:1fr } }
    .dw-chat-rooms { border-right:1px solid var(--dw-line); background:#fff; display:flex; flex-direction:column; }
    .dw-chat-project { padding:12px 14px; font-size:13px; font-weight:600; color:var(--dw-ink); border-bottom:1px solid var(--dw-line); }
    .dw-chat-project i { color:var(--dw-brand); margin-right:6px; }
    .dw-chat-room-item { display:flex; align-items:center; gap:10px; padding:11px 14px; font-size:13px; color:var(--dw-ink);
        border-left:3px solid transparent; border-radius:0; }
    .dw-chat-room-item .dot { width:30px; height:30px; border-radius:50%; background:#e8efff; color:#2f6fed;
        display:grid; place-items:center; flex:0 0 30px; }
    .dw-chat-room-item small { display:block; color:var(--dw-muted); font-size:11px; }
    .dw-chat-room-item.active { background:#eef3ff; border-left-color:var(--dw-brand); }
    .dw-chat-members-title { padding:10px 14px 6px; font-size:12px; color:#9aa1b1; }
    .dw-chat-members { flex:1; overflow-y:auto; padding-bottom:8px; }
    .dw-chat-member { display:flex; align-items:center; gap:9px; padding:9px 14px; font-size:13px; color:var(--dw-ink); }
    .dw-chat-member:hover { background:#f6f8fd; }
    .dw-chat-member.active { background:#eef3ff; }
    .dw-chat-member .ava { width:30px; height:30px; border-radius:50%; background:#dfe3ea; color:#5b6270;
        display:grid; place-items:center; font-size:12px; flex:0 0 30px; }
    .dw-chat-member small { display:block; color:var(--dw-muted); font-size:11px; }
    .dw-chat-main { display:flex; flex-direction:column; min-width:0; }
    .dw-chat-list { flex:1; display:flex; flex-direction:column; gap:12px; overflow-y:auto; padding:16px 18px; }
    .dw-chat-msg { display:flex; gap:10px; align-items:flex-start; }
    .dw-chat-msg.is-mine { flex-direction:row-reverse; }
    .dw-chat-avatar { width:32px; height:32px; border-radius:50%; background:#eef2ff; color:var(--dw-brand);
        display:grid; place-items:center; font-weight:700; font-size:13px; flex:0 0 32px; }
    .dw-chat-bubble { max-width:72%; padding:9px 12px; border-radius:8px; background:#f5f6fa; font-size:13px; line-height:1.55; }
    .dw-chat-msg.is-mine .dw-chat-bubble { background:#e9efff; }
    .dw-chat-meta { font-size:11px; color:var(--dw-muted); margin-bottom:3px; display:flex; gap:8px; align-items:center; }
    .dw-chat-attach { margin-top:6px; display:flex; flex-direction:column; gap:4px; }
    .dw-chat-attach a { font-size:12px; }
    .dw-chat-img { max-width:220px; border-radius:8px; margin-top:6px; }
    .dw-chat-empty { margin:auto; text-align:center; color:var(--dw-muted); font-size:13px; }
    .dw-chat-empty i { display:block; font-size:52px; color:#c9cfdb; margin-bottom:12px; }
    .dw-chat-compose { border-top:1px solid var(--dw-line); padding:10px 14px 14px; }
    .dw-chat-tools { display:flex; align-items:center; gap:8px; margin-bottom:8px; flex-wrap:wrap; }
    .dw-chat-tools .tbtn { border-radius:4px; border:0; font-size:12px; padding:6px 14px; color:#fff; background:#f0b429; }
    .dw-chat-tools .tbtn.file { background:#17a2b8; }
    .dw-chat-tools .send { margin-left:auto; background:#2f6fed; color:#fff; border:0; border-radius:4px; padding:7px 26px; font-size:13px; }
    .dw-chat-emoji { display:none; }
    .dw-chat-emoji { flex-wrap:wrap; gap:4px; margin-bottom:8px; }
    .dw-chat-emoji button { border:0; background:none; font-size:17px; line-height:1; padding:2px; cursor:pointer; }
    .dw-chat-hint { font-size:12px; color:var(--dw-muted); }
    .dw-chat-input { width:100%; border:1px solid var(--dw-line); border-radius:6px; padding:9px 12px; font:inherit; resize:vertical; }
</style>
@endpush

@section('content')
<div class="dw-chat-panel">
    <div class="dw-chat-head">
        <h2><i class="fas fa-comments" aria-hidden="true"></i>{{ $peer ? '与 '.$peer->displayName().' 的私聊' : ($roomType === 'project' ? '项目 #'.$roomId.' 留言板' : '团队聊天室') }}</h2>
        <form method="POST" action="{{ route('core.chat.clear') }}" class="m-0">
            @csrf
            <input type="hidden" name="module" value="{{ $module }}">
            <input type="hidden" name="room" value="{{ $roomType }}">
            <input type="hidden" name="to" value="{{ $roomType === 'private' ? $roomId : '' }}">
            <input type="hidden" name="project" value="{{ $roomType === 'project' ? $roomId : '' }}">
            <button type="submit" class="dw-chat-clear"><i class="fas fa-trash" aria-hidden="true"></i> 清空我的留言板</button>
        </form>
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-0 rounded-0 py-2" role="status">{{ session('status') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning mb-0 rounded-0 py-2" role="alert">{{ session('warning') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-0 rounded-0 py-2" role="alert">{{ session('error') }}</div>
    @endif

    <div class="dw-chat-body">
        <aside class="dw-chat-rooms" aria-label="房间与成员">
            <div class="dw-chat-project"><i class="fas fa-users" aria-hidden="true"></i>{{ strtoupper($module) }} · 协作</div>
            <a class="dw-chat-room-item {{ $roomType === 'group' ? 'active' : '' }}"
               href="{{ route('core.chat.index', ['module' => $module, 'room' => 'group']) }}">
                <span class="dot"><i class="fas fa-comments" aria-hidden="true"></i></span>
                <span>模块群聊<small>全体成员</small></span>
            </a>
            @if ($roomType === 'project')
                <a class="dw-chat-room-item active"
                   href="{{ route('core.chat.index', ['module' => $module, 'room' => 'project', 'project' => $roomId]) }}">
                    <span class="dot"><i class="fas fa-comments" aria-hidden="true"></i></span>
                    <span>项目留言板 #{{ $roomId }}<small>项目成员</small></span>
                </a>
            @endif
            <div class="dw-chat-members-title">项目成员 / 同事</div>
            <div class="dw-chat-members">
                @foreach ($members as $m)
                    <a class="dw-chat-member {{ $roomType === 'private' && $roomId === $m['id'] ? 'active' : '' }}"
                       href="{{ route('core.chat.index', ['module' => $module, 'room' => 'private', 'to' => $m['id']]) }}">
                        <span class="ava">{{ mb_substr($m['name'], 0, 1) }}</span>
                        <span>{{ $m['name'] }}<small>离线</small></span>
                    </a>
                @endforeach
            </div>
        </aside>

        <section class="dw-chat-main">
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
                                                    <i class="fas fa-paperclip"></i> {{ $file['name'] }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="dw-chat-empty">
                        <i class="far fa-comments" aria-hidden="true"></i>
                        暂无聊天记录，快开始聊天吧！
                    </div>
                @endforelse
            </div>

            <form class="dw-chat-compose" id="chat-form" method="POST" action="{{ route('core.chat.send') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="module" value="{{ $module }}">
                <input type="hidden" name="room" value="{{ $roomType }}">
                <input type="hidden" name="to" value="{{ $roomType === 'private' ? $roomId : '' }}">
                <input type="hidden" name="project" value="{{ $roomType === 'project' ? $roomId : '' }}">

                <div class="dw-chat-tools">
                    <button type="button" class="tbtn" id="emoji-toggle">😊 表情</button>
                    <label class="tbtn file mb-0" for="chat-files">📎 文件</label>
                    <input class="dw-sr" type="file" id="chat-files" name="files[]" multiple
                           accept=".{{ implode(',', $allowedExt) }}">
                    <span class="dw-chat-hint" id="chat-status"></span>
                    <button class="send" type="submit">发送</button>
                </div>

                <div class="dw-chat-emoji" id="emoji-panel" aria-label="快捷表情">
                    @foreach (['👍','🙏','🎉','😄','😅','🤔','✅','❗','🚀','📌'] as $emoji)
                        <button type="button" data-emoji="{{ $emoji }}" aria-label="插入表情 {{ $emoji }}">{{ $emoji }}</button>
                    @endforeach
                </div>

                <label class="visually-hidden" for="chat-content">消息内容</label>
                <textarea class="dw-chat-input" id="chat-content" name="content" rows="2" maxlength="2000"
                          placeholder="输入消息…"></textarea>
                <p class="dw-chat-hint mb-0 mt-1">附件上限 {{ intdiv($maxFileKb, 1024) }}MB，最多 5 个；Enter 发送，Shift+Enter 换行。</p>
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

    document.getElementById('emoji-toggle').addEventListener('click', function () {
        var panel = document.getElementById('emoji-panel');
        panel.style.display = panel.style.display === 'flex' ? 'none' : 'flex';
    });

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
