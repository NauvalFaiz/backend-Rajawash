<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'owner_id',
        'service_id',
        'laundry_location',
        'delivery_type',
        'pickup_type',
        'payment_method',
        'payment_code',
        'promo_code',
        'discount',
        'total_price',
        'status',
        'payment_status',
        'image_url',
        'points_granted',
        'payment_token',
        'payment_token_expires_at',
        'paid_at',
        'payment_device_info',
        'payment_ip_address'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function courierTask()
    {
        return $this->hasOne(CourierTask::class);
    }

    public function histories()
    {
        return $this->hasMany(OrderHistory::class);
    }
}