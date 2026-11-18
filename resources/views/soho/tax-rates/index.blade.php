@extends('soho::layouts.app')

@section('title', '税率管理')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">税率列表</h5>
        <a href="{{ route('soho.tax-rate.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 新增税率
        </a>
    </div>
    <div class="card-body">
        <!-- 搜索筛选表单 -->
        <form method="GET" action="{{ route('soho.tax-rate.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索税率名称或国家代码" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="country_code" class="form-select">
                        <option value="">全部国家/地区</option>
                        <option value="CN" @selected(request('country_code') == 'CN')>中国 (CN)</option>
                        <option value="US" @selected(request('country_code') == 'US')>美国 (US)</option>
                        <option value="JP" @selected(request('country_code') == 'JP')>日本 (JP)</option>
                        <option value="KR" @selected(request('country_code') == 'KR')>韩国 (KR)</option>
                        <option value="EU" @selected(request('country_code') == 'EU')>欧盟 (EU)</option>
                        <option value="UK" @selected(request('country_code') == 'UK')>英国 (UK)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="" @selected(request('status') === '')>全部状态</option>
                        <option value="1" @selected(request('status') == '1')>启用</option>
                        <option value="0" @selected(request('status') == '0')>禁用</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.tax-rate.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>税率名称</th>
                        <th>国家/地区</th>
                        <th>省份</th>
                        <th>税率值</th>
                        <th>优先级</th>
                        <th>状态</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxRates as $taxRate)
                    <tr>
                        <td>{{ $taxRate->id }}</td>
                        <td>{{ $taxRate->name }}</td>
                        <td>
                            @php
                                $countryMap = [
                                    'CN' => '中国',
                                    'US' => '美国',
                                    'JP' => '日本',
                                    'KR' => '韩国',
                                    'EU' => '欧盟',
                                    'UK' => '英国',
                                ];
                            @endphp
                            {{ $countryMap[$taxRate->country_code] ?? $taxRate->country_code }}
                        </td>
                        <td>{{ $taxRate->province ?? '-' }}</td>
                        <td><span class="text-danger fw-bold">{{ number_format($taxRate->rate, 2) }}%</span></td>
                        <td>{{ $taxRate->priority ?? 0 }}</td>
                        <td>
                            <span class="badge bg-{{ $taxRate->status ? 'success' : 'secondary' }}">
                                {{ $taxRate->status ? '启用' : '禁用' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('soho.tax-rate.edit', $taxRate) }}" class="btn btn-outline-primary">
                                    <i class="fa fa-edit"></i> 编辑
                                </a>
                                <form method="POST" action="{{ route('soho.tax-rate.destroy', $taxRate) }}" class="d-inline" onsubmit="return confirm('确定删除此税率？')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fa fa-trash"></i> 删除
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2"></i>
                            <p>暂无税率数据</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $taxRates->links() }}
        </div>
    </div>
</div>
@endsection
