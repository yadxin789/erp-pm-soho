<?php
namespace App\Pm\Models;
use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectMessage extends Model
{
    use SoftDeletes;
    protected $table = "project_messages";
    protected $fillable = ["project_id","user_id","receiver_id","is_private","content","file_path","file_name"];
    protected $casts = ["is_private"=>"boolean"];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, "user_id"); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, "receiver_id"); }
}