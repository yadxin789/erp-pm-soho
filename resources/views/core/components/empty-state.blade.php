<div class="dw-empty">
    <div class="icon" aria-hidden="true"><i class="far {{ $icon ?? 'fa-folder-open' }}"></i></div>
    <div class="title">{{ $title ?? '暂无数据' }}</div>
    @isset($desc)<div>{{ $desc }}</div>@endisset
    @isset($action)<div style="margin-top:12px">{!! $action !!}</div>@endisset
</div>