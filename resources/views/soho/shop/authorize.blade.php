@extends('soho::layouts.app')

@section('title', '店铺授权管理')

@section('content')
<!-- 统计卡片 -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card text-center border-0 bg-light">
            <div class="card-body">
                <h3 class="text-primary mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0 mt-2">店铺总数</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-0 bg-light">
            <div class="card-body">
                <h3 class="text-success mb-0">{{ $stats['authorized'] }}</h3>
                <p class="text-muted mb-0 mt-2">已授权</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-0 bg-light">
            <div class="card-body">
                <h3 class="text-danger mb-0">{{ $stats['unauthorized'] }}</h3>
                <p class="text-muted mb-0 mt-2">未授权</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-0 bg-light">
            <div class="card-body">
                <h3 class="text-warning mb-0">{{ $stats['expiring'] }}</h3>
                <p class="text-muted mb-0 mt-2">即将过期(7天内)</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">店铺授权管理</h5>
    </div>
    <div class="card-body">
        <!-- 筛选表单 -->
        <form method="GET" action="{{ route('soho.shop.authorize') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="搜索店铺名称/编码" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="platform" class="form-select">
                        <option value="">全部平台</option>
                        <option value="taobao" @selected(request('platform') == 'taobao')>淘宝</option>
                        <option value="jd" @selected(request('platform') == 'jd')>京东</option>
                        <option value="pinduoduo" @selected(request('platform') == 'pinduoduo')>拼多多</option>
                        <option value="amazon" @selected(request('platform') == 'amazon')>亚马逊</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="auth_status" class="form-select">
                        <option value="">全部状态</option>
                        <option value="1" @selected(request('auth_status') == '1')>已授权</option>
                        <option value="0" @selected(request('auth_status') == '0')>未授权</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search me-1"></i> 筛选
                    </button>
                    <a href="{{ route('soho.shop.authorize') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        <!-- 店铺授权列表 -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>店铺名称</th>
                        <th>平台</th>
                        <th>店铺编码</th>
                        <th>授权状态</th>
                        <th>Token 过期时间</th>
                        <th>AppKey 配置</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shops as $shop)
                    <tr>
                        <td>{{ $shop->id }}</td>
                        <td>{{ $shop->shop_name }}</td>
                        <td>
                            <span class="badge bg-info">{{ $shop->platform_label }}</span>
                        </td>
                        <td>{{ $shop->shop_code }}</td>
                        <td>
                            @if($shop->auth_status)
                                <span class="badge bg-success">已授权</span>
                            @else
                                <span class="badge bg-danger">未授权</span>
                            @endif
                        </td>
                        <td>
                            @if($shop->token_expires_at)
                                @php
                                    $isExpired = $shop->token_expires_at->isPast();
                                    $isExpiring = !$isExpired && $shop->token_expires_at->lte(now()->addDays(7));
                                @endphp
                                @if($isExpired)
                                    <span class="text-danger">
                                        <i class="fa fa-exclamation-circle"></i>
                                        {{ $shop->token_expires_at->format('Y-m-d H:i') }} (已过期)
                                    </span>
                                @elseif($isExpiring)
                                    <span class="text-warning">
                                        <i class="fa fa-clock-o"></i>
                                        {{ $shop->token_expires_at->format('Y-m-d H:i') }} (即将过期)
                                    </span>
                                @else
                                    <span class="text-success">
                                        {{ $shop->token_expires_at->format('Y-m-d H:i') }}
                                    </span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($shop->app_key)
                                <span class="badge bg-secondary">已配置</span>
                            @else
                                <span class="badge bg-warning">未配置</span>
                            @endif
                        </td>
                        <td>
                            <!-- 检测授权 -->
                            <form method="POST" action="{{ route('soho.shop.check-auth', $shop) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-info" title="检测授权状态">
                                    <i class="fa fa-check-circle"></i>
                                </button>
                            </form>

                            <!-- 刷新 Token -->
                            <form method="POST" action="{{ route('soho.shop.refresh-token', $shop) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="刷新 Token" onclick="return confirm('确定要刷新该店铺的 Token 吗？')">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </form>

                            <!-- 编辑店铺 -->
                            <a href="{{ route('soho.shop.edit', $shop) }}" class="btn btn-sm btn-outline-primary" title="编辑店铺">
                                <i class="fa fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2"></i>
                            <p>暂无店铺数据</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        @if($shops->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $shops->links() }}
        </div>
        @endif
    </div>
</div>

<!-- 操作提示 -->
<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fa fa-info-circle"></i> 授权说明</h6>
    </div>
    <div class="card-body">
        <ul class="mb-0 text-muted small">
            <li><strong>检测授权</strong>：调用平台 API 验证当前 Token 是否有效</li>
            <li><strong>刷新 Token</strong>：使用 Refresh Token 获取新的 Access Token（需先配置 AppKey/AppSecret）</li>
            <li><strong>编辑店铺</strong>：配置 AppKey、AppSecret、Access Token、Refresh Token 等凭证信息</li>
            <li><strong>即将过期</strong>：Token 在 7 天内将过期，建议及时刷新</li>
        </ul>
    </div>
</div>
@endsection
