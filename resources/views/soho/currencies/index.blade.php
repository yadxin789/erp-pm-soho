@extends('soho::layouts.app')

@section('title', '货币管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">货币列表</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCurrencyModal">
            <i class="fa fa-plus"></i> 新增货币
        </button>
    </div>
    <div class="card-body">
        <!-- 搜索筛选表单 -->
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索货币代码或名称" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="active" @selected(request('status') == 'active')>启用</option>
                        <option value="inactive" @selected(request('status') == 'inactive')>禁用</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.currencies.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>货币代码</th>
                        <th>货币名称</th>
                        <th>符号</th>
                        <th>汇率(兑人民币)</th>
                        <th>小数位数</th>
                        <th>是否默认</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($currencies as $currency)
                    <tr>
                        <td>{{ $currency->id }}</td>
                        <td><code>{{ $currency->code }}</code></td>
                        <td>{{ $currency->name }}</td>
                        <td>{{ $currency->symbol }}</td>
                        <td>
                            @if($currency->code === 'CNY')
                                <span class="text-muted">基准</span>
                            @else
                                {{ number_format($currency->exchange_rate, 4) }}
                            @endif
                        </td>
                        <td>{{ $currency->decimal_places }}</td>
                        <td>
                            @if($currency->is_default)
                                <span class="badge bg-warning text-dark">默认</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $currency->status ? 'success' : 'secondary' }}">
                                {{ $currency->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('soho.currencies.edit', $currency) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-edit"></i> 编辑
                            </a>
                            @if(!$currency->is_default)
                            <form method="POST" action="{{ route('soho.currencies.set-default', $currency) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="设为默认">
                                    <i class="fa fa-star"></i> 默认
                                </button>
                            </form>
                            <form method="POST" action="{{ route('soho.currencies.destroy', $currency) }}" class="d-inline" onsubmit="return confirm('确定删除此货币？')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i> 删除
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $currencies->links() }}
    </div>
</div>

<!-- 新增货币模态框 -->
<div class="modal fade" id="createCurrencyModal" tabindex="-1" aria-labelledby="createCurrencyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('soho.currencies.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createCurrencyModalLabel">新增货币</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- 货币代码 -->
                            <div class="mb-3">
                                <label class="form-label">货币代码 <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" required placeholder="如：USD、EUR、JPY" maxlength="3">
                            </div>
                            <!-- 货币名称 -->
                            <div class="mb-3">
                                <label class="form-label">货币名称 <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="如：美元">
                            </div>
                            <!-- 符号 -->
                            <div class="mb-3">
                                <label class="form-label">符号</label>
                                <input type="text" name="symbol" class="form-control" placeholder="如：$、€、¥">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- 汇率 -->
                            <div class="mb-3">
                                <label class="form-label">汇率(兑人民币) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">1 单位 =</span>
                                    <input type="number" step="0.0001" name="exchange_rate" class="form-control" required placeholder="如 7.2500">
                                    <span class="input-group-text">CNY</span>
                                </div>
                            </div>
                            <!-- 小数位数 -->
                            <div class="mb-3">
                                <label class="form-label">小数位数</label>
                                <input type="number" name="decimal_places" class="form-control" value="2" min="0" max="4">
                                <div class="form-text">一般货币为 2 位，比特币等可为 4 位</div>
                            </div>
                            <!-- 状态 -->
                            <div class="mb-3">
                                <label class="form-label">状态</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" value="1" checked id="currencyStatus">
                                    <label class="form-check-label" for="currencyStatus">启用</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 备注 -->
                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <textarea name="remark" class="form-control" rows="2" placeholder="货币备注说明"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection