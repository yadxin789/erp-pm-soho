@extends('layouts.business')

@section('title', '新建项目')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-diagram-project"></i>新建产品研发项目</h2>
        <a href="{{ erp_route('app.projects.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ erp_route('app.projects.store') }}">
        @csrf

        <div class="card mb-4">
            <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>项目信息</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="name">项目名称 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required value="{{ old('name') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="status">初始状态</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', 'not_started') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="budget">项目预算</label>
                        <input type="number" step="0.01" id="budget" name="budget" class="form-control" value="{{ old('budget', 0) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="product_name">产品名称</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" value="{{ old('product_name') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="product_model">产品型号</label>
                        <input type="text" id="product_model" name="product_model" class="form-control" value="{{ old('product_model') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="version">版本号</label>
                        <input type="text" id="version" name="version" class="form-control" value="{{ old('version') }}" placeholder="如 V1.0">
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="start_date">开始日期</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="end_date">结束日期</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="customer_id">关联客户</label>
                        <select id="customer_id" name="customer_id" class="form-select">
                            <option value="">（无）</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="manager_id">项目负责人</label>
                        <select id="manager_id" name="manager_id" class="form-select">
                            <option value="">（默认当前登录人）</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('manager_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="description">项目说明</label>
                        <textarea id="description" name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-list-check me-2"></i>项目任务（每个任务都要指定执行人与审核人）</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-task-row">
                    <i class="fa-solid fa-plus me-1"></i>添加任务
                </button>
            </div>
            <div class="card-body">
                <div id="task-rows">
                    @php
                        $oldTasks = old('tasks', [['name' => '', 'task_type_id' => '', 'executor_id' => '', 'auditor_id' => '', 'start_date' => '', 'end_date' => '', 'priority' => 2]]);
                    @endphp
                    @foreach($oldTasks as $i => $task)
                        <div class="row g-2 align-items-end task-row mb-2 pb-2 border-bottom">
                            <div class="col-12 col-md-3">
                                <label class="form-label small mb-0">任务名称 <span class="text-danger">*</span></label>
                                <input type="text" name="tasks[{{ $i }}][name]" class="form-control form-control-sm"
                                       value="{{ $task['name'] ?? '' }}" required>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-0">任务类型</label>
                                <select name="tasks[{{ $i }}][task_type_id]" class="form-select form-select-sm">
                                    <option value="">（未分类）</option>
                                    @foreach($taskTypes as $type)
                                        <option value="{{ $type->id }}" {{ ($task['task_type_id'] ?? '') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-0">执行人 <span class="text-danger">*</span></label>
                                <select name="tasks[{{ $i }}][executor_id]" class="form-select form-select-sm" required>
                                    <option value="">请选择</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ ($task['executor_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-0">审核人 <span class="text-danger">*</span></label>
                                <select name="tasks[{{ $i }}][auditor_id]" class="form-select form-select-sm" required>
                                    <option value="">请选择</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ ($task['auditor_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-1">
                                <label class="form-label small mb-0">优先级</label>
                                <select name="tasks[{{ $i }}][priority]" class="form-select form-select-sm">
                                    @foreach(\App\Erp\Models\Project\ProjectTask::PRIORITY as $key => $label)
                                        <option value="{{ $key }}" {{ ($task['priority'] ?? 2) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-0">开始</label>
                                <input type="date" name="tasks[{{ $i }}][start_date]" class="form-control form-control-sm" value="{{ $task['start_date'] ?? '' }}">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-0">结束</label>
                                <input type="date" name="tasks[{{ $i }}][end_date]" class="form-control form-control-sm" value="{{ $task['end_date'] ?? '' }}">
                            </div>
                            <div class="col-12 col-md-1 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-task-row" title="删除此行">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="small text-muted mt-2">
                    执行人在任务详情中上传交付文件，审核人在任务详情中审核通过或退回；审核人不能与执行人相同。
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建项目</button>
            <a href="{{ erp_route('app.projects.index') }}" class="btn btn-light">取消</a>
        </div>
    </form>

    <script>
        (function () {
            var rows = document.getElementById('task-rows');
            var addBtn = document.getElementById('add-task-row');
            var index = rows.querySelectorAll('.task-row').length;

            function bindRemove(row) {
                var btn = row.querySelector('.remove-task-row');
                btn.addEventListener('click', function () {
                    if (rows.querySelectorAll('.task-row').length > 1) {
                        row.remove();
                    }
                });
            }

            rows.querySelectorAll('.task-row').forEach(bindRemove);

            addBtn.addEventListener('click', function () {
                var template = rows.querySelector('.task-row').cloneNode(true);
                template.querySelectorAll('input').forEach(function (el) {
                    el.value = '';
                });
                template.querySelectorAll('select').forEach(function (el) {
                    el.selectedIndex = 0;
                });
                template.querySelectorAll('input, select').forEach(function (el) {
                    if (el.name) {
                        el.name = el.name.replace(/\d+/, index);
                    }
                });
                rows.appendChild(template);
                bindRemove(template);
                index++;
            });
        })();
    </script>
@endsection
