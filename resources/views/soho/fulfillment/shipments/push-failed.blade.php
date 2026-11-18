@extends('soho::layouts.app')

@section('title', '推送失败发货列表')

@section('content')
{{-- 面包屑导航 --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('soho.dashboard') }}">首页</a></li>
        <li class="breadcrumb-item"><a href="#">履约管理</a></li>
        <li class="breadcrumb-item active">推送失败发货</li>
    </ol>
</nav>

{{-- 页面标题 --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa fa-exclamation-circle me-2"></i>推送失败发货列表</h4>
    <div>
        <a href="{{ route('soho.shipment.pending') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-clock me-1"></i>待发货包裹
        </a>
        <button type="button" class="btn btn-danger btn-sm" onclick="retryAll()">
            <i class="fa fa-redo me-1"></i>一键重试全部
        </button>
    </div>
</div>

{{-- 失败统计卡片 --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">平台接口错误</div>
                <div class="fs-3 text-danger">{{ $stats['api_error'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">数据校验失败</div>
                <div class="fs-3 text-warning">{{ $stats['validation_error'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">网络超时</div>
                <div class="fs-3 text-warning">{{ $stats['timeout'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">认证失败</div>
                <div class="fs-3 text-danger">{{ $stats['auth_error'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

{{-- 推送失败列表 --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">推送失败包裹</h5>
        <span class="badge bg-danger">{{ $pushFailed->total() }} 条失败记录</span>
    </div>
    <div class="card-body">
        {{-- 筛选表单 --}}
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="包裹号/物流单号" value="{{ request('search') }}">
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
                    <select name="error_type" class="form-select">
                        <option value="">全部错误类型</option>
                        <option value="api_error" @selected(request('error_type') == 'api_error')>平台接口错误</option>
                        <option value="validation_error" @selected(request('error_type') == 'validation_error')>数据校验失败</option>
                        <option value="timeout" @selected(request('error_type') == 'timeout')>网络超时</option>
                        <option value="auth_error" @selected(request('error_type') == 'auth_error')>认证失败</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search me-1"></i>筛选
                    </button>
                    <a href="{{ route('soho.shipment.push.failed') }}" class="btn btn-outline-secondary">重置</a>
                </div>
            </div>
        </form>

        {{-- 数据表格 --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>包裹号</th>
                        <th>订单号</th>
                        <th>平台</th>
                        <th>快递公司</th>
                        <th>物流单号</th>
                        <th>错误类型</th>
                        <th>错误信息</th>
                        <th>重试次数</th>
                        <th>最后失败时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pushFailed as $parcel)
                    <tr>
                        <td><code>{{ $parcel->parcel_no }}</code></td>
                        <td>{{ $parcel->order_no ?? '-' }}</td>
                        <td>
                            @if($parcel->platform == 'taobao')
                                <span class="badge bg-warning text-dark">淘宝</span>
                            @elseif($parcel->platform == 'jd')
                                <span class="badge bg-danger">京东</span>
                            @elseif($parcel->platform == 'pinduoduo')
                                <span class="badge bg-info">拼多多</span>
                            @elseif($parcel->platform == 'amazon')
                                <span class="badge bg-success">亚马逊</span>
                            @else
                                <span class="badge bg-secondary">{{ $parcel->platform }}</span>
                            @endif
                        </td>
                        <td>{{ $parcel->express_company_name ?? '-' }}</td>
                        <td>{{ $parcel->tracking_no ?? '-' }}</td>
                        <td>
                            @if($parcel->error_type == 'api_error')
                                <span class="badge bg-danger">接口错误</span>
                            @elseif($parcel->error_type == 'validation_error')
                                <span class="badge bg-warning text-dark">校验失败</span>
                            @elseif($parcel->error_type == 'timeout')
                                <span class="badge bg-warning text-dark">超时</span>
                            @elseif($parcel->error_type == 'auth_error')
                                <span class="badge bg-danger">认证失败</span>
                            @else
                                <span class="badge bg-secondary">{{ $parcel->error_type }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ \Illuminate\Support\Str::limit($parcel->error_message ?? '-', 50) }}</td>
                        <td>{{ $parcel->retry_count ?? 0 }} 次</td>
                        <td>{{ optional($parcel->last_failed_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-success" onclick="retryPush({{ $parcel->id }})">
                                <i class="fa fa-redo"></i> 重试
                            </button>
                            <a href="{{ route('soho.shipment.pending', $parcel) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> 详情
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fa fa-check-circle fa-2x mb-2 d-block"></i>
                            暂无推送失败的包裹
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 分页 --}}
        <div class="d-flex justify-content-center">
            {{ $pushFailed->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function retryPush(id) {
    if (confirm('确认重新推送该包裹？')) {
        // 重试推送逻辑
    }
}

function retryAll() {
    if (confirm('确认重试全部失败的包裹？')) {
        // 批量重试逻辑
    }
}
</script>
@endsection