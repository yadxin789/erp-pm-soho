<?php

namespace App\Erp\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCenter extends Model
{
    protected $table = 'erp_work_centers';

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'department_id', 'work_center_type', 'active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function productionLines()
    {
        return $this->hasMany(ProductionLine::class);
    }

    public function productionOrders()
    {
        return $this->hasMany(\App\Erp\Models\Production\ProductionOrder::class);
    }

    public function routingOperations()
    {
        return $this->hasMany(\App\Erp\Models\Production\RoutingOperation::class);
    }
}
