<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'name',
        'discount_amount',
        'discount_percentage',
        'expires_at',
        'usage_limit',
        'usage_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isValid(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->usage_count >= $this->usage_limit) {
            return false;
        }
        return true;
    }
}
