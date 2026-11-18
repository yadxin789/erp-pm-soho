{{--
    自定义 Bootstrap 5 简洁分页模板（仅上一页/下一页）
    修复：使用 < > 替代 ‹ › 箭头，紧凑布局
--}}
@if ($paginator->hasPages())
    <nav class="pagination-wrapper">
        <ul class="pagination">
            {{-- 上一页链接 --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><</a>
                </li>
            @endif

            {{-- 下一页链接 --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">></a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
