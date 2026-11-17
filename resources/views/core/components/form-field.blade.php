@php
    $name = $name ?? 'field';
    $fid = 'f-'.str_replace(['[', ']', '.'], ['-', '', '-'], $name).'-'.substr(md5($label ?? $name), 0, 4);
    $type = $type ?? 'text';
    $required = $required ?? false;
    $errorId = $fid.'-err';
    $helpId = isset($help) ? $fid.'-help' : null;
@endphp
<div class="dw-field">
    <label for="{{ $fid }}">{{ $label ?? $name }}@if ($required)<span class="req" aria-hidden="true">*</span>@endif</label>

    @if ($type === 'select')
        <select id="{{ $fid }}" name="{{ $name }}" class="dw-select"
                @if ($required) required @endif
                @if (isset($error)) data-error="{{ $errorId }}" @endif>
            @foreach (($options ?? []) as $val => $text)
                <option value="{{ $val }}" @selected(($value ?? null) == $val)>{{ $text }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $fid }}" name="{{ $name }}" rows="{{ $rows ?? 3 }}" class="dw-textarea"
                  @if ($required) required @endif
                  @if (isset($error)) data-error="{{ $errorId }}" @endif>{{ $value ?? '' }}</textarea>
    @else
        <input id="{{ $fid }}" type="{{ $type }}" name="{{ $name }}" class="dw-input"
               value="{{ $value ?? '' }}"
               @if (isset($placeholder)) placeholder="{{ $placeholder }}" @endif
               @if ($required) required @endif
               @if (isset($error)) data-error="{{ $errorId }}" @endif>
    @endif

    @if ($helpId)
        <span id="{{ $helpId }}" class="dw-help">{{ $help }}</span>
    @endif

    @if (isset($error))
        <span id="{{ $errorId }}" class="dw-error">{{ $error }}</span>
    @endif
</div>