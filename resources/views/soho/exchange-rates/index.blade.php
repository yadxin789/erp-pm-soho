@extends('soho::layouts.app')

@section('title', '汇率管理')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">汇率管理</li>
    </ol>
</nav>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">新增汇率</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('soho.exchange_rates.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">源货币 <span class="text-danger">*</span></label>
                        <select name="from_currency" class="form-select" required>
                            <option value="USD" @selected(old('from_currency') == 'USD')>美元 (USD)</option>
                            <option value="EUR" @selected(old('from_currency') == 'EUR')>欧元 (EUR)</option>
                            <option value="GBP" @selected(old('from_currency') == 'GBP')>英镑 (GBP)</option>
                            <option value="JPY" @selected(old('from_currency') == 'JPY')>日元 (JPY)</option>
                            <option value="CNY" @selected(old('from_currency') == 'CNY')>人民币 (CNY)</option>
                            <option value="HKD" @selected(old('from_currency') == 'HKD')>港币 (HKD)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">目标货币 <span class="text-danger">*</span></label>
                        <select name="to_currency" class="form-select" required>
                            <option value="CNY" @selected(old('to_currency') == 'CNY')>人民币 (CNY)</option>
                            <option value="USD" @selected(old('to_currency') == 'USD')>美元 (USD)</option>
                            <option value="EUR" @selected(old('to_currency') == 'EUR')>欧元 (EUR)</option>
                            <option value="GBP" @selected(old('to_currency') == 'GBP')>英镑 (GBP)</option>
                            <option value="JPY" @selected(old('to_currency') == 'JPY')>日元 (JPY)</option>
                            <option value="HKD" @selected(old('to_currency') == 'HKD')>港币 (HKD)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">汇率 <span class="text-danger">*</span></label>
                        <input type="number" name="rate" class="form-control" step="0.0001" min="0" value="{{ old('rate') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">备注</label>
                        <input type="text" name="remark" class="form-control" value="{{ old('remark') }}">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-plus"></i> 新增汇率
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">汇率列表</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('soho.exchange_rates.index') }}" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="搜索货币" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fa fa-search me-1"></i> 搜索
                            </button>
                            <a href="{{ route('soho.exchange_rates.index') }}" class="btn btn-outline-secondary">重置</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>源货币</th>
                                <th>目标货币</th>
                                <th>汇率</th>
                                <th>备注</th>
                                <th>更新时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exchangeRates as $rate)
                            <tr>
                                <td>{{ $rate->id }}</td>
                                <td><span class="badge bg-info">{{ $rate->from_currency }}</span></td>
                                <td><span class="badge bg-success">{{ $rate->to_currency }}</span></td>
                                <td class="fw-bold">{{ number_format($rate->rate, 4) }}</td>
                                <td>{{ $rate->remark ?? '-' }}</td>
                                <td>{{ $rate->updated_at ? $rate->updated_at->format('Y-m-d H:i') : '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">暂无数据</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $exchangeRates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection