@extends('core::layouts.base')

@section('title', '站内通知 · '.config('app.name'))

@section('content')
@include('core::partials.topbar')
<div class="dw-page">
    <header class="dw-page-head">
        <div>
            <p class="dw-eyebrow">三模块共用 · 一处查阅</p>
            <h1 class="dw-page-title">站内通知</h1>
            <p class="dw-page-desc">
                共 {{ $totalCount }} 条 · 未读 {{ $counts['total'] }} 条（core {{ $counts['core'] }} / erp {{ $counts['erp'] }} / pm {{ $counts['pm'] }} / soho {{ $counts['soho'] }}）
            </p>
        </div>
        <div class="dw-page-actions">
            @if ($counts['total'] > 0)
                <form method="POST" action="{{ route('core.notifications.read-all') }}">
                    @csrf
                    @if ($module) <input type="hidden" name="module" value="{{ $module }}"> @endif
                    <button type="submit" class="dw-btn">全部标为已读</button>
                </form>
            @endif
        </div>
    </header>

    @if (session('status'))
        <section class="dw-card dw-alert dw-alert-ok" role="status">
            <p class="dw-hint">{{ session('status') }}</p>
        </section>
    @endif

    <nav class="dw-page-actions dw-tabs" aria-label="按模块筛选">
        <a class="dw-btn {{ $module === null ? 'is-current' : '' }}" href="{{ route('core.notifications.index', array_filter(['status' => $status])) }}">全部</a>
        @foreach (['core' => '核心', 'erp' => 'ERP', 'pm' => 'PM', 'soho' => 'SOHO'] as $key => $label)
            <a class="dw-btn {{ $module === $key ? 'is-current' : '' }}"
               href="{{ route('core.notifications.index', array_filter(['module' => $key, 'status' => $status])) }}">
                {{ $label }} <span class="dw-badge dw-badge--muted">{{ $byModule[$key] ?? 0 }}</span>
            </a>
        @endforeach

        <span class="dw-tabs-sep"></span>
        @php $moduleQuery = $module ? ['module' => $module] : []; @endphp
        <a class="dw-btn {{ $status === 'unread' ? 'is-current' : '' }}" href="{{ route('core.notifications.index', $moduleQuery + ['status' => 'unread']) }}">未读</a>
        <a class="dw-btn {{ $status === 'read' ? 'is-current' : '' }}" href="{{ route('core.notifications.index', $moduleQuery + ['status' => 'read']) }}">已读</a>
    </nav>

    @if ($items->isEmpty())
        <section class="dw-card dw-empty">
            <p class="title">这个筛选条件下没有通知</p>
            <p>换个模块或切回“全部”看看。</p>
        </section>
    @else
        <ul class="dw-notice-list">
            @foreach ($items as $item)
                <li class="dw-notice {{ $item->is_read ? 'is-read' : 'is-unread' }}">
                    <div class="dw-notice-main">
                        <div class="dw-notice-head">
                            <span class="dw-notice-dot" aria-hidden="true"></span>
                            <span class="dw-badge dw-badge--info">{{ $item->module ?: 'core' }}</span>
                            <strong>{{ $item->title }}</strong>
                            @if ($item->priority !== 'normal')
                                <span class="dw-badge dw-badge--warn">{{ $item->priority }}</span>
                            @endif
                            <time class="dw-hint" datetime="{{ $item->created_at?->toIso8601String() }}">
                                {{ $item->created_at?->format('Y-m-d H:i') }}
                            </time>
                        </div>
                        @if ($item->content)
                            <p class="dw-notice-body">{{ $item->content }}</p>
                        @endif
                        @if (! empty($item->data['summary']))
                            <p class="dw-hint">{{ $item->data['summary'] }}</p>
                        @endif
                    </div>
                    <div class="dw-notice-actions">
                        @if (! $item->is_read)
                            <form method="POST" action="{{ route('core.notifications.read', $item->id) }}">
                                @csrf
                                <button type="submit" class="dw-btn">标为已读</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('core.notifications.destroy', $item->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dw-btn dw-btn-danger">删除</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="dw-pagination">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
