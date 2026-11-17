<?php

namespace App\Pm\Http\Controllers;

use App\Pm\Models\Project;
use App\Pm\Models\ProjectLog;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 显示项目履历页面
     */
    public function projectHistory(Project $project)
    {
        // 获取项目的所有日志记录
        $logs = ProjectLog::where('project_id', $project->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('pm::history.project-history', compact('project', 'logs'));
    }
}
