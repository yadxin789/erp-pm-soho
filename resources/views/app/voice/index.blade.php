@extends('layouts.business')

@section('title', $project->name . ' · 电话会议')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="page-title mb-1"><i class="fa-solid fa-phone-volume"></i>电话会议</h2>
            <div class="small text-muted">
                {{ $project->name }} · 房间 {{ $room }}
                · 开始于 {{ optional($call->started_at)->format('m-d H:i') }}
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ erp_route('app.projects.voice.end', $project) }}" onsubmit="return confirm('结束本次会议？')">
                @csrf
                <button class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-phone-slash me-1"></i>结束会议</button>
            </form>
            <a href="{{ erp_route('app.projects.messages.index', $project) }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-comments me-1"></i>聊天室</a>
            <a href="{{ erp_route('app.projects.collab.index', $project) }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-pen me-1"></i>协同办公</a>
            <a href="{{ erp_route('app.projects.show', $project) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>项目详情</a>
        </div>
    </div>

    <div id="voice-app"
         data-token="{{ erp_route('app.projects.voice.token', $project) }}"
         data-poll="{{ erp_route('app.api.voice.poll', $project) }}"
         data-signal="{{ erp_route('app.api.voice.signal', $project) }}"
         data-me="{{ auth()->id() }}"
         data-me-name="{{ auth()->user()->name }}">

        <div class="row g-3">
            <div class="col-lg-3">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-users me-1"></i>在会成员</span>
                        <span class="badge bg-success-subtle text-success-emphasis" id="online-count">0 人在线</span>
                    </div>
                    <div class="list-group list-group-flush" id="member-list">
                        <div class="list-group-item small text-muted">点击下方“加入会议”后显示</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-sliders me-1"></i>会议控制</div>
                    <div class="card-body d-grid gap-2">
                        <button class="btn btn-success" id="btn-join"><i class="fa-solid fa-phone me-1"></i>加入会议</button>
                        <button class="btn btn-outline-danger d-none" id="btn-leave"><i class="fa-solid fa-phone-slash me-1"></i>离开会议</button>
                        <button class="btn btn-outline-secondary d-none" id="btn-mute"><i class="fa-solid fa-microphone me-1"></i>静音麦克风</button>
                        <div class="small text-muted" id="voice-status">未加入</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header"><i class="fa-solid fa-headphones me-1"></i>音频</div>
                    <div class="card-body">
                        <div id="remote-audio" class="d-grid gap-2"></div>
                        <div class="text-center text-muted py-5" id="audio-placeholder">
                            <i class="fa-solid fa-volume-high fa-2x mb-2"></i>
                            <p class="mb-0">加入会议后，这里显示其他成员的音频</p>
                        </div>
                        <div class="alert alert-light border small mt-3 mb-0">
                            音频在浏览器之间点对点直连，信令走本机应用内中转，不依赖任何外网服务；
                            断网（本机或同一内网）环境同样可用。
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><i class="fa-solid fa-chalkboard me-1"></i>文稿演示</div>
                    <div class="card-body">
                        <label class="form-label small">选择项目协作文档</label>
                        <select class="form-select mb-2" id="demo-doc">
                            <option value="">— 选择文档 —</option>
                            @foreach($documents as $document)
                                <option value="{{ erp_route('app.projects.collab.open', [$project, $document]) }}">{{ $document->title }}</option>
                            @endforeach
                        </select>
                        <form method="POST" action="{{ erp_route('app.projects.voice.upload-demo', $project) }}" enctype="multipart/form-data" class="mb-2">
                            @csrf
                            <input type="file" name="file" class="form-control mb-1" required>
                            <button class="btn btn-outline-primary btn-sm w-100"><i class="fa-solid fa-upload me-1"></i>上传演示文件</button>
                        </form>
                        @if($call->demo_file)
                            <a href="{{ erp_route('app.projects.voice.demo', $project) }}" target="_blank" class="btn btn-outline-secondary btn-sm w-100 mb-2">
                                <i class="fa-solid fa-up-right-from-square me-1"></i>打开已上传的演示文件
                            </a>
                        @endif
                        <div class="border rounded" style="height:240px;overflow:hidden;background:#f8f9fa;">
                            <iframe id="demo-frame" src="" style="width:100%;height:100%;border:0;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header"><i class="fa-solid fa-clock-rotate-left me-1"></i>会议历史</div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>开始</th><th>结束</th><th>时长</th><th>发起人</th><th>参与人</th></tr></thead>
                <tbody>
                @forelse($history as $row)
                    <tr>
                        <td class="small">{{ optional($row->started_at)->format('Y-m-d H:i') }}</td>
                        <td class="small">{{ optional($row->ended_at)->format('Y-m-d H:i') ?: '进行中' }}</td>
                        <td class="small">{{ $row->duration_label }}</td>
                        <td class="small">{{ $row->starter->name ?? '—' }}</td>
                        <td class="small">{{ implode('、', $row->participants ?? []) ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-3">暂无会议记录</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            var root = document.getElementById('voice-app');
            var tokenUrl = root.dataset.token;
            var pollUrl = root.dataset.poll;
            var signalUrl = root.dataset.signal;
            var me = parseInt(root.dataset.me, 10);
            var myName = root.dataset.meName;
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            var peers = {};
            var localStream = null;
            var joined = false;
            var pollTimer = null;
            var tokenData = null;

            var btnJoin = document.getElementById('btn-join');
            var btnLeave = document.getElementById('btn-leave');
            var btnMute = document.getElementById('btn-mute');
            var statusEl = document.getElementById('voice-status');
            var listEl = document.getElementById('member-list');
            var onlineEl = document.getElementById('online-count');
            var audioWrap = document.getElementById('remote-audio');
            var placeholder = document.getElementById('audio-placeholder');

            function postSignal(to, type, payload) {
                return fetch(signalUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ to: to, type: type, payload: payload })
                });
            }

            function addRemoteAudio(uid) {
                var id = 'audio-' + uid;
                if (document.getElementById(id)) { return; }
                var wrap = document.createElement('div');
                wrap.className = 'd-flex align-items-center gap-2 border rounded p-2';
                wrap.innerHTML = '<i class="fa-solid fa-circle-user text-muted"></i><span class="small flex-grow-1">成员 ' + uid + '</span>';
                var audio = document.createElement('audio');
                audio.id = id;
                audio.autoplay = true;
                audio.controls = true;
                audio.style.height = '32px';
                wrap.appendChild(audio);
                audioWrap.appendChild(wrap);
                if (placeholder) { placeholder.classList.add('d-none'); }
            }

            function createPeer(uid) {
                if (peers[uid]) { return peers[uid]; }
                var pc = new RTCPeerConnection({ iceServers: tokenData.iceServers || [] });
                if (localStream) {
                    localStream.getTracks().forEach(function (t) { pc.addTrack(t, localStream); });
                }
                pc.onicecandidate = function (e) {
                    if (e.candidate) { postSignal(uid, 'candidate', e.candidate); }
                };
                pc.ontrack = function (e) {
                    addRemoteAudio(uid);
                    var el = document.getElementById('audio-' + uid);
                    if (el) { el.srcObject = e.streams[0]; }
                };
                pc.onconnectionstatechange = function () {
                    if (['failed', 'closed'].indexOf(pc.connectionState) >= 0) {
                        delete peers[uid];
                    }
                };
                peers[uid] = pc;
                return pc;
            }

            async function offerTo(uid) {
                var pc = createPeer(uid);
                var offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                postSignal(uid, 'offer', pc.localDescription);
            }

            async function handleSignal(sig) {
                var from = sig.from;
                var pc = peers[from] || createPeer(from);

                if (sig.type === 'offer') {
                    await pc.setRemoteDescription(new RTCSessionDescription(sig.payload));
                    var answer = await pc.createAnswer();
                    await pc.setLocalDescription(answer);
                    postSignal(from, 'answer', pc.localDescription);
                } else if (sig.type === 'answer') {
                    if (pc.signalingState !== 'stable') {
                        await pc.setRemoteDescription(new RTCSessionDescription(sig.payload));
                    }
                } else if (sig.type === 'candidate') {
                    try { await pc.addIceCandidate(new RTCIceCandidate(sig.payload)); } catch (e) { /* 忽略过期候选 */ }
                } else if (sig.type === 'leave') {
                    if (peers[from]) { peers[from].close(); delete peers[from]; }
                    var el = document.getElementById('audio-' + from);
                    if (el && el.parentElement) { el.parentElement.remove(); }
                }
            }

            function renderMembers(members) {
                onlineEl.textContent = members.length + ' 人在线';
                if (!members.length) {
                    listEl.innerHTML = '<div class="list-group-item small text-muted">暂无其他成员，等待参与人加入</div>';
                    return;
                }
                listEl.innerHTML = members.map(function (m) {
                    return '<div class="list-group-item d-flex justify-content-between align-items-center">'
                        + '<span class="small"><i class="fa-solid fa-circle text-success me-1"></i>' + m.name + '</span>'
                        + '<span class="badge bg-success-subtle text-success-emphasis">在线</span></div>';
                }).join('');
            }

            async function poll() {
                try {
                    var data = await fetch(pollUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); });
                    renderMembers(data.members || []);
                    for (var i = 0; i < (data.signals || []).length; i++) {
                        await handleSignal(data.signals[i]);
                    }
                    // 新成员：由 id 较小的一方发起 offer，避免双向同时协商
                    (data.members || []).forEach(function (m) {
                        if (m.id !== me && !peers[m.id] && me < m.id) {
                            offerTo(m.id);
                        }
                    });
                } catch (e) { /* 网络抖动忽略 */ }
            }

            async function join() {
                btnJoin.disabled = true;
                statusEl.textContent = '正在获取麦克风…';
                try {
                    localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                } catch (e) {
                    localStream = new MediaStream();
                    statusEl.textContent = '未获取到麦克风，可旁听';
                }
                tokenData = await fetch(tokenUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.json(); });
                joined = true;
                btnLeave.classList.remove('d-none');
                btnMute.classList.remove('d-none');
                btnJoin.classList.add('d-none');
                statusEl.textContent = '已加入会议 · ' + tokenData.room;
                pollTimer = setInterval(poll, 900);
                poll();
            }

            function leave() {
                Object.keys(peers).forEach(function (uid) {
                    postSignal(parseInt(uid, 10), 'leave', null);
                    peers[uid].close();
                });
                peers = {};
                if (localStream) { localStream.getTracks().forEach(function (t) { t.stop(); }); }
                clearInterval(pollTimer);
                joined = false;
                btnJoin.classList.remove('d-none');
                btnJoin.disabled = false;
                btnLeave.classList.add('d-none');
                btnMute.classList.add('d-none');
                statusEl.textContent = '已离开会议';
                audioWrap.innerHTML = '';
                if (placeholder) { placeholder.classList.remove('d-none'); }
            }

            btnJoin.addEventListener('click', join);
            btnLeave.addEventListener('click', leave);
            btnMute.addEventListener('click', function () {
                if (!localStream) { return; }
                var track = localStream.getAudioTracks()[0];
                if (!track) { return; }
                track.enabled = !track.enabled;
                btnMute.innerHTML = track.enabled
                    ? '<i class="fa-solid fa-microphone me-1"></i>静音麦克风'
                    : '<i class="fa-solid fa-microphone-slash me-1"></i>取消静音';
            });

            document.getElementById('demo-doc').addEventListener('change', function () {
                document.getElementById('demo-frame').src = this.value;
            });

            window.addEventListener('beforeunload', function () {
                if (joined) { Object.keys(peers).forEach(function (uid) { postSignal(parseInt(uid, 10), 'leave', null); }); }
            });
        })();
    </script>
@endsection
