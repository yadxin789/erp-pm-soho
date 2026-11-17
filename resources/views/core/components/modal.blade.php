@php $did = $id ?? 'dw-dialog-'.substr(md5(uniqid('', true)), 0, 8); @endphp
<dialog id="{{ $did }}" aria-labelledby="{{ $did }}-title">
    <div class="dw-dialog-head" id="{{ $did }}-title">{{ $title ?? '确认操作' }}</div>
    <div class="dw-dialog-body">{!! $body ?? '' !!}</div>
    <div class="dw-dialog-foot">
        <button type="button" class="btn btn-light btn-sm" data-dialog-close value="cancel">取消</button>
        @if (isset($confirmText))
            <button type="button" class="btn btn-{{ $confirmType ?? 'primary' }} btn-sm" data-dialog-close value="confirm">{{ $confirmText }}</button>
        @endif
    </div>
</dialog>