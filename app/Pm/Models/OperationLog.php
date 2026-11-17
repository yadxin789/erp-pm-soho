<?php

namespace App\Pm\Models;

use Illuminate\Database\Eloquent\Model;

class OperationLog extends Model
{
    protected $fillable = ['user_id', 'ip', 'module', 'action', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}