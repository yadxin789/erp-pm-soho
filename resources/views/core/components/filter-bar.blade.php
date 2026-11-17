<form class="dw-card" method="{{ $method ?? 'GET' }}" action="{{ $action ?? url()->current() }}" role="search">
    <div class="dw-filter">
        @foreach (($fields ?? []) as $field)
            @include('core::components.form-field', $field)
        @endforeach
        <div class="dw-field" style="justify-content:flex-end">
            <button type="submit" class="btn btn-primary btn-sm">筛选</button>
            @isset($resetUrl)
                <a class="btn btn-link btn-sm" href="{{ $resetUrl }}">重置</a>
            @endisset
        </div>
    </div>
</form>