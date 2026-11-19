@extends('core::layouts.base')

@section('title', '我的待办 · '.config('app.name'))

@section('content')
<div class="dw-page">
    <div class="dw-panel">
        <div class="dw-panel-head">
            <div class="left">
                <span class="ic"><i class="fas fa-list-check" aria-hidden="true"></i></span>
                <h2>我的待办
                    <span class="sub">
                        共 {{ $summary['total'] }} 条
                        · Core {{ $summary['counts']['core'] ?? 0 }}
                        · ERP {{ $summary['counts']['erp'] ?? 0 }}
                        · PM {{ $summary['counts']['pm'] ?? 0 }}
                        · SOHO {{ $summary['counts']['soho'] ?? 0 }}
                    </span>
                </h2>
            </div>
            <div class="tools">
                <a class="dw-btn" href="{{ route('core.approvals.index') }}"><i class="fas fa-check-circle" aria-hidden="true"></i> 审批中心</a>
                <a class="dw-btn" href="{{ route('core.notifications.index') }}"><i class="fas fa-bell" aria-hidden="true"></i> 站内通知</a>
            </div>
        </div>

        <div class="dw-panel-body" style="border-bottom:1px solid var(--dw-line);padding:12px 18px">
            <div class="dw-toolbar">
                <a class="dw-chip {{ $module === null ? 'is-current' : '' }}" href="{{ route('core.todos.index') }}">
                    全部 <span class="n">{{ $summary['total'] }}</span>
                </a>
                @foreach($summary['modules'] as $m)
                    @if($m !== 'core')
                        <a class="dw-chip {{ $module === $m ? 'is-current' : '' }}" href="{{ route('core.todos.index', ['module' => $m]) }}">
                            {{ strtoupper($m) }} <span class="n">{{ $summary['counts'][$m] ?? 0 }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        @forelse($groups as $group)
            @if(count($group['items']) > 0)
                <div class="dw-panel-body" style="padding:0">
                    <div style="padding:12px 18px;border-bottom:1px solid var(--dw-line);font-weight:600">
                        {{ $group['label'] }}
                        <span class="sub" style="font-weight:400;color:var(--dw-muted)">· {{ count($group['items']) }} 条</span>
                    </div>
                    <table class="dw-table">
                        <thead>
                            <tr>
                                <th style="width:150px">分类</th>
                                <th>标题</th>
                                <th style="width:280px">说明</th>
                                <th style="width:160px">时间</th>
                                <th style="width:90px">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($group['items'] as $item)
                            <tr>
                                <td><span class="dw-badge">{{ $item['category'] }}</span></td>
                                <td>{{ $item['title'] }}</td>
                                <td class="sub">{{ $item['meta'] }}</td>
                                <td class="sub">{{ $item['at']?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>
                                    @if($item['url'])
                                        <a class="dw-btn dw-btn-sm" href="{{ $item['url'] }}">前往</a>
                                    @else
                                        <span class="sub">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @empty
        @endforelse

        @if($summary['total'] === 0)
            <div class="dw-panel-body">
                <div class="dw-empty">
                    <i class="fas fa-mug-hot" aria-hidden="true"></i>
                    <p>当前没有待处理事项。</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
