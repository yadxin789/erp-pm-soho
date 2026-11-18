{{--
    自定义默认简洁分页模板
    修复：使用 < > 替代 ‹ › 箭头
--}}
@if ($paginator->hasPages())
    <nav class="pagination-wrapper">
        <ul class="pagination">
            {{-- 上一页链接 --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true"><span><</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev"><</a></li>
            @endif

            {{-- 下一页链接 --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">></a></li>
            @else
                <li class="disabled" aria-disabled="true"><span>></span></li>
            @endif
        </ul>
    </nav>
@endif
