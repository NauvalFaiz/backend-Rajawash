<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'unit_type',
        'price',
        'is_active',
        'image_url'
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
