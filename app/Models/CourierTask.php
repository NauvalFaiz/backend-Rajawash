<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierTask extends Model
{
    protected $fillable = [
        'order_id',
        'courier_id',
        'weight',
        'price_per_kg',
        'total_price',
        'payment_code',
        'qr_code',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
}