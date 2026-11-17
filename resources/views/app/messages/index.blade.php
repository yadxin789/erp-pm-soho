@extends('layouts.business')

@section('title', $project->name . ' · 项目组聊天室')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="page-title mb-1"><i class="fa-solid fa-comments"></i>项目组聊天室</h2>
            <div class="small text-muted">{{ $project->name }} · 编号 {{ $project->code ?: '—' }} · 参与人 {{ $members->count() }} 人</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ erp_route('app.projects.show', $project) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>项目详情</a>
            <a href="{{ erp_route('app.projects.collab.index', $project) }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-pen me-1"></i>协同办公</a>
            <a href="{{ erp_route('app.projects.voice.index', $project) }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-phone-volume me-1"></i>电话会议</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-users me-1"></i>群聊消息</span>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="checkbox" class="form-check-input" id="auto-refresh" checked>
                        <label class="form-check-label small" for="auto-refresh">自动刷新</label>
                        <form method="POST" action="{{ erp_route('app.projects.messages.clear-mine', $project) }}" onsubmit="return confirm('确定清空我发出的全部消息？')">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-eraser me-1"></i>清空我的消息</button>
                        </form>
                    </div>
                </div>
                <div class="card-body" id="chat-box" style="max-height:520px;overflow-y:auto;">
                    @forelse($messages as $message)
                        @php $mine = (int) $message->user_id === (int) auth()->id(); @endphp
                        <div class="d-flex mb-3 {{ $mine ? 'justify-content-end' : '' }}">
                            <div class="p-2 rounded-3 {{ $mine ? 'bg-primary text-white' : 'bg-light' }}" style="max-width:75%;">
                                <div class="small mb-1 {{ $mine ? 'text-white-50' : 'text-muted' }}">
                                    {{ $message->user->name ?? '未知' }} · {{ optional($message->created_at)->format('m-d H:i') }}
                                </div>
                                @if($message->content)
                                    <div style="white-space:pre-wrap;">{{ $message->content }}</div>
                                @endif
                                @if($message->file_path)
                                    <div class="mt-1">
                                        <a href="{{ erp_route('app.messages.file', $message) }}" class="btn btn-sm {{ $mine ? 'btn-light' : 'btn-outline-primary' }}">
                                            <i class="fa-solid fa-paperclip me-1"></i>{{ $message->file_name }}
                                        </a>
                                    </div>
                                @endif
                                @if($mine || (int) $project->creator_id === (int) auth()->id())
                                    <form method="POST" action="{{ erp_route('app.projects.messages.destroy', [$project, $message]) }}" class="mt-1" onsubmit="return confirm('删除这条消息？')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-link btn-sm p-0 {{ $mine ? 'text-white-50' : 'text-muted' }}"><i class="fa-regular fa-trash-can"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center my-5">还没有消息，打个招呼吧</p>
                    @endforelse
                </div>
                <div class="card-footer">
                    <form method="POST" action="{{ erp_route('app.projects.messages.store', $project) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-7">
                                <textarea name="content" class="form-control" rows="2" placeholder="输入消息内容（Ctrl+Enter 发送）" onkeydown="if(event.ctrlKey&&event.key==='Enter'){this.form.submit()}"></textarea>
                            </div>
                            <div class="col-md-3">
                                <input type="file" name="file" class="form-control">
                            </div>
                            <div class="col-md-2 d-grid">
                                <button class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>发送</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-header"><i class="fa-solid fa-user-group me-1"></i>参与人（可私聊）</div>
                <div class="list-group list-group-flush">
                    @foreach($members as $member)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fa-solid fa-circle-user me-1 text-muted"></i>{{ $member->name }}
                                @if((int) $member->id === (int) $project->creator_id)
                                    <span class="badge bg-primary-subtle text-primary-emphasis">创建人</span>
                                @endif
                            </span>
                            @if((int) $member->id !== (int) auth()->id())
                                <a href="{{ erp_route('app.projects.messages.private', [$project, $member]) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa-regular fa-comment-dots"></i>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var box = document.getElementById('chat-box');
                if (box) { box.scrollTop = box.scrollHeight; }
                var timer = setInterval(function () {
                    var cb = document.getElementById('auto-refresh');
                    if (cb && cb.checked && document.activeElement.tagName !== 'TEXTAREA') {
                        window.location.reload();
                    }
                }, 10000);
            })();
        </script>
    @endpush
@endsection
