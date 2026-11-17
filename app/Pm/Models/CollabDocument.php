<?php
namespace App\Pm\Models;
use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollabDocument extends Model
{
    use SoftDeletes;
    protected $table = "collab_documents";
    protected $fillable = ["project_id","name","extension","storage_path","is_temp_demo","expires_at","doc_key","size","version","created_by","updated_by"];
    protected $casts = ["is_temp_demo"=>"boolean","expires_at"=>"datetime"];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, "created_by"); }
}