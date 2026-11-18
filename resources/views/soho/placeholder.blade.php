@extends('soho::layouts.app')

@section('title', $title ?? '功能开发中')

@section('content')
{{-- 正在开发中占位页面：使用主布局，保持侧边栏导航一致性 --}}
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                {{-- 顶部图标与状态 --}}
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle"
                         style="width: 96px; height: 96px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fa fa-wrench text-white" style="font-size: 42px;"></i>
                    </div>
                </div>

                <h4 class="fw-bold mb-2">{{ $title ?? '功能开发中' }}</h4>
                <p class="text-muted mb-3">该模块正在紧锣密鼓地开发中，敬请期待</p>

                {{-- 模块信息卡片 --}}
                <div class="bg-light rounded-3 py-3 px-4 mb-4">
                    <div class="text-muted small mb-1">当前模块</div>
                    <div class="fw-semibold text-primary" style="font-size: 1.05rem;">
                        <i class="fa fa-cube me-1"></i>{{ $feature ?? '未知功能' }}
                    </div>
                </div>

                {{-- 状态徽章 --}}
                <div class="mb-4">
                    <span class="badge bg-warning text-dark px-3 py-2">
                        <i class="fa fa-spinner fa-spin me-1"></i>{{ $status ?? '开发中' }}
                    </span>
                    @if($module ?? null)
                        <span class="badge bg-light text-dark border px-3 py-2 ms-1">
                            <i class="fa fa-tag me-1"></i>{{ $module }}
                        </span>
                    @endif
                </div>

                {{-- 开发计划提示 --}}
                <div class="row text-start mt-4">
                    <div class="col-md-6 mb-3">
                        <div class="border-start border-primary border-3 ps-3">
                            <div class="fw-semibold small text-dark">规划阶段</div>
                            <div class="text-muted small">需求分析与方案设计</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="border-start border-primary border-3 ps-3">
                            <div class="fw-semibold small text-dark">开发阶段</div>
                            <div class="text-muted small">前后端编码实现</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="border-start border-warning border-3 ps-3">
                            <div class="fw-semibold small text-dark">测试阶段</div>
                            <div class="text-muted small">功能测试与回归验证</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="border-start border-secondary border-3 ps-3">
                            <div class="fw-semibold small text-dark">上线阶段</div>
                            <div class="text-muted small">灰度发布与全量上线</div>
                        </div>
                    </div>
                </div>

                {{-- 返回按钮 --}}
                <div class="mt-4">
                    <a href="{{ route('soho.dashboard') }}" class="btn btn-primary">
                        <i class="fa fa-home me-1"></i>返回首页
                    </a>
                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="javascript:history.back()">
                        <i class="fa fa-arrow-left me-1"></i>返回上一页
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
