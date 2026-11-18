@extends('core::layouts.base')

@section('title', '无访问权限')

@section('content')
<div class="dw-card" style="max-width:640px;margin:8vh auto;text-align:center">
    <div style="font-size:40px;line-height:1;margin-bottom:12px" aria-hidden="true">🔒</div>
    <h1 class="dw-page-title" style="margin-bottom:8px">403 · 无访问权限</h1>
    <p style="color:var(--dw-muted);margin-bottom:20px">
        {{ isset($exception) ? ($exception->getMessage() ?: '您当前没有访问该页面的权限，请联系管理员开通。') : '您当前没有访问该页面的权限，请联系管理员开通。' }}
    </p>
    <div class="d-flex gap-2 justify-content-center">
        <a class="btn btn-primary" href="/">返回门户</a>
        <a class="btn btn-outline-secondary" href="/profile">个人中心</a>
    </div>
</div>
@endsection
