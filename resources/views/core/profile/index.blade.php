@extends('core::layouts.base')

@section('title', '个人中心 · ' . config('app.name'))

@push('styles')
<style>
    .dw-profile-tabs .nav-link { color: var(--dw-muted); font-weight: 600; }
    .dw-profile-tabs .nav-link.active { color: var(--dw-brand); }
    .dw-kv { display: grid; grid-template-columns: 7.5em minmax(0, 1fr); gap: 8px 16px; font-size: 13px; }
    .dw-kv dt { color: var(--dw-muted); font-weight: 600; margin: 0; }
    .dw-kv dd { margin: 0; word-break: break-all; }
</style>
@endpush

@section('content')
<div class="dw-panel">
    <div class="dw-panel-head">
        <div class="left">
            <span class="ic"><i class="fas fa-id-card" aria-hidden="true"></i></span>
            <h2>个人中心 <span class="sub">三模块共用一套账号资料；修改后立即对 ERP / PM / SOHO 同时生效</span></h2>
        </div>
        <div class="tools">
            <span class="dw-badge dw-badge--muted">{{ strtoupper(auth()->user()->module ?? 'core') }}</span>
        </div>
    </div>
</div>

@if (session('status'))
    <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="dw-card">
    <ul class="nav nav-tabs dw-profile-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-basic" data-bs-toggle="tab" data-bs-target="#pane-basic"
                    type="button" role="tab" aria-controls="pane-basic" aria-selected="true">基本信息</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-password" data-bs-toggle="tab" data-bs-target="#pane-password"
                    type="button" role="tab" aria-controls="pane-password" aria-selected="false">修改密码</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-notify" data-bs-toggle="tab" data-bs-target="#pane-notify"
                    type="button" role="tab" aria-controls="pane-notify" aria-selected="false">通知偏好</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-sessions" data-bs-toggle="tab" data-bs-target="#pane-sessions"
                    type="button" role="tab" aria-controls="pane-sessions" aria-selected="false">会话与设备</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-ai" data-bs-toggle="tab" data-bs-target="#pane-ai"
                    type="button" role="tab" aria-controls="pane-ai" aria-selected="false">AI 设置</button>
        </li>
    </ul>

    <div class="tab-content pt-3">
        {{-- 基本信息 --}}
        <div class="tab-pane fade show active" id="pane-basic" role="tabpanel" aria-labelledby="tab-basic">
            <div class="row g-4">
                <div class="col-lg-7">
                    <form method="POST" action="/profile/basic" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="dw-field">
                                    <label for="name">显示名 <span class="req" aria-hidden="true">*</span></label>
                                    <input class="dw-input @error('name') is-invalid @enderror" id="name" name="name"
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name') <span class="dw-error" style="display:block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="dw-field">
                                    <label for="real_name">真实姓名</label>
                                    <input class="dw-input" id="real_name" name="real_name" value="{{ old('real_name', $user->real_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="dw-field">
                                    <label for="email">邮箱</label>
                                    <input class="dw-input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                                           autocomplete="email">
                                    <span class="dw-help">用于登录与通知；可为空（纯用户名账号）。</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="dw-field">
                                    <label for="phone">手机号</label>
                                    <input class="dw-input" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                           autocomplete="tel">
                                    <span class="dw-help">仅作联系方式，不参与登录（D10）。</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="dw-field">
                                    <label for="signature">签名</label>
                                    <textarea class="dw-textarea" id="signature" name="signature" rows="2">{{ old('signature', $user->signature) }}</textarea>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit">保存基本信息</button>
                    </form>
                </div>

                <div class="col-lg-5">
                    <div class="dw-card" style="background:#fafbff">
                        <h2 class="dw-card-title">组织归属（只读）</h2>
                        <dl class="dw-kv">
                            <dt>登录账号</dt><dd>{{ $user->username ?: '—' }}</dd>
                            <dt>部门</dt><dd>{{ $user->department->name ?? '未分配' }}</dd>
                            <dt>岗位</dt><dd>{{ $user->position->name ?? '未设置' }}</dd>
                            <dt>可进入模块</dt><dd>{{ $modules ? implode('、', array_map('strtoupper', $modules)) : '（暂未分配）' }}</dd>
                            <dt>账号状态</dt><dd>{{ (int) $user->status === 1 ? '正常' : '停用' }}</dd>
                            <dt>上次登录</dt><dd>{{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }} {{ $user->last_login_ip ? '('.$user->last_login_ip.')' : '' }}</dd>
                        </dl>
                        <p class="text-muted small mt-3 mb-0">部门与岗位由管理员维护，如需调整请联系管理员。</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 修改密码 --}}
        <div class="tab-pane fade" id="pane-password" role="tabpanel" aria-labelledby="tab-password">
            <form method="POST" action="/profile/password" class="col-lg-6" novalidate>
                @csrf
                @method('PUT')
                <div class="dw-field">
                    <label for="current_password">当前密码 <span class="req" aria-hidden="true">*</span></label>
                    <input class="dw-input" id="current_password" name="current_password" type="password"
                           autocomplete="current-password" required>
                </div>
                <div class="dw-field">
                    <label for="password">新密码 <span class="req" aria-hidden="true">*</span></label>
                    <input class="dw-input" id="password" name="password" type="password" autocomplete="new-password" required>
                    <span class="dw-help">至少 8 位；改密后其他设备需重新登录。</span>
                </div>
                <div class="dw-field">
                    <label for="password_confirmation">确认新密码 <span class="req" aria-hidden="true">*</span></label>
                    <input class="dw-input" id="password_confirmation" name="password_confirmation" type="password"
                           autocomplete="new-password" required>
                </div>
                <button class="btn btn-primary" type="submit">更新密码</button>
            </form>
        </div>

        {{-- 通知偏好 --}}
        <div class="tab-pane fade" id="pane-notify" role="tabpanel" aria-labelledby="tab-notify">
            <form method="POST" action="/profile/notifications" class="col-lg-7" novalidate>
                @csrf
                @method('PUT')
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="wecom_notify_enabled" name="wecom_notify_enabled" value="1"
                           @checked($user->wecom_notify_enabled)>
                    <label class="form-check-label" for="wecom_notify_enabled">启用企业微信通知</label>
                </div>
                <p class="dw-help mb-3">
                    @if ($user->wecom_userid)
                        已绑定企业微信账号：{{ $user->wecom_userid }}（绑定时间 {{ $user->wecom_bound_at?->format('Y-m-d') }}）
                    @else
                        当前账号未绑定企业微信，绑定入口沿用 PM 原功能，后续统一到此处。
                    @endif
                </p>

                <fieldset class="mb-3">
                    <legend class="h6">通知类型</legend>
                    @php $settings = (array) ($user->wecom_notify_settings ?? []); @endphp
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notify_approval" name="notify_approval" value="1"
                               @checked($settings['approval'] ?? true)>
                        <label class="form-check-label" for="notify_approval">审批流转（待办 / 通过 / 驳回）</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notify_task" name="notify_task" value="1"
                               @checked($settings['task'] ?? true)>
                        <label class="form-check-label" for="notify_task">任务与项目提醒</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notify_chat" name="notify_chat" value="1"
                               @checked($settings['chat'] ?? false)>
                        <label class="form-check-label" for="notify_chat">聊天 @ 提醒</label>
                    </div>
                </fieldset>

                <button class="btn btn-primary" type="submit">保存通知偏好</button>
            </form>
        </div>

        {{-- 会话与设备 --}}
        <div class="tab-pane fade" id="pane-sessions" role="tabpanel" aria-labelledby="tab-sessions">
            @if ($sessions)
                <table class="table table-sm align-middle mb-3">
                    <thead>
                        <tr><th scope="col">设备</th><th scope="col">IP</th><th scope="col">最近活动</th><th scope="col">状态</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $s)
                            <tr>
                                <td>{{ $s['agent'] }}</td>
                                <td>{{ $s['ip'] }}</td>
                                <td>{{ $s['last_active'] }}</td>
                                <td>
                                    @if ($s['is_current'])
                                        <span class="dw-badge dw-badge--ok">当前会话</span>
                                    @else
                                        <span class="dw-badge dw-badge--muted">其他设备</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <form method="POST" action="/profile/sessions">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" type="submit">退出其他所有设备</button>
                </form>
            @else
                <p class="text-muted mb-0">当前会话驱动（{{ config('session.driver') }}）不提供设备列表。</p>
            @endif
        </div>

        {{-- AI 设置 --}}
        <div class="tab-pane fade" id="pane-ai" role="tabpanel" aria-labelledby="tab-ai">
            <div class="dw-card" style="background:#fafbff">
                <h2 class="dw-card-title">AI 能力设置（待迁入）</h2>
                <p class="text-muted mb-2">
                    这里将收纳 ERP 的 AI 设置页（模型 / API Key / 工具开关）与 PM 的 AI 工作台偏好。
                    当前 erp 的 `ai-settings/*` 与 pm 的 AI 工作台仍在各自模块内运行，后续 AI 能力收口时统一迁到此处，功能不删。
                </p>
                <p class="text-muted mb-0">在此之前，请分别前往 ERP「AI 设置」与 PM「AI 工作台」调整。</p>
            </div>
        </div>
    </div>
</div>

<p class="mt-3 mb-0"><a href="/">← 返回门户</a></p>
@endsection
