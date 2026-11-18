@extends('soho::layouts.app')

@section('title', '平台账号')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item active">平台账号</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">平台账号列表</h5>
        <a href="{{ route('soho.platform_accounts.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> 添加账号
        </a>
    </div>
    <div class="card-body">
        <!-- 筛选表单 -->
        <form method="GET" action="{{ route('soho.platform_accounts.index') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索账号名称/平台/卖家" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="platform_code" class="form-select">
                        <option value="">全部平台</option>
                        <option value="taobao" @selected(request('platform_code') == 'taobao')>淘宝</option>
                        <option value="jd" @selected(request('platform_code') == 'jd')>京东</option>
                        <option value="pinduoduo" @selected(request('platform_code') == 'pinduoduo')>拼多多</option>
                        <option value="amazon" @selected(request('platform_code') == 'amazon')>亚马逊</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="active" @selected(request('status') == 'active')>启用</option>
                        <option value="inactive" @selected(request('status') == 'inactive')>禁用</option>
                        <option value="suspended" @selected(request('status') == 'suspended')>已暂停</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i> 搜索
                    </button>
                    <a href="{{ route('soho.platform_accounts.index') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>账号名称</th>
                        <th>平台</th>
                        <th>账号类型</th>
                        <th>开发者ID</th>
                        <th>卖家主体</th>
                        <th>授权状态</th>
                        <th>Token 过期</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($platformAccounts as $account)
                    @php
                        // 账号类型中文映射
                        $accountTypeMap = [
                            'developer' => '开发者账号',
                            'seller' => '卖家主体',
                            'agency' => '代理账号',
                        ];
                        $accountTypeLabel = $accountTypeMap[$account->account_type] ?? $account->account_type;
                    @endphp
                    <tr>
                        <td>{{ $account->id }}</td>
                        <td>{{ $account->account_name }}</td>
                        <td><span class="badge bg-info">{{ $account->platform_label }}</span></td>
                        <td>{{ $accountTypeLabel }}</td>
                        <td>{{ $account->developer_id ?? '-' }}</td>
                        <td>{{ $account->seller_name ?? '-' }}</td>
                        <td>
                            @if($account->oauth_status === 'authorized')
                                <span class="badge bg-success">{{ $account->oauth_status_label }}</span>
                            @elseif($account->oauth_status === 'expired')
                                <span class="badge bg-warning text-dark">{{ $account->oauth_status_label }}</span>
                            @else
                                <span class="badge bg-danger">{{ $account->oauth_status_label }}</span>
                            @endif
                        </td>
                        <td>
                            @if($account->token_expires_at)
                                @if($account->token_expires_at->isPast())
                                    <span class="text-danger">{{ $account->token_expires_at->format('Y-m-d') }} (已过期)</span>
                                @else
                                    {{ $account->token_expires_at->format('Y-m-d') }}
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $account->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $account->status_label }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('soho.platform_accounts.edit', $account) }}" class="btn btn-sm btn-outline-primary" title="编辑">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('soho.platform_accounts.authorize', $account) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-info" title="授权" onclick="return confirm('确定要授权此账号吗？')">
                                    <i class="fa fa-key"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted">暂无数据</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $platformAccounts->links() }}
    </div>
</div>
@endsection