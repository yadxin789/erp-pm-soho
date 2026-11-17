<?php

namespace App\Erp\Models\Project;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** 项目组内私聊消息 */
class PrivateMessage extends Model
{
    use SoftDeletes;

    protected $table = 'project_private_messages';

    protected $fillable = [
        'project_id', 'sender_id', 'receiver_id', 'content', 'file_name', 'file_path', 'file_size', 'read_at',
    ];

    protected $casts = ['read_at' => 'datetime'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
