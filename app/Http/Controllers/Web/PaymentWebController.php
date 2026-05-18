<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentWebController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle the QR scan URL.
     */
    public function pay(Request $request, $token)
    {
        $metadata = [
            'device_info' => $request->header('User-Agent'),
            'ip_address' => $request->ip(),
        ];

        $result = $this->paymentService->processPayment($token, $metadata);

        if ($result['status'] === 'success') {
            return view('payment.status', [
                'success' => true,
                'message' => $result['message'],
                'order' => $result['order']
            ]);
        }

        return view('payment.status', [
            'success' => false,
            'message' => $result['message'],
            'order' => null
        ]);
    }
}
