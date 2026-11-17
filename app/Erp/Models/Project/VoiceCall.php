<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

/** 电话会议记录 */
class VoiceCall extends Model
{
    protected $table = 'erp_voice_calls';

    protected $fillable = [
        'project_id', 'room', 'started_by', 'started_at', 'ended_at', 'duration_seconds', 'participants', 'demo_file',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'participants' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function starter()
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function getDurationLabelAttribute(): string
    {
        $s = (int) $this->duration_seconds;

        return sprintf('%02d:%02d:%02d', intdiv($s, 3600), intdiv($s % 3600, 60), $s % 60);
    }
}
