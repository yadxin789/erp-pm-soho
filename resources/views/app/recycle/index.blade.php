@extends('layouts.business')

@section('title', '回收站')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="page-title mb-0"><i class="fa-regular fa-trash-can"></i>回收站</h2>
            <div class="small text-muted">删除的记录可恢复，确认无用后可彻底删除</div>
        </div>
        <a href="{{ erp_route('app.projects.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>项目列表</a>
    </div>

    @foreach($groups as $key => $group)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>{{ $group['label'] }}</span>
                <span class="badge bg-secondary">{{ $group['rows']->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>记录</th><th>删除时间</th><th class="text-end">操作</th></tr></thead>
                    <tbody>
                    @forelse($group['rows'] as $row)
                        <tr>
                            <td class="small">
                                @php
                                    $label = match ($key) {
                                        'projects' => $row->name,
                                        'tasks' => $row->name,
                                        'files' => $row->original_name ?? $row->stored_path,
                                        'messages' => mb_substr((string) ($row->content ?: $row->file_name), 0, 40),
                                        'collab' => $row->title,
                                        'department-files' => $row->original_name,
                                        'resources' => $row->title,
                                        default => '#' . $row->id,
                                    };
                                @endphp
                                {{ $label }}
                            </td>
                            <td class="small text-muted">{{ optional($row->deleted_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ erp_route('app.recycle.restore', [$key, $row->id]) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-success btn-sm">恢复</button>
                                </form>
                                <form method="POST" action="{{ erp_route('app.recycle.force-delete', [$key, $row->id]) }}" class="d-inline" onsubmit="return confirm('彻底删除不可恢复，确定？')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">彻底删除</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted small text-center py-3">暂无</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endsection
