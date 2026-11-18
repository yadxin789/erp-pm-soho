{{--
    自定义 Bootstrap 5 分页模板
    修复：使用 < > 替代 ‹ › 箭头，紧凑布局，减少间距
--}}
@if ($paginator->hasPages())
    <nav class="d-flex justify-items-center justify-content-between pagination-wrapper">
        <div class="d-flex justify-content-between flex-fill d-sm-none">
            <ul class="pagination">
                {{-- 上一页链接（移动端） --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link" aria-hidden="true"><</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><</a>
                    </li>
                @endif

                {{-- 下一页链接（移动端） --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">></a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link" aria-hidden="true">></span>
                    </li>
                @endif
            </ul>
        </div>

        <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between">
            {{-- 结果统计信息（桌面端） --}}
            <div class="pagination-info">
                <p class="small text-muted mb-0">
                    {{ __('显示') }}
                    <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                    {{ __('至') }}
                    <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                    {{ __('条，共') }}
                    <span class="fw-semibold">{{ $paginator->total() }}</span>
                    {{ __('条') }}
                </p>
            </div>

            {{-- 分页页码（桌面端） --}}
            <div>
                <ul class="pagination">
                    {{-- 上一页按钮 --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true"><</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><</a>
                        </li>
                    @endif

                    {{-- 分页元素（页码 / 省略号） --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            {{-- 省略号 --}}
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    {{-- 当前页 --}}
                                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    {{-- 其他页码 --}}
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- 下一页按钮 --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">></a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true">></span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
