@extends('pm::layouts.app')

@section('title', '协同办公 · '.$doc->name)

@section('content')
@include('pm::project.partials.tabs', ['project' => $project, 'activeTab' => 'collab'])

<div class="pm-panel">
    <div class="pm-panel-head">
        <h3><i class="fas fa-file-alt" aria-hidden="true"></i>{{ $doc->name }}（v{{ $doc->version }}）</h3>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('pm.project.collab.index', $project) }}">返回列表</a>
    </div>
    <div class="pm-panel-body">
        @if ($editorUrl)
            <iframe src="{{ $editorUrl }}" style="width:100%;height:70vh;border:0" title="协作文档编辑器"></iframe>
        @else
            <div class="pm-empty">
                <i class="fas fa-plug" aria-hidden="true"></i>
                未配置 OnlyOffice 文档服务（`ONLYOFFICE_URL` 为空），当前仅保留文档记录；配置后可在此打开在线协作编辑器。
            </div>
        @endif
    </div>
</div>
@endsection
