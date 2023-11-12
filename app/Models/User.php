<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    protected $fillable = [
        'role_id',
        'firstname',
        'middlename',
        'lastname',
        'age',
        'birthdate',
        'contactnumber',
        'address',
        'email',
        'password',
        'image',
    ];

    public $timestamps = true;

    protected $casts = [
        'birthdate' => 'date:Y-m-d',
    ];

    protected $hidden = [
        'password',
        'verification_code',
        'password_reset_token',
        'fCMToken',
        'email_verified_at',
        'last_code_request'
    ];

    public function reports()
    {
        return $this->hasMany(Report::class, 'user_id');
    }

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function barangays()
    {
        return $this->hasOne(Barangay::class, 'moderator_id');
    }

    public function isAdmin()
    {
        return $this->roles->name === 'Admin';
    }

    public function isModerator()
    {
        return $this->roles->name === 'Moderator';
    }

    public function isUser()
    {
        return $this->roles->name === 'User';
    }
}