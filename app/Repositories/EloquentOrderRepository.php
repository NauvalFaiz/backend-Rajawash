<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findByToken(string $token): ?Order
    {
        return Order::where('payment_token', $token)->first();
    }

    public function updatePaymentStatus(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }
}
