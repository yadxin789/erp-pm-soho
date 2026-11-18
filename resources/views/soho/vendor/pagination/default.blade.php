{{--
    自定义默认分页模板（支持非 Bootstrap 5 场景）
    修复：使用 < > 替代 ‹ › 箭头，紧凑布局
--}}
@if ($paginator->hasPages())
    <nav class="pagination-wrapper">
        <ul class="pagination">
            {{-- 上一页链接 --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span aria-hidden="true"><</span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><</a>
                </li>
            @endif

            {{-- 分页元素 --}}
            @foreach ($elements as $element)
                {{-- 省略号 --}}
                @if (is_string($element))
                    <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                {{-- 页码链接 --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- 下一页链接 --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">></a>
                </li>
            @else
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span aria-hidden="true">></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
