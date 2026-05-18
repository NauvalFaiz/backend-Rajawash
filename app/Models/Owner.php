<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Owner extends Authenticatable
{
    use HasApiTokens;
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'laundry_name',
        'laundry_address',
        'phone',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['role'];

    public function getRoleAttribute()
    {
        return 'owner';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}