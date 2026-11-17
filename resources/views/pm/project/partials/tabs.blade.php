@php
    $tabs = [
        ['key' => 'overview', 'label' => '项目概览', 'icon' => 'fa-tachometer', 'route' => route('pm.project.show', $project)],
        ['key' => 'gantt', 'label' => '甘特图', 'icon' => 'fa-chart-bar', 'route' => route('pm.project.gantt', $project)],
        ['key' => 'history', 'label' => '项目履历', 'icon' => 'fa-history', 'route' => route('pm.project.history', $project)],
        ['key' => 'logs', 'label' => '项目日志', 'icon' => 'fa-list-alt', 'route' => route('pm.project.logs', $project)],
        ['key' => 'collab', 'label' => '协同办公', 'icon' => 'fa-users', 'route' => route('pm.project.collab.index', $project)],
        ['key' => 'chat', 'label' => '聊天室', 'icon' => 'fa-comments', 'route' => route('pm.project.messages.index', $project->id)],
    ];
    if ($project->isMember(auth()->user())) {
        $tabs[] = ['key' => 'voice', 'label' => '电话会议', 'icon' => 'fa-phone', 'route' => route('pm.project.voice.index', $project)];
    }
    $active = $activeTab ?? 'overview';
@endphp
<div class="pm-project-head">
    <h4 class="pm-project-title"><i class="fas fa-folder-open" aria-hidden="true"></i>{{ $project->name }}</h4>
    <div class="pm-project-meta">
        项目编码：{{ $project->code }} · 状态：
        <span class="pm-status" style="background:{{ $project->statusColor() }}">{{ $project->statusLabel() }}</span>
    </div>
</div>
<ul class="pm-tabs" role="tablist">
    @foreach ($tabs as $tab)
        <li>
            <a class="pm-tab {{ $active === $tab['key'] ? 'active' : '' }}" href="{{ $tab['route'] }}"
               @if ($active === $tab['key']) aria-current="page" @endif>
                <i class="fas {{ $tab['icon'] }}" aria-hidden="true"></i>{{ $tab['label'] }}
            </a>
        </li>
    @endforeach
</ul>
