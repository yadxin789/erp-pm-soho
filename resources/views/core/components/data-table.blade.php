@php
    $tid = $id ?? 'dw-table-'.substr(md5(uniqid('', true)), 0, 8);
    $cols = $columns ?? [];
    $items = $rows ?? [];
@endphp
<div class="table-wrapper" tabindex="0" role="region" aria-labelledby="{{ $tid }}-caption">
    <table class="dw-table">
        <caption id="{{ $tid }}-caption">{{ $title ?? '数据列表' }}</caption>
        <thead>
            <tr>
                @foreach ($cols as $key => $label)
                    <th scope="col">{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $row)
                <tr>
                    @foreach ($cols as $key => $label)
                        @php $value = is_array($row) ? ($row[$key] ?? '') : ($row->{$key} ?? ''); @endphp
                        @if ($loop->first)
                            <th scope="row" data-label="{{ $label }}">{!! $value !!}</th>
                        @else
                            <td data-label="{{ $label }}">{!! $value !!}</td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($cols) }}" style="border:0">
                        @include('core::components.empty-state', ['title' => $empty ?? '暂无数据', 'desc' => $emptyDesc ?? ''])
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>