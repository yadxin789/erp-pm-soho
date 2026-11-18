@extends('soho::layouts.app')

@section('title', '运费模板管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">运费模板列表</h5>
        <a href="{{ route('soho.shipping-templates.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 添加运费模板
        </a>
    </div>
    <div class="card-body">
        <!-- 搜索筛选表单 -->
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索模板名称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="billing_type" class="form-select">
                        <option value="">全部计费方式</option>
                        <option value="fixed" @selected(request('billing_type') == 'fixed')>固定运费</option>
                        <option value="weight" @selected(request('billing_type') == 'weight')>按重量计费</option>
                        <option value="volume" @selected(request('billing_type') == 'volume')>按体积计费</option>
                        <option value="quantity" @selected(request('billing_type') == 'quantity')>按件数计费</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="1" @selected(request('status') == '1')>启用</option>
                        <option value="0" @selected(request('status') == '0')>禁用</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.shipping-templates.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <!-- 运费模板数据表格 -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>模板名称</th>
                        <th>计费方式</th>
                        <th>默认运费</th>
                        <th>首件/首重</th>
                        <th>首费</th>
                        <th>续件/续重</th>
                        <th>续费</th>
                        <th>适用商品数</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td>{{ $template->id }}</td>
                        <td>
                            <strong>{{ $template->name }}</strong>
                            @if($template->is_default)
                                <span class="badge bg-info ms-1">默认</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $billingMap = [
                                    'fixed' => ['label' => '固定运费', 'class' => 'primary'],
                                    'weight' => ['label' => '按重量', 'class' => 'info'],
                                    'volume' => ['label' => '按体积', 'class' => 'warning'],
                                    'quantity' => ['label' => '按件数', 'class' => 'success'],
                                ];
                                $billingInfo = $billingMap[$template->billing_type] ?? ['label' => $template->billing_type ?? '未知', 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $billingInfo['class'] }}">{{ $billingInfo['label'] }}</span>
                        </td>
                        <td>¥{{ number_format($template->default_fee ?? 0, 2) }}</td>
                        <td>{{ $template->first_quantity ?? '-' }}{{ $template->billing_type == 'weight' ? 'kg' : ($template->billing_type == 'volume' ? 'm³' : '件') }}</td>
                        <td>¥{{ number_format($template->first_fee ?? 0, 2) }}</td>
                        <td>{{ $template->additional_quantity ?? '-' }}{{ $template->billing_type == 'weight' ? 'kg' : ($template->billing_type == 'volume' ? 'm³' : '件') }}</td>
                        <td>¥{{ number_format($template->additional_fee ?? 0, 2) }}</td>
                        <td><span class="badge bg-secondary">{{ $template->products_count ?? 0 }}</span></td>
                        <td>
                            <span class="badge bg-{{ $template->status ? 'success' : 'secondary' }}">
                                {{ $template->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('soho.shipping-templates.edit', $template) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i> 编辑
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewTemplate({{ $template->id }})">
                                <i class="fa fa-eye"></i> 详情
                            </button>
                            <form method="POST" action="{{ route('soho.shipping-templates.destroy', $template) }}" class="d-inline" onsubmit="return confirm('确定删除此运费模板？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i> 删除
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2"></i>
                            <p>暂无运费模板数据</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                共 {{ $templates->total() }} 条数据
            </div>
            {{ $templates->links() }}
        </div>
    </div>
</div>

<!-- 运费模板详情弹窗 -->
<div class="modal fade" id="templateDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-truck me-2"></i>运费模板详情</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="templateDetailBody">
                <!-- 通过AJAX加载详情内容 -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">加载中...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 查看模板详情
    function viewTemplate(templateId) {
        var modal = new bootstrap.Modal(document.getElementById('templateDetailModal'));
        var body = document.getElementById('templateDetailBody');

        fetch('/shipping-templates/' + templateId + '/detail')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    var html = '<div class="row">' +
                        '<div class="col-md-6">' +
                            '<table class="table table-sm">' +
                                '<tr><th>模板名称</th><td>' + data.template.name + '</td></tr>' +
                                '<tr><th>计费方式</th><td>' + data.template.billing_type_label + '</td></tr>' +
                                '<tr><th>首件/首重</th><td>' + data.template.first_quantity + '</td></tr>' +
                                '<tr><th>首费</th><td>¥' + data.template.first_fee + '</td></tr>' +
                            '</table>' +
                        '</div>' +
                        '<div class="col-md-6">' +
                            '<table class="table table-sm">' +
                                '<tr><th>续件/续重</th><td>' + data.template.additional_quantity + '</td></tr>' +
                                '<tr><th>续费</th><td>¥' + data.template.additional_fee + '</td></tr>' +
                                '<tr><th>默认运费</th><td>¥' + data.template.default_fee + '</td></tr>' +
                                '<tr><th>状态</th><td>' + (data.template.status ? '启用' : '禁用') + '</td></tr>' +
                            '</table>' +
                        '</div>' +
                    '</div>';

                    if (data.template.areas && data.template.areas.length > 0) {
                        html += '<h6 class="mt-3">适用地区</h6><ul class="list-group list-group-flush">';
                        data.template.areas.forEach(function(area) {
                            html += '<li class="list-group-item">' + area.name + ' - 首重:' + area.first_quantity + ' 首费:¥' + area.first_fee + ' 续重:' + area.additional_quantity + ' 续费:¥' + area.additional_fee + '</li>';
                        });
                        html += '</ul>';
                    }

                    body.innerHTML = html;
                } else {
                    body.innerHTML = '<div class="alert alert-danger">加载失败：' + data.message + '</div>';
                }
            })
            .catch(function(error) {
                body.innerHTML = '<div class="alert alert-danger">加载失败，请稍后重试</div>';
            });

        modal.show();
    }
</script>
@endsection