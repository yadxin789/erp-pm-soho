<?php
namespace App\Pm\Models;
use App\Core\Models\Department;
use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;
    protected $table = "tasks";
    protected $fillable = ["project_id","department_id","name","executor_id","auditor_id","start_date","end_date","status","task_type_id","is_overdue","extension_days","paused_at","completed_at"];
    protected $casts = ["start_date"=>"date","end_date"=>"date","completed_at"=>"datetime","paused_at"=>"datetime","is_overdue"=>"boolean"];

    public const STATUS_LABELS = ["pending"=>"待处理","for_audit"=>"待审核","completed"=>"已完成","rejected"=>"已驳回","overdue"=>"已逾期"];
    public const STATUS_COLORS = ["pending"=>"#ffc107","for_audit"=>"#0d6efd","completed"=>"#28a745","rejected"=>"#dc3545","overdue"=>"#fd7e14"];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function executor(): BelongsTo { return $this->belongsTo(User::class, "executor_id"); }
    public function auditor(): BelongsTo { return $this->belongsTo(User::class, "auditor_id"); }
    public function taskType(): BelongsTo { return $this->belongsTo(TaskType::class); }

    public function statusLabel(): string { return self::STATUS_LABELS[$this->status] ?? $this->status; }
    public function statusColor(): string { return self::STATUS_COLORS[$this->status] ?? "#6c757d"; }
}