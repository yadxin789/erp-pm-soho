@extends('core::layouts.base')

@section('title', '站内通知 · '.config('app.name'))

@section('content')
<div class="dw-page">
    <div class="dw-panel">
        <div class="dw-panel-head">
            <div class="left">
                <span class="ic"><i class="fas fa-bell" aria-hidden="true"></i></span>
                <h2>站内通知 <span class="sub">共 {{ $totalCount }} 条 · 未读 {{ $counts['total'] }} 条</span></h2>
            </div>
            <div class="tools">
                @if ($counts['total'] > 0)
                    <form method="POST" action="{{ route('core.notifications.read-all') }}" class="dw-inline-form">
                        @csrf
                        @if ($module) <input type="hidden" name="module" value="{{ $module }}"> @endif
                        <button type="submit" class="dw-btn"><i class="fas fa-check" aria-hidden="true"></i> 全部标为已读</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="dw-panel-body" style="border-bottom:1px solid var(--dw-line);padding:12px 18px">
            <div class="dw-toolbar">
                <a class="dw-chip {{ $module === null ? 'is-current' : '' }}"
                   href="{{ route('core.notifications.index', array_filter(['status' => $status])) }}">
                    全部 <span class="n">{{ $totalCount }}</span>
                </a>
                @foreach (['core' => '核心', 'erp' => 'ERP', 'pm' => 'PM', 'soho' => 'SOHO'] as $key => $label)
                    <a class="dw-chip {{ $module === $key ? 'is-current' : '' }}"
                       href="{{ route('core.notifications.index', array_filter(['module' => $key, 'status' => $status])) }}">
                        {{ $label }} <span class="n">{{ $byModule[$key] ?? 0 }}</span>
                    </a>
                @endforeach
                <span style="width:1px;height:18px;background:var(--dw-line)"></span>
                @php $moduleQuery = $module ? ['module' => $module] : []; @endphp
                <a class="dw-chip {{ $status === 'unread' ? 'is-current' : '' }}"
                   href="{{ route('core.notifications.index', $moduleQuery + ['status' => 'unread']) }}">未读</a>
                <a class="dw-chip {{ $status === 'read' ? 'is-current' : '' }}"
                   href="{{ route('core.notifications.index', $moduleQuery + ['status' => 'read']) }}">已读</a>
            </div>
        </div>

        @if (session('status'))
            <div class="dw-panel-body" style="padding:10px 18px;background:#f2fbf5;border-bottom:1px solid #d8f0e0">
                <span class="dw-badge dw-badge--ok"><i class="fas fa-check" aria-hidden="true"></i> {{ session('status') }}</span>
            </div>
        @endif

        @if ($items->isEmpty())
            <div class="dw-empty-panel">
                <i class="far fa-bell" aria-hidden="true"></i>
                <p class="t">这个筛选条件下没有通知</p>
                <p class="mb-0">换个模块或切回“全部”看看。</p>
            </div>
        @else
            <ul class="dw-notice-list list-unstyled mb-0">
                @foreach ($items as $item)
                    <li class="dw-notice {{ $item->is_read ? 'is-read' : 'is-unread' }}">
                        <span class="dw-notice-ico {{ $item->is_read ? 'core' : 'core is-unread' }}">
                            @switch($item->module)
                                @case('erp') <i class="fas fa-industry" aria-hidden="true"></i> @break
                                @case('pm') <i class="fas fa-cubes" aria-hidden="true"></i> @break
                                @case('soho') <i class="fas fa-shopping-cart" aria-hidden="true"></i> @break
                                @default <i class="far fa-bell" aria-hidden="true"></i>
                            @endswitch
                        </span>
                        <div class="dw-notice-main">
                            <div class="dw-notice-head">
                                <span class="dw-badge dw-badge--muted">{{ $item->module ?: 'core' }}</span>
                                <strong>{{ $item->title }}</strong>
                                @if ($item->priority !== 'normal')
                                    <span class="dw-badge dw-badge--warn">{{ $item->priority }}</span>
                                @endif
                                @if (! $item->is_read)
                                    <span class="dw-badge dw-badge--info">未读</span>
                                @endif
                                <time class="dw-hint ms-auto" datetime="{{ $item->created_at?->toIso8601String() }}">
                                    {{ $item->created_at?->format('Y-m-d H:i') }}
                                </time>
                            </div>
                            @if ($item->content)
                                <p class="dw-notice-body mb-0">{{ $item->content }}</p>
                            @endif
                            @if (! empty($item->data['summary']))
                                <p class="dw-hint mb-0">{{ $item->data['summary'] }}</p>
                            @endif
                        </div>
                        <div class="dw-notice-actions">
                            @if (! $item->is_read)
                                <form method="POST" action="{{ route('core.notifications.read', $item->id) }}" class="dw-inline-form">
                                    @csrf
                                    <button type="submit" class="dw-btn">标为已读</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('core.notifications.destroy', $item->id) }}" class="dw-inline-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dw-btn dw-btn-danger">删除</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="dw-panel-foot dw-pagination">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
