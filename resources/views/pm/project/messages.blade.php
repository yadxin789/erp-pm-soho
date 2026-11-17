@extends('pm::layouts.app')

@section('title', $project->name.' - 聊天室')

@section('content')
@include('pm::project.partials.tabs', ['project' => $project, 'activeTab' => 'chat'])

<div class="pm-panel">
    <div class="pm-chat-body">
        <aside class="pm-chat-side">
            <div class="proj"><i class="fas fa-folder-open" aria-hidden="true"></i>{{ $project->name }}</div>
            <a class="pm-room active" href="{{ route('pm.project.messages.index', $project->id) }}">
                <span class="ic"><i class="fas fa-users" aria-hidden="true"></i></span>
                <span>项目群聊<small class="d-block text-muted">全体成员</small></span>
            </a>
            <div class="pm-members-title">项目成员</div>
            <div class="pm-members">
                @foreach ($participants as $m)
                    <a class="pm-member {{ $peerId === $m->id ? 'active' : '' }}"
                       href="{{ route('pm.project.messages.private', [$project->id, $m->id]) }}">
                        <span class="ava">{{ mb_substr($m->displayName(), 0, 1) }}</span>
                        <span>{{ $m->displayName() }}<small>离线</small></span>
                    </a>
                @endforeach
            </div>
        </aside>

        <section class="pm-chat-main">
            <div class="pm-chat-head">
                <span><i class="fas fa-comments text-primary" aria-hidden="true"></i>
                    {{ $peer ? $project->name.' - 与 '.$peer->displayName().' 私聊' : $project->name.' - 聊天室' }}</span>
                <form method="POST" action="{{ route('pm.project.messages.clear', $project->id) }}" class="m-0">
                    @csrf
                    <input type="hidden" name="peer" value="{{ $peerId }}">
                    <button type="submit" class="clear"><i class="fas fa-trash" aria-hidden="true"></i> 清空我的留言板</button>
                </form>
            </div>

            <div class="pm-msg-list" id="pm-msg-list">
                @forelse ($messages as $message)
                    <div class="pm-msg {{ $message->user_id === auth()->id() ? 'mine' : '' }}">
                        <div class="ava">{{ mb_substr($message->sender?->displayName() ?? '?', 0, 1) }}</div>
                        <div>
                            <div class="pm-msg-meta">{{ $message->sender?->displayName() }} · {{ $message->created_at?->format('H:i') }}</div>
                            <div class="pm-bubble">
                                @if ($message->content)<div>{!! nl2br(e($message->content)) !!}</div>@endif
                                @if ($message->file_path)
                                    <a class="d-block small mt-1" href="{{ route('pm.project.messages.file', [$project->id, $message->id]) }}">
                                        <i class="fas fa-paperclip" aria-hidden="true"></i> {{ $message->file_name }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="pm-empty" style="margin:auto">
                        <i class="far fa-comments" aria-hidden="true"></i>
                        暂无聊天记录，快开始聊天吧！
                    </div>
                @endforelse
            </div>

            <form class="pm-composer" method="POST" action="{{ route('pm.project.messages.store', $project->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="peer" value="{{ $peerId }}">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="pm-toggle y" id="pm-emoji-toggle">😊 表情</button>
                    <label class="pm-toggle f mb-0" for="pm-file">📎 文件</label>
                    <input class="d-none" type="file" id="pm-file" name="file">
                    <span class="small text-muted" id="pm-file-name"></span>
                    <button type="submit" class="btn btn-primary btn-sm ms-auto px-4">发送</button>
                </div>
                <div class="d-none flex-wrap gap-1 mt-2" id="pm-emoji-panel">
                    @foreach (['👍','🙏','🎉','😄','😅','🤔','✅','❗','🚀','📌'] as $emoji)
                        <button type="button" class="btn btn-sm btn-light" data-emoji="{{ $emoji }}">{{ $emoji }}</button>
                    @endforeach
                </div>
                <label class="visually-hidden" for="pm-content">消息内容</label>
                <textarea id="pm-content" name="content" rows="2" maxlength="2000" placeholder="输入消息..."></textarea>
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var toggle = document.getElementById('pm-emoji-toggle');
    var panel = document.getElementById('pm-emoji-panel');
    var input = document.getElementById('pm-content');
    var list = document.getElementById('pm-msg-list');
    if (list) { list.scrollTop = list.scrollHeight; }
    if (toggle) {
        toggle.addEventListener('click', function () {
            panel.classList.toggle('d-none');
            panel.classList.toggle('d-flex');
        });
    }
    document.querySelectorAll('[data-emoji]').forEach(function (b) {
        b.addEventListener('click', function () { input.value += b.dataset.emoji; input.focus(); });
    });
    var file = document.getElementById('pm-file');
    if (file) {
        file.addEventListener('change', function () {
            document.getElementById('pm-file-name').textContent = file.files[0] ? file.files[0].name : '';
        });
    }
})();
</script>
@endpush
