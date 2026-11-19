@extends('pm::layouts.app')

@section('title', '电话会议 - ' . $project->name)

@section('content')
@include('pm::project.partials.project-tabs', ['project' => $project, 'activeTab' => 'voice'])

<style>
    .voice-shell { display: flex; gap: 0; border: 1px solid #dee2e6; border-radius: 6px; overflow: hidden; background: #fff; }
    .voice-side { width: 240px; flex: 0 0 240px; border-right: 1px solid #dee2e6; background: #f8f9fa; display: flex; flex-direction: column; }
    .voice-main { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
    .voice-member { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-bottom: 1px dashed #e9ecef; font-size: 13px; }
    .voice-dot { width: 8px; height: 8px; border-radius: 50%; background: #adb5bd; flex: 0 0 8px; }
    .voice-dot.on { background: #2ecc71; }
    .voice-avatar { width: 28px; height: 28px; border-radius: 50%; background: #e7f1ff; color: #0d6efd; display: grid; place-items: center; font-weight: 600; font-size: 13px; }
    .voice-stage { padding: 18px; }
    .voice-tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-bottom: 14px; }
    .voice-tile { border: 1px solid #e9ecef; border-radius: 10px; padding: 12px; background: #fafbff; }
    .voice-tile.owner { border-color: #c7d2fe; background: #f5f7ff; }
    .voice-log { font-size: 12px; color: #6c757d; max-height: 140px; overflow-y: auto; border-top: 1px dashed #e9ecef; padding-top: 8px; }
    @media (max-width: 900px) { .voice-shell { flex-direction: column; } .voice-side { width: 100%; flex: 0 0 auto; border-right: 0; border-bottom: 1px solid #dee2e6; } }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-1"><i class="fa fa-phone text-primary me-1"></i>项目电话会议</h5>
        <div class="text-muted small">
            房间 <span class="font-monospace">{{ $room->name }}</span>
            · 状态 <span id="room-status">{{ $room->status === 'open' ? '进行中' : '已结束' }}</span>
            · 开始 {{ $room->started_at?->format('Y-m-d H:i') ?? '—' }}
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if($isHost)
            <span class="badge bg-success"><i class="fa fa-star me-1"></i>你是主持人</span>
            <form method="POST" action="{{ route('pm.project.voice.end', $project) }}" onsubmit="return confirm('确认结束会议？将清理未消费信令与在会记录。')">
                @csrf
                <button class="btn btn-outline-danger btn-sm"><i class="fa fa-stop me-1"></i>结束会议</button>
            </form>
        @else
            <span class="badge bg-secondary">成员</span>
        @endif
        <a href="{{ route('pm.project.show', $project) }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-1"></i>返回项目</a>
    </div>
</div>

<div class="voice-shell" style="min-height: calc(100vh - 260px);">
    <div class="voice-side">
        <div class="px-3 py-2 border-bottom bg-white d-flex justify-content-between align-items-center">
            <span class="small text-muted">项目成员 / 在线状态</span>
            <span class="badge bg-light text-secondary border" id="online-count">0</span>
        </div>
        <div class="flex-grow-1 overflow-y-auto" id="voice-members">
            <div class="text-center py-4 text-muted small">加载中…</div>
        </div>
        <div class="p-2 border-top bg-white">
            <div class="small text-muted mb-1">会议资料（可下载）</div>
            @if($documents->isEmpty())
                <div class="small text-muted">暂无项目文档</div>
            @else
                <div style="max-height:150px;overflow-y:auto;">
                    @foreach($documents as $doc)
                        <div class="d-flex justify-content-between align-items-center py-1" style="font-size:12px;">
                            <span class="text-truncate me-2" title="{{ $doc['name'] }}.{{ $doc['ext'] }}">{{ $doc['name'] }}.{{ $doc['ext'] }}</span>
                            <a href="{{ $doc['download_url'] }}" class="text-decoration-none"><i class="fa fa-download"></i></a>
                        </div>
                    @endforeach
                </div>
            @endif
            <form id="form-upload" class="mt-2 d-flex gap-1" method="POST" action="{{ route('pm.project.voice.upload-demo', $project) }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" id="file-upload" class="form-control form-control-sm" accept=".docx,.xlsx,.pptx,.pdf,.doc,.xls,.ppt">
                <button class="btn btn-outline-secondary btn-sm" type="submit"><i class="fa fa-upload"></i></button>
            </form>
        </div>
    </div>

    <div class="voice-main">
        <div class="d-flex flex-wrap align-items-center gap-2 p-2 border-bottom bg-white">
            <button class="btn btn-primary btn-sm" id="btn-join" type="button"><i class="fa fa-microphone me-1"></i>加入会议</button>
            <button class="btn btn-outline-secondary btn-sm" id="btn-mute" type="button" disabled><i class="fa fa-microphone-slash me-1"></i>静音</button>
            <button class="btn btn-outline-secondary btn-sm" id="btn-leave" type="button" disabled><i class="fa fa-right-from-bracket me-1"></i>离开</button>
            <span class="small text-muted ms-2" id="voice-state">未加入</span>
            <span class="ms-auto small text-muted">信令站内中转（2 秒轮询），无需外部媒体服务器</span>
        </div>

        <div class="voice-stage flex-grow-1">
            <div class="voice-tiles" id="voice-tiles">
                @forelse($members as $m)
                    <div class="voice-tile {{ $m['is_owner'] ? 'owner' : '' }}">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="voice-dot on"></span>
                            <span class="fw-semibold" style="font-size:13px">{{ $m['name'] }}</span>
                        </div>
                        <div class="text-muted" style="font-size:12px">{{ $m['is_owner'] ? '主持人' : '成员' }} · {{ $m['joined_at'] }}</div>
                    </div>
                @empty
                    <div class="text-muted small">还没有人在会里，点击「加入会议」开始。</div>
                @endforelse
            </div>

            <div class="voice-log" id="voice-log" aria-live="polite"></div>
            <div id="voice-audio"></div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><h6 class="mb-0"><i class="fa fa-clock-rotate-left me-2"></i>本项目会议历史</h6></div>
    <div class="card-body">
        @if(empty($history))
            <div class="text-muted small">暂无历史会议。</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>开始</th><th>结束</th><th>状态</th><th>时长</th><th>主持人</th></tr></thead>
                    <tbody>
                        @foreach($history as $row)
                            <tr>
                                <td class="small">{{ $row['started_at'] ?? '—' }}</td>
                                <td class="small">{{ $row['ended_at'] ?? '—' }}</td>
                                <td class="small">{{ $row['status'] === 'open' ? '进行中' : '已结束' }}</td>
                                <td class="small">{{ $row['duration'] }}</td>
                                <td class="small">{{ $row['owner'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var ctx = {
        tokenUrl: @json(route('pm.project.voice.token', $project)),
        signalUrl: @json(route('pm.project.voice.signal', $project)),
        pollUrl: @json(route('pm.project.voice.poll', $project)),
        csrf: '{{ csrf_token() }}'
    };
    var state = { joined: false, muted: false, localStream: null, peers: {}, selfId: null, timer: null };
    var btnJoin = document.getElementById('btn-join');
    var btnMute = document.getElementById('btn-mute');
    var btnLeave = document.getElementById('btn-leave');
    var stateEl = document.getElementById('voice-state');
    var tilesEl = document.getElementById('voice-tiles');
    var logEl = document.getElementById('voice-log');
    var audioEl = document.getElementById('voice-audio');
    var membersEl = document.getElementById('voice-members');
    var onlineEl = document.getElementById('online-count');

    function log(msg) {
        var line = document.createElement('div');
        line.textContent = new Date().toLocaleTimeString() + '  ' + msg;
        logEl.appendChild(line);
        logEl.scrollTop = logEl.scrollHeight;
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': ctx.csrf, 'Accept': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (r) { return r.ok ? r.json() : null; });
    }

    function sendSignal(to, kind, payload) {
        return post(ctx.signalUrl, { to: to, type: kind, payload: payload });
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderMembers(list, selfId) {
        if (membersEl) {
            membersEl.innerHTML = '';
            if (!list || !list.length) {
                membersEl.innerHTML = '<div class="text-center py-4 text-muted small">还没有人在会里</div>';
            } else {
                list.forEach(function (m) {
                    var row = document.createElement('div');
                    row.className = 'voice-member';
                    row.innerHTML = '<span class="voice-dot on"></span>'
                        + '<div class="voice-avatar">' + escapeHtml((m.name || '?').slice(0, 1)) + '</div>'
                        + '<div class="flex-grow-1"><div class="fw-semibold">' + escapeHtml(m.name) + (m.id === selfId ? '（我）' : '') + '</div>'
                        + '<div class="text-muted" style="font-size:11px">' + (m.is_owner ? '主持人' : '成员') + (m.joined_at ? ' · ' + m.joined_at : '') + '</div></div>';
                    membersEl.appendChild(row);
                });
            }
        }
        if (onlineEl) { onlineEl.textContent = (list || []).length; }

        if (tilesEl) {
            tilesEl.innerHTML = '';
            if (!list || !list.length) {
                tilesEl.innerHTML = '<div class="text-muted small">还没有人在会里，点击「加入会议」开始。</div>';
            } else {
                list.forEach(function (m) {
                    var tile = document.createElement('div');
                    tile.className = 'voice-tile' + (m.is_owner ? ' owner' : '');
                    tile.innerHTML = '<div class="d-flex align-items-center gap-2 mb-1"><span class="voice-dot on"></span>'
                        + '<span class="fw-semibold" style="font-size:13px">' + escapeHtml(m.name) + (m.id === selfId ? '（我）' : '') + '</span></div>'
                        + '<div class="text-muted" style="font-size:12px">' + (m.is_owner ? '主持人' : '成员') + (m.joined_at ? ' · ' + m.joined_at : '') + '</div>';
                    tilesEl.appendChild(tile);
                });
            }
        }
    }

    function createPeer(peerId) {
        if (state.peers[peerId]) { return state.peers[peerId]; }
        var pc = new RTCPeerConnection({ iceServers: [] });
        state.peers[peerId] = pc;
        if (state.localStream) {
            state.localStream.getTracks().forEach(function (t) { pc.addTrack(t, state.localStream); });
        }
        pc.onicecandidate = function (e) {
            if (e.candidate) { sendSignal(peerId, 'candidate', e.candidate.toJSON()); }
        };
        pc.ontrack = function (e) {
            var audio = document.getElementById('audio-' + peerId);
            if (!audio) {
                audio = document.createElement('audio');
                audio.id = 'audio-' + peerId;
                audio.autoplay = true;
                audioEl.appendChild(audio);
            }
            audio.srcObject = e.streams[0];
        };
        pc.onconnectionstatechange = function () {
            if (['failed', 'closed', 'disconnected'].indexOf(pc.connectionState) >= 0) {
                log('与 ' + peerId + ' 的连接断开（' + pc.connectionState + '）');
            }
        };
        return pc;
    }

    function closePeer(peerId) {
        var pc = state.peers[peerId];
        if (pc) { try { pc.close(); } catch (e) {} delete state.peers[peerId]; }
        var audio = document.getElementById('audio-' + peerId);
        if (audio) { audio.remove(); }
    }

    function handleSignal(sig) {
        var peerId = sig.from;
        var pc = createPeer(peerId);
        if (sig.type === 'offer') {
            pc.setRemoteDescription(new RTCSessionDescription(sig.payload))
                .then(function () { return pc.createAnswer(); })
                .then(function (answer) { return pc.setLocalDescription(answer); })
                .then(function () { sendSignal(peerId, 'answer', pc.localDescription.toJSON()); })
                .catch(function (e) { log('应答失败：' + e.message); });
        } else if (sig.type === 'answer') {
            pc.setRemoteDescription(new RTCSessionDescription(sig.payload)).catch(function () {});
        } else if (sig.type === 'candidate') {
            pc.addIceCandidate(new RTCIceCandidate(sig.payload)).catch(function () {});
        } else if (sig.type === 'leave') {
            closePeer(peerId);
        }
    }

    function poll() {
        if (document.hidden) { return; }
        fetch(ctx.pollUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) { return; }
                renderMembers(data.members, state.selfId);
                var rs = document.getElementById('room-status');
                if (rs && data.room) { rs.textContent = data.room.status === 'open' ? '进行中' : '已结束'; }
                if (state.joined) { (data.signals || []).forEach(handleSignal); }
            })
            .catch(function () {});
    }

    btnJoin.addEventListener('click', function () {
        if (state.joined) { return; }
        stateEl.textContent = '申请麦克风…';
        navigator.mediaDevices.getUserMedia({ audio: true, video: false })
            .then(function (stream) {
                state.localStream = stream;
                return post(ctx.tokenUrl, {});
            })
            .then(function (token) {
                if (!token) { throw new Error('令牌签发失败，请确认你是项目成员'); }
                state.joined = true;
                state.selfId = token.userId;
                btnJoin.disabled = true;
                btnMute.disabled = false;
                btnLeave.disabled = false;
                stateEl.textContent = '已在会中（' + (token.userName || '') + '）';
                log('加入成功');
                return fetch(ctx.pollUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }).then(function (r) { return r.json(); });
            })
            .then(function (data) {
                renderMembers(data.members, state.selfId);
                (data.members || []).forEach(function (m) {
                    if (m.id === state.selfId) { return; }
                    var pc = createPeer(m.id);
                    pc.createOffer()
                        .then(function (offer) { return pc.setLocalDescription(offer); })
                        .then(function () { sendSignal(m.id, 'offer', pc.localDescription.toJSON()); })
                        .catch(function (e) { log('发起连接失败：' + e.message); });
                });
                (data.signals || []).forEach(handleSignal);
                if (state.timer) { clearInterval(state.timer); }
                state.timer = setInterval(poll, 2000);
            })
            .catch(function (e) {
                stateEl.textContent = '加入失败：' + e.message;
                log('加入失败：' + e.message);
            });
    });

    btnMute.addEventListener('click', function () {
        if (!state.localStream) { return; }
        state.muted = !state.muted;
        state.localStream.getAudioTracks().forEach(function (t) { t.enabled = !state.muted; });
        btnMute.innerHTML = state.muted ? '<i class="fa fa-microphone me-1"></i>取消静音' : '<i class="fa fa-microphone-slash me-1"></i>静音';
        stateEl.textContent = state.muted ? '已静音（仍在会中）' : '已在会中';
    });

    btnLeave.addEventListener('click', function () {
        if (!state.joined) { return; }
        sendSignal(null, 'leave', null);
        if (state.timer) { clearInterval(state.timer); }
        Object.keys(state.peers).forEach(closePeer);
        if (state.localStream) { state.localStream.getTracks().forEach(function (t) { t.stop(); }); }
        state.joined = false;
        state.localStream = null;
        btnJoin.disabled = false;
        btnMute.disabled = true;
        btnLeave.disabled = true;
        btnMute.innerHTML = '<i class="fa fa-microphone-slash me-1"></i>静音';
        stateEl.textContent = '已离开';
        log('已离开会议');
    });

    setInterval(function () { if (!state.joined) { poll(); } }, 4000);
    poll();

    var formUpload = document.getElementById('form-upload');
    if (formUpload) {
        formUpload.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = document.getElementById('file-upload');
            if (!input.files.length) { return; }
            var fd = new FormData(formUpload);
            fetch(formUpload.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': ctx.csrf, 'Accept': 'application/json' },
                body: fd
            }).then(function (r) { return r.json().catch(function () { return null; }); })
              .then(function (data) {
                  if (data && data.id) { log('资料已上传，可下载查看（# ' + data.id + '）'); input.value = ''; }
                  else { log('上传失败：' + ((data && data.error) || '服务端未返回')); }
              });
        });
    }
})();
</script>
@endsection
