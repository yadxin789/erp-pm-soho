@extends('pm::layouts.app')

@section('title', '电话会议 · '.$project->name)

@section('content')
@include('pm::project.partials.tabs', ['project' => $project, 'activeTab' => 'voice'])

<div class="pm-panel">
    <div class="pm-panel-head">
        <h3><i class="fas fa-phone" aria-hidden="true"></i>项目电话会议</h3>
        <a class="btn btn-sm btn-outline-primary"
           href="{{ route('core.voice.index', ['module' => 'pm', 'room_type' => 'project', 'room' => $project->id]) }}">
            进入语音房
        </a>
    </div>
    <div class="pm-panel-body">
        <div class="pm-voice-stage">
            @forelse ($members as $member)
                <div class="pm-voice-tile">
                    <div class="ava">{{ mb_substr($member['name'] ?? '?', 0, 1) }}</div>
                    {{ $member['name'] ?? '成员' }}
                    <small>{{ ($member['role'] ?? 'member') === 'host' ? '主持人' : '参会' }}{{ ! empty($member['online']) ? ' · 在线' : ' · 未加入' }}</small>
                </div>
            @empty
                <div class="pm-empty" style="grid-column:1/-1">
                    <i class="fas fa-phone" aria-hidden="true"></i>
                    目前没有成员在会议中，点击右上角「进入语音房」发起。
                </div>
            @endforelse
        </div>

        <div class="pm-voice-actions">
            <a class="pm-btn-primary" href="{{ route('core.voice.index', ['module' => 'pm', 'room_type' => 'project', 'room' => $project->id]) }}">
                加入项目会议
            </a>
            <span class="text-muted small align-self-center">房间：project #{{ $project->id }} · 仅本项目成员可见</span>
        </div>

        @if (! empty($history))
            <div class="mt-4">
                <h4 class="h6">最近会议记录</h4>
                <ul class="list-unstyled small text-muted mb-0">
                    @foreach (array_slice($history, 0, 6) as $row)
                        <li>{{ $row['created_at'] ?? '' }} · {{ $row['title'] ?? '项目会议' }}（{{ $row['status'] ?? '已结束' }}）</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection
