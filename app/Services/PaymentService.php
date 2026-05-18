<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentService
{
    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function generatePaymentToken(Order $order)
    {
        $token = Str::random(40);
        $expiresAt = Carbon::now()->addMinutes(30); // 30 minutes expiry

        $this->orderRepository->updatePaymentStatus($order, [
            'payment_token' => $token,
            'payment_token_expires_at' => $expiresAt,
            'payment_status' => 'unpaid'
        ]);

        return [
            'token' => $token,
            'expires_at' => $expiresAt,
            'payment_url' => url("/payment/pay/{$token}")
        ];
    }

    public function processPayment(string $token, array $metadata)
    {
        $order = $this->orderRepository->findByToken($token);

        if (!$order) {
            return ['status' => 'error', 'message' => 'Invalid payment'];
        }

        if ($order->payment_status === 'paid') {
            return ['status' => 'error', 'message' => 'Payment already completed'];
        }

        if (Carbon::now()->isAfter($order->payment_token_expires_at)) {
            return ['status' => 'error', 'message' => 'QR expired'];
        }

        // Process success
        $this->orderRepository->updatePaymentStatus($order, [
            'payment_status' => 'paid',
            'status' => 'paid',
            'paid_at' => Carbon::now(),
            'payment_device_info' => $metadata['device_info'] ?? null,
            'payment_ip_address' => $metadata['ip_address'] ?? null,
        ]);

        // Award points based on weight/price
        $user = $order->user;
        if ($user) {
            // Points already calculated in order creation usually, but we ensure it here
            $user->points += $order->points_granted;
            $user->save();
            $user->updateLevel();
        }

        return ['status' => 'success', 'message' => 'Payment success', 'order' => $order];
    }
}
