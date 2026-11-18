<?php

namespace App\Soho\Http\Controllers\Log;

use App\Soho\Http\Controllers\Controller;
use App\Soho\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * 审计日志列表
     */
    public function index(Request $request)
    {
        $auditLogs = AuditLog::when($request->search, function($query) use ($request) {
                $query->where('action', 'like', "%{$request->search}%")
                    ->orWhere('model_type', 'like', "%{$request->search}%")
                    ->orWhere('model_id', 'like', "%{$request->search}%");
            })
            ->when($request->user_id, function($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when($request->action, function($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->when($request->model_type, function($query) use ($request) {
                $query->where('model_type', $request->model_type);
            })
            ->when($request->date_start, function($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_start);
            })
            ->when($request->date_end, function($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_end);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('log.audit', compact('auditLogs'));
    }
}