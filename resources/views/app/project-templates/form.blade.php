@extends('layouts.business')

@section('title', $mode === 'create' ? '新建项目模板' : '编辑项目模板')

@section('content')
    @php $rows = old('tasks', $template->tasks ?: [['name' => '', 'priority' => 2, 'duration_days' => 7]]); @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title mb-0"><i class="fa-solid fa-layer-group"></i>{{ $mode === 'create' ? '新建项目模板' : '编辑项目模板' }}</h2>
        <a href="{{ erp_route('app.project-templates.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>返回</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $mode === 'create' ? erp_route('app.project-templates.store') : erp_route('app.project-templates.update', $template) }}">
                @csrf
                @if($mode === 'edit') @method('PUT') @endif

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label">模板名称 <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">说明</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $template->description) }}">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">任务清单</label>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-row"><i class="fa-solid fa-plus me-1"></i>添加一行</button>
                </div>

                <div id="task-rows">
                    @foreach($rows as $i => $row)
                        <div class="row g-2 mb-2 task-row">
                            <div class="col-md-6">
                                <input type="text" name="tasks[{{ $i }}][name]" class="form-control" placeholder="任务名称" value="{{ $row['name'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <select name="tasks[{{ $i }}][priority]" class="form-select">
                                    @foreach([1 => '紧急', 2 => '高', 3 => '中', 4 => '低'] as $value => $label)
                                        <option value="{{ $value }}" {{ (int) ($row['priority'] ?? 2) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="tasks[{{ $i }}][duration_days]" class="form-control" min="1" max="365" value="{{ $row['duration_days'] ?? 7 }}">
                            </div>
                            <div class="col-md-1 d-grid">
                                <button type="button" class="btn btn-outline-danger remove-row"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>保存模板</button>
                    <a href="{{ erp_route('app.project-templates.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var rows = document.getElementById('task-rows');
            var seq = {{ count($rows) }};
            document.getElementById('add-row').addEventListener('click', function () {
                var tpl = rows.querySelector('.task-row').cloneNode(true);
                tpl.querySelectorAll('input, select').forEach(function (el) {
                    el.name = el.name.replace(/tasks\[\d+\]/, 'tasks[' + seq + ']');
                    if (el.type !== 'hidden' && el.tagName === 'INPUT') { el.value = el.name.indexOf('duration') >= 0 ? 7 : ''; }
                });
                rows.appendChild(tpl);
                seq++;
            });
            rows.addEventListener('click', function (e) {
                if (e.target.closest('.remove-row')) {
                    if (rows.querySelectorAll('.task-row').length > 1) {
                        e.target.closest('.task-row').remove();
                    }
                }
            });
        })();
    </script>
@endsection
