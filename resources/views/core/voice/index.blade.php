@extends('core::layouts.base')

@section('title', '语音会议 · ' . config('app.name'))

@push('styles')
<style>
    .dw-voice-grid { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 18px; }
    @media (max-width: 900px) { .dw-voice-grid { grid-template-columns: 1fr; } }
    .dw-voice-stage { background: #fff; border: 1px solid var(--dw-line); border-radius: var(--dw-radius); padding: 18px; }
    .dw-voice-members { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
    .dw-voice-member { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid var(--dw-line); border-radius: 10px; background: #fafbff; }
    .dw-voice-member.is-owner { border-color: #c7d2fe; background: #f5f7ff; }
    .dw-voice-dot { width: 8px; height: 8px; border-radius: 50%; background: #2ecc71; flex: 0 0 8px; }
    .dw-voice-avatar { width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; background: #eef2ff; color: var(--dw-brand); font-weight: 700; }
    .dw-voice-status { font-size: 12px; color: var(--dw-muted); }
    .dw-voice-bar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin: 14px 0; }
    .dw-voice-log { font-size: 12px; color: var(--dw-muted); max-height: 150px; overflow-y: auto; margin-top: 10px; }
</style>
@endpush

@section('content')
<div class="dw-page">
    <header class="dw-page-head">
        <div>
            <p class="dw-eyebrow">三模块共用 · 浏览器内置 WebRTC</p>
            <h1 class="dw-page-title">语音会议</h1>
            <p class="dw-page-desc">
                房间 {{ $room->name }}（#{{ $room->id }}）· 模块 {{ $module }} · 状态
                <span id="room-status">{{ $room->status }}</span> · 开始 {{ $room->started_at?->format('Y-m-d H:i') ?? '—' }}
            </p>
        </div>
        <div class="dw-page-actions">
            <a class="dw-btn" href="{{ route('core.chat.index', ['module' => $module]) }}">← 回聊天</a>
        </div>
    </header>

    @if (session('status'))
        <section class="dw-card dw-alert dw-alert-ok" role="status"><p class="dw-hint">{{ session('status') }}</p></section>
    @endif

    <div class="dw-voice-grid">
        <section class="dw-voice-stage">
            <div class="dw-voice-bar">
                <button class="btn btn-primary btn-sm" id="btn-join" type="button">加入会议</button>
                <button class="btn btn-outline-secondary btn-sm" id="btn-mute" type="button" disabled>静音</button>
                <button class="btn btn-outline-secondary btn-sm" id="btn-leave" type="button" disabled>离开</button>
                <form method="POST" action="{{ route('core.voice.end') }}" class="d-inline" id="form-end">
                    @csrf
                    <input type="hidden" name="module" value="{{ $module }}">
                    <input type="hidden" name="room_type" value="{{ $type }}">
                    <input type="hidden" name="room" value="{{ $roomKey }}">
                    <button class="btn btn-outline-danger btn-sm" type="submit">结束会议（主持人）</button>
                </form>
                <span class="dw-voice-status" id="voice-state" role="status" aria-live="polite">未加入</span>
            </div>

            <div class="dw-voice-members" id="voice-members" aria-live="polite">
                @forelse ($members as $member)
                    <div class="dw-voice-member {{ $member['is_owner'] ? 'is-owner' : '' }}">
                        <span class="dw-voice-dot" title="在线"></span>
                        <div class="dw-voice-avatar">{{ mb_substr($member['name'], 0, 1) }}</div>
                        <div>
                            <div class="fw-semibold" style="font-size:13px">{{ $member['name'] }}</div>
                            <div class="dw-voice-status">{{ $member['is_owner'] ? '主持人' : '成员' }} · {{ $member['joined_at'] }}</div>
                        </div>
                    </div>
                @empty
                    <p class="dw-voice-status">还没有人在会里</p>
                @endforelse
            </div>

            <div class="dw-voice-log" id="voice-log" aria-live="polite"></div>
            <div id="voice-audio"></div>
        </section>

        <aside>
            <div class="dw-card">
                <div class="dw-card-body">
                    <p class="fw-semibold mb-2">参会须知</p>
                    <ul class="dw-voice-status ps-3 mb-0">
                        <li>浏览器会单独申请麦克风权限，拒绝后无法发言。</li>
                        <li>信令走站内中转（每 2 秒拉取一次），不依赖外部媒体服务器；内网/断外网可用。</li>
                        <li>建议 2~6 人小会，人越多 CPU/带宽越高。</li>
                        <li>主持人结束会议后，信令与在会记录一并清理。</li>
                    </ul>
                </div>
            </div>

            <div class="dw-card mt-3">
                <div class="dw-card-body">
                    <p class="fw-semibold mb-2">本模块会议历史</p>
                    @if (empty($history))
                        <p class="dw-voice-status">暂无历史</p>
                    @else
                        <table class="table table-sm mb-0" style="font-size:12px">
                            <thead><tr><th>开始</th><th>状态</th><th>时长</th></tr></thead>
                            <tbody>
                            @foreach ($history as $row)
                                <tr>
                                    <td>{{ $row['started_at'] ?? '—' }}</td>
                                    <td>{{ $row['status'] === 'open' ? '进行中' : '已结束' }}</td>
                                    <td>{{ $row['duration'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var ctx = {
        module: @json($module),
        room_type: @json($type),
        room: @json($roomKey),
        tokenUrl: @json(route('core.voice.token')),
        signalUrl: @json(route('core.voice.signal')),
        pollUrl: @json(route('core.voice.poll')),
        csrf: '{{ csrf_token() }}'
    };
    var state = { joined: false, muted: false, localStream: null, peers: {}, selfId: null, timer: null };
    var btnJoin = document.getElementById('btn-join');
    var btnMute = document.getElementById('btn-mute');
    var btnLeave = document.getElementById('btn-leave');
    var stateEl = document.getElementById('voice-state');
    var membersEl = document.getElementById('voice-members');
    var logEl = document.getElementById('voice-log');
    var audioEl = document.getElementById('voice-audio');

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

    // 发信令：to=null 表示广播给房内其他人
    function sendSignal(to, kind, payload) {
        return post(ctx.signalUrl, {
            module: ctx.module,
            room_type: ctx.room_type,
            room: ctx.room,
            to: to,
            type: kind,
            payload: payload
        });
    }

    function renderMembers(list, selfId) {
        membersEl.innerHTML = '';
        if (!list || !list.length) {
            var empty = document.createElement('p');
            empty.className = 'dw-voice-status';
            empty.textContent = '还没有人在会里';
            membersEl.appendChild(empty);
            return;
        }
        list.forEach(function (m) {
            var div = document.createElement('div');
            div.className = 'dw-voice-member' + (m.is_owner ? ' is-owner' : '');
            var dot = document.createElement('span'); dot.className = 'dw-voice-dot';
            var av = document.createElement('div'); av.className = 'dw-voice-avatar'; av.textContent = (m.name || '?').slice(0, 1);
            var box = document.createElement('div');
            var name = document.createElement('div'); name.className = 'fw-semibold'; name.style.fontSize = '13px';
            name.textContent = m.name + (m.id === selfId ? '（我）' : '');
            var sub = document.createElement('div'); sub.className = 'dw-voice-status';
            sub.textContent = (m.is_owner ? '主持人' : '成员') + (m.joined_at ? ' · ' + m.joined_at : '');
            box.appendChild(name); box.appendChild(sub);
            div.appendChild(dot); div.appendChild(av); div.appendChild(box);
            membersEl.appendChild(div);
        });
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

    function closePeer(peerId) {
        var pc = state.peers[peerId];
        if (pc) { try { pc.close(); } catch (e) {} delete state.peers[peerId]; }
        var audio = document.getElementById('audio-' + peerId);
        if (audio) { audio.remove(); }
    }

    function poll() {
        if (!state.joined || document.hidden) { return; }
        fetch(ctx.pollUrl + '?module=' + encodeURIComponent(ctx.module) + '&room_type=' + encodeURIComponent(ctx.room_type) + '&room=' + encodeURIComponent(ctx.room),
              { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) { return; }
                renderMembers(data.members, state.selfId);
                document.getElementById('room-status').textContent = data.room && data.room.status ? data.room.status : '';
                (data.signals || []).forEach(handleSignal);
            })
            .catch(function () {});
    }

    btnJoin.addEventListener('click', function () {
        if (state.joined) { return; }
        stateEl.textContent = '申请麦克风…';
        navigator.mediaDevices.getUserMedia({ audio: true, video: false })
            .then(function (stream) {
                state.localStream = stream;
                return post(ctx.tokenUrl, { module: ctx.module, room_type: ctx.room_type, room: ctx.room });
            })
            .then(function (token) {
                if (!token) { throw new Error('令牌签发失败'); }
                state.joined = true;
                state.selfId = token.userId;
                btnJoin.disabled = true;
                btnMute.disabled = false;
                btnLeave.disabled = false;
                stateEl.textContent = '已在会中（' + token.userName + '）';
                log('加入成功，房间 ' + token.room);
                // 先开头：给房间内现有成员各发一个 offer
                return fetch(ctx.pollUrl + '?module=' + encodeURIComponent(ctx.module) + '&room_type=' + encodeURIComponent(ctx.room_type) + '&room=' + encodeURIComponent(ctx.room),
                             { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); });
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
        btnMute.textContent = state.muted ? '取消静音' : '静音';
        stateEl.textContent = state.muted ? '已静音' : '已在会中';
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
        btnMute.textContent = '静音';
        stateEl.textContent = '已离开';
        log('已离开会议');
    });

    window.addEventListener('beforeunload', function () {
        if (state.joined) { sendSignal(null, 'leave', null); }
    });
})();
</script>
@endpush
