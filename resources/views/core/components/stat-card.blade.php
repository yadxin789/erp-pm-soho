<div class="dw-card dw-stat">
    <span class="icon" aria-hidden="true"><i class="fas {{ $icon ?? 'fa-chart-simple' }}"></i></span>
    <div>
        <div class="label">{{ $label ?? '' }}</div>
        <div class="value">{{ $value ?? '—' }}</div>
        @isset($delta)<div class="delta {{ $trend ?? 'up' }}">{{ $delta }}</div>@endisset
    </div>
</div>