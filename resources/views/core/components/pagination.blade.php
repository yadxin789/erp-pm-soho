@php
    $p = $paginator ?? null;
    if (! $p) { return; }
    $cur = $p->currentPage(); $last = $p->lastPage();
    $win = max(1, $cur - 2); $winEnd = min($last, $cur + 2);
@endphp
<nav class="dw-pagination" aria-label="分页导航">
    <span>共 {{ $p->total() }} 条 / {{ $last }} 页</span>
    @if ($cur > 1)
        <a href="{{ $p->url($cur - 1) }}" rel="prev">上一页</a>
    @else
        <span class="disabled">上一页</span>
    @endif

    @for ($i = $win; $i <= $winEnd; $i++)
        @if ($i === $cur)
            <span class="active" aria-current="page">{{ $i }}</span>
        @else
            <a href="{{ $p->url($i) }}">{{ $i }}</a>
        @endif
    @endfor

    @if ($cur < $last)
        <a href="{{ $p->url($cur + 1) }}" rel="next">下一页</a>
    @else
        <span class="disabled">下一页</span>
    @endif
</nav>