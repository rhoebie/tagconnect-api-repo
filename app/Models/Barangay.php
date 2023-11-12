<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Barangay extends Model
{
    use HasFactory, HasSpatial;

    protected $fillable = [
        'moderator_id',
        'name',
        'district',
        'contact',
        'address',
        'location',
        'image'
    ];
    protected $casts = [
        'location' => Point::class,
    ];

    public $timestamps = true;

    public function reports()
    {
        return $this->hasMany(Report::class, 'barangay_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}