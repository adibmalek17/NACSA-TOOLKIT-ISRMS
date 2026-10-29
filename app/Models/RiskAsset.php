<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'department_id',
        'code',
        'name',
        'serial_number',
        'quantity',
        'category',
        'description',
        'owner',
        'location',
    ];
}