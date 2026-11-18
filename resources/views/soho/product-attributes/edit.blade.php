@extends('soho::layouts.app')

@section('title', '编辑商品属性')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.product-attribute.index') }}">商品属性</a></li>
        <li class="breadcrumb-item active">编辑属性</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header"><h5 class="mb-0">编辑商品属性</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.product-attribute.update', $productAttribute) }}">
            @csrf @method('PUT')
            <!-- 基本信息 -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">属性名称 <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $productAttribute->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">属性编码 <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $productAttribute->code) }}" required>
                </div>
            </div>

            <!-- 类型和排序 -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">属性类型 <span class="text-danger">*</span></label>
                    <select name="type" id="typeSelect" class="form-select" required>
                        <option value="text" @selected($productAttribute->type == 'text')>文本输入</option>
                        <option value="number" @selected($productAttribute->type == 'number')>数字输入</option>
                        <option value="select" @selected($productAttribute->type == 'select')>下拉选择（单选）</option>
                        <option value="multi_select" @selected($productAttribute->type == 'multi_select')>多选下拉</option>
                        <option value="date" @selected($productAttribute->type == 'date')>日期选择</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">排序</label>
                    <input type="number" name="sort" class="form-control" value="{{ old('sort', $productAttribute->sort) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">状态</label>
                    <select name="status" class="form-select">
                        <option value="1" @selected($productAttribute->status == 1)>启用</option>
                        <option value="0" @selected($productAttribute->status == 0)>禁用</option>
                    </select>
                </div>
            </div>

            <!-- 选项配置（select/multi_select 类型） -->
            <div id="optionsSection" class="mb-3" style="{{ in_array($productAttribute->type, ['select', 'multi_select']) ? '' : 'display:none;' }}">
                <label class="form-label">选项配置</label>
                <div id="optionsList">
                    @php
                        $options = old('options', $productAttribute->options_json ?? []);
                    @endphp
                    @if(!empty($options))
                        @foreach($options as $idx => $opt)
                            <div class="option-row row g-2 mb-2">
                                <div class="col-md-4">
                                    <input type="text" name="options[{{ $idx }}][label]" class="form-control" value="{{ $opt['label'] ?? '' }}" placeholder="选项标签">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="options[{{ $idx }}][value]" class="form-control" value="{{ $opt['value'] ?? '' }}" placeholder="选项值">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger remove-option">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="option-row row g-2 mb-2">
                            <div class="col-md-4">
                                <input type="text" name="options[0][label]" class="form-control" placeholder="选项标签">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="options[0][value]" class="form-control" placeholder="选项值">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger remove-option" disabled>
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addOption">
                    <i class="fa fa-plus"></i> 添加选项
                </button>
            </div>

            <!-- 属性值配置 -->
            <div id="valuesSection" class="mb-3">
                <label class="form-label">属性值</label>
                <div id="valuesList">
                    @php
                        $values = old('values', $productAttribute->values->toArray());
                    @endphp
                    @if(!empty($values))
                        @foreach($values as $idx => $val)
                            <div class="value-row row g-2 mb-2">
                                <div class="col-md-6">
                                    <input type="text" name="values[{{ $idx }}][value_name]" class="form-control" value="{{ $val['value_name'] ?? '' }}" placeholder="属性值名称">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="values[{{ $idx }}][sort_order]" class="form-control" value="{{ $val['sort_order'] ?? $idx }}" placeholder="排序" min="0">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger remove-value" disabled>
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="value-row row g-2 mb-2">
                            <div class="col-md-6">
                                <input type="text" name="values[0][value_name]" class="form-control" placeholder="属性值名称">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="values[0][sort_order]" class="form-control" placeholder="排序" min="0" value="0">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger remove-value" disabled>
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addValue">
                    <i class="fa fa-plus"></i> 添加属性值
                </button>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> 保存
            </button>
            <a href="{{ route('soho.product-attribute.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> 返回
            </a>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 根据类型显示/隐藏选项配置
    const typeSelect = document.getElementById('typeSelect');
    const optionsSection = document.getElementById('optionsSection');

    function toggleOptions() {
        const val = typeSelect.value;
        optionsSection.style.display = (val === 'select' || val === 'multi_select') ? '' : 'none';
    }
    typeSelect.addEventListener('change', toggleOptions);

    // 选项动态添加/删除
    let optionIndex = {{ count($productAttribute->options_json ?? []) }} + 1;
    if (optionIndex < 1) optionIndex = 1;

    document.getElementById('addOption').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'option-row row g-2 mb-2';
        row.innerHTML = `
            <div class="col-md-4">
                <input type="text" name="options[${optionIndex}][label]" class="form-control" placeholder="选项标签">
            </div>
            <div class="col-md-4">
                <input type="text" name="options[${optionIndex}][value]" class="form-control" placeholder="选项值">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger remove-option">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
        document.getElementById('optionsList').appendChild(row);
        optionIndex++;
        updateRemoveButtons();
    });

    document.getElementById('optionsList').addEventListener('click', function (e) {
        if (e.target.closest('.remove-option')) {
            e.target.closest('.option-row').remove();
            updateRemoveButtons();
        }
    });

    // 属性值动态添加/删除
    let valueIndex = {{ count($productAttribute->values) }} + 1;
    if (valueIndex < 1) valueIndex = 1;

    document.getElementById('addValue').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'value-row row g-2 mb-2';
        row.innerHTML = `
            <div class="col-md-6">
                <input type="text" name="values[${valueIndex}][value_name]" class="form-control" placeholder="属性值名称">
            </div>
            <div class="col-md-3">
                <input type="number" name="values[${valueIndex}][sort_order]" class="form-control" placeholder="排序" min="0" value="0">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger remove-value">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
        document.getElementById('valuesList').appendChild(row);
        valueIndex++;
        updateRemoveButtons();
    });

    document.getElementById('valuesList').addEventListener('click', function (e) {
        if (e.target.closest('.remove-value')) {
            e.target.closest('.value-row').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const optRows = document.querySelectorAll('.option-row');
        optRows.forEach((row, i) => {
            row.querySelector('.remove-option').disabled = optRows.length <= 1;
        });
        const valRows = document.querySelectorAll('.value-row');
        valRows.forEach((row, i) => {
            row.querySelector('.remove-value').disabled = valRows.length <= 1;
        });
    }
</script>
@endsection