<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory, HasSpatial;

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'barangay_id',
        'emergency_type',
        'for_whom',
        'description',
        'casualties',
        'location',
        'visibility',
        'image',
        'status',
    ];


    protected $casts = [
        'location' => Point::class,
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barangays()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }
}