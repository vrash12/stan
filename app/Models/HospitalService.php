<?php
// app/Models/HospitalService.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalService extends Model
{
    protected $table = 'hospital_services';
    protected $primaryKey = 'service_id';
    public $timestamps = false; // adjust if you have created_at/updated_at

    protected $fillable = [
        'service_name',
        'department_id',
        'price',
        'description',
        // (no quantity here if you use the stock‐movements approach)
    ];

    public function department()
    {
        return $this->belongsTo(Department::class,'department_id','department_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(MedicineStockMovement::class,'service_id','service_id');
    }
}
