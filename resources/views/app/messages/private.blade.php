@extends('layouts.business')

@section('title', $project->name . ' · 与 ' . $target->name . ' 私聊')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="page-title mb-1"><i class="fa-solid fa-comment-dots"></i>与 {{ $target->name }} 私聊</h2>
            <div class="small text-muted">{{ $project->name }} 项目组内私聊</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ erp_route('app.projects.messages.index', $project) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回群聊</a>
            <form method="POST" action="{{ erp_route('app.projects.messages.private.clear-mine', [$project, $target]) }}" onsubmit="return confirm('清空我发出的私聊记录？')">
                @csrf
                <button class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-eraser me-1"></i>清空我的记录</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">项目参与人</div>
                <div class="list-group list-group-flush">
                    @foreach($members as $member)
                        <a href="{{ erp_route('app.projects.messages.private', [$project, $member]) }}"
                           class="list-group-item list-group-item-action {{ (int) $member->id === (int) $target->id ? 'active' : '' }}">
                            <i class="fa-solid fa-circle-user me-1"></i>{{ $member->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card">
                <div class="card-header"><i class="fa-solid fa-user me-1"></i>{{ $target->name }}</div>
                <div class="card-body" id="pm-box" style="height:460px;overflow-y:auto;background:#f8f9fa;"></div>
                <div class="card-footer">
                    <div class="input-group">
                        <input type="text" id="pm-input" class="form-control" placeholder="输入私聊内容，回车发送">
                        <button class="btn btn-primary" id="pm-send"><i class="fa-solid fa-paper-plane me-1"></i>发送</button>
                    </div>
                    <div class="small text-muted mt-1" id="pm-hint">消息每 3 秒自动拉取</div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var box = document.getElementById('pm-box');
                var input = document.getElementById('pm-input');
                var btn = document.getElementById('pm-send');
                var hint = document.getElementById('pm-hint');
                var historyUrl = @json(erp_route('app.api.chat.history', [$project, $target]));
                var sendUrl = @json(erp_route('app.api.chat.send', $project));
                var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                var lastCount = -1;

                function render(rows) {
                    if (rows.length === lastCount) { return; }
                    lastCount = rows.length;
                    var html = '';
                    rows.forEach(function (m) {
                        var cls = m.mine ? 'justify-content-end' : '';
                        var bg = m.mine ? 'bg-primary text-white' : 'bg-white border';
                        html += '<div class="d-flex mb-2 ' + cls + '"><div class="p-2 rounded-3 ' + bg + '" style="max-width:75%">'
                            + '<div class="small ' + (m.mine ? 'text-white-50' : 'text-muted') + '">' + m.sender + ' ' + m.time + '</div>'
                            + '<div style="white-space:pre-wrap">' + (m.content || '') + '</div>'
                            + (m.file_name ? '<div class="small"><i class="fa-solid fa-paperclip"></i> ' + m.file_name + '</div>' : '')
                            + '</div></div>';
                    });
                    box.innerHTML = html || '<p class="text-muted text-center mt-5">还没有私聊记录</p>';
                    box.scrollTop = box.scrollHeight;
                }

                function pull() {
                    fetch(historyUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function (r) { return r.json(); })
                        .then(function (d) { render(d.messages || []); })
                        .catch(function () { hint.textContent = '拉取失败，稍后重试'; });
                }

                function send() {
                    var text = input.value.trim();
                    if (!text) { return; }
                    input.value = '';
                    fetch(sendUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ receiver_id: {{ (int) $target->id }}, content: text })
                    }).then(function () { lastCount = -1; pull(); });
                }

                btn.addEventListener('click', send);
                input.addEventListener('keydown', function (e) { if (e.key === 'Enter') { send(); } });
                pull();
                setInterval(pull, 3000);
            })();
        </script>
    @endpush
@endsection
