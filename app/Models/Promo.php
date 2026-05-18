<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_url',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function isActive(): bool
    {
        $now = now();
        if ($this->start_date && $now->isBefore($this->start_date)) {
            return false;
        }
        if ($this->end_date && $now->isAfter($this->end_date)) {
            return false;
        }
        return true;
    }
}
