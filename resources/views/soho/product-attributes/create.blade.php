@extends('soho::layouts.app')

@section('title', '创建商品属性')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="{{ route('soho.product-attribute.index') }}">商品属性</a></li>
        <li class="breadcrumb-item active">创建属性</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header"><h5 class="mb-0">创建商品属性</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('soho.product-attribute.store') }}">
            @csrf
            <!-- 基本信息 -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">属性名称 <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="如：颜色、尺寸">
                </div>
                <div class="col-md-6">
                    <label class="form-label">属性编码 <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" required placeholder="如：color、size">
                </div>
            </div>

            <!-- 类型和排序 -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">属性类型 <span class="text-danger">*</span></label>
                    <select name="type" id="typeSelect" class="form-select" required>
                        <option value="">选择类型</option>
                        <option value="text" @selected(old('type') == 'text')>文本输入</option>
                        <option value="number" @selected(old('type') == 'number')>数字输入</option>
                        <option value="select" @selected(old('type') == 'select')>下拉选择（单选）</option>
                        <option value="multi_select" @selected(old('type') == 'multi_select')>多选下拉</option>
                        <option value="date" @selected(old('type') == 'date')>日期选择</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">排序</label>
                    <input type="number" name="sort" class="form-control" value="{{ old('sort', 0) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">状态</label>
                    <select name="status" class="form-select">
                        <option value="1" @selected(old('status', '1') == '1')>启用</option>
                        <option value="0" @selected(old('status') == '0')>禁用</option>
                    </select>
                </div>
            </div>

            <!-- 选项配置（select/multi_select 类型） -->
            <div id="optionsSection" class="mb-3" style="display:none;">
                <label class="form-label">选项配置</label>
                <div id="optionsList">
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
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addOption">
                    <i class="fa fa-plus"></i> 添加选项
                </button>
            </div>

            <!-- 属性值配置 -->
            <div id="valuesSection" class="mb-3">
                <label class="form-label">属性值</label>
                <div id="valuesList">
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
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addValue">
                    <i class="fa fa-plus"></i> 添加属性值
                </button>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-check"></i> 创建
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
    toggleOptions();

    // 选项动态添加/删除
    let optionIndex = 1;
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
    let valueIndex = 1;
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