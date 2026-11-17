@php
    $vals = array_values($values ?? []);
    $labs = array_values($labels ?? []);
    $h = $height ?? 160;
    $max = $vals ? max($vals) : 1; $max = $max > 0 ? $max : 1;
    $n = max(count($vals), 1);
    $gap = 8; $bw = $n ? (100 - ($n - 1) * $gap / $n) / $n : 100;
@endphp
<div class="dw-card">
    @isset($title)<div class="dw-card-title">{{ $title }}</div>@endisset
    <svg class="dw-chart" viewBox="0 0 100 {{ $h }}" preserveAspectRatio="none" role="img"
         aria-label="{{ $title ?? '数据图表' }}：{{ implode('、', array_map(fn ($l, $i) => $l.' '.($vals[$i] ?? 0), $labs, array_keys($labs))) }}">
        @foreach ($vals as $i => $v)
            @php $bh = $max ? ($v / $max) * ($h - 24) : 0; $x = $i * ($bw + $gap); @endphp
            <rect x="{{ $x }}" y="{{ $h - 16 - $bh }}" width="{{ $bw }}" height="{{ $bh }}"
                  fill="var(--dw-brand)" rx="1.5" opacity="{{ 1 - $i * 0.12 }}"></rect>
            <text x="{{ $x + $bw / 2 }}" y="{{ $h - 4 }}" text-anchor="middle">{{ $labs[$i] ?? '' }}</text>
        @endforeach
    </svg>
</div>