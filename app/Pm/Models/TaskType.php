<?php
namespace App\Pm\Models;
use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    protected $table = "task_types";
    protected $fillable = ["name","description","department_id","is_required","sort_order"];
    protected $casts = ["is_required"=>"boolean"];
}