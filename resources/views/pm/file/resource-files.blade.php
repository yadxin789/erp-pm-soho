@extends('pm::layouts.app')

@section('title', '资料文件')

@section('content')
<h1 class="mb-4">资料文件</h1>

<a href="{{ route('pm.file.upload-department') }}" class="btn btn-primary mb-4">上传资料文件</a>

@include('pm::file.partials.resource-file-tabs', [
    'categoryFiles' => $categoryFiles,
    'tabIdPrefix' => 'resource-files',
    'canManage' => false,
])
@endsection
