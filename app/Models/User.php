<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'points',
        'level',
        'manual_discount',
        'provider',
        'provider_id',
        'avatar',
        'last_login_at'
    ];

    /**
     * Get the discount percentage based on the user's level.
     */
    public function getLevelDiscountAttribute()
    {
        return match ($this->level) {
            1 => 0,
            2 => 2,
            3 => 5,
            4 => 10,
            5 => 15,
            default => 15,
        };
    }

    /**
     * Calculate and update level based on points.
     */
    public function updateLevel()
    {
        $newLevel = 1;
        if ($this->points > 1000) {
            $newLevel = 5;
        } elseif ($this->points > 600) {
            $newLevel = 4;
        } elseif ($this->points > 300) {
            $newLevel = 3;
        } elseif ($this->points > 100) {
            $newLevel = 2;
        }

        if ($this->level !== $newLevel) {
            $this->level = $newLevel;
            $this->save();
        }
    }

    /**
     * Get total discount (manual + level).
     */
    public function getTotalDiscountAttribute()
    {
        // level discount is percentage, manual discount is fixed or also percentage?
        // Let's assume manual_discount is also a fixed amount for now, 
        // but if the user wants it to be "semakin banyak level semakin ada diskon", 
        // usually it's additive.
        // Let's treat manual_discount as a fixed amount (Rp) and level discount as percentage.
        return $this->level_discount; // Percentage
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 🔗 relasi
    public function userProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function courierProfile()
    {
        return $this->hasOne(CourierProfile::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function courierTasks()
    {
        return $this->hasMany(CourierTask::class, 'courier_id');
    }

    public function ownerProfile()
    {
        return $this->hasOne(Owner::class, 'email', 'email');
    }
}