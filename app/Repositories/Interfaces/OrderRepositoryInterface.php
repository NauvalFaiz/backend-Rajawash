<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;

interface OrderRepositoryInterface
{
    public function findByToken(string $token): ?Order;
    public function updatePaymentStatus(Order $order, array $data): bool;
    public function findById(int $id): ?Order;
}
