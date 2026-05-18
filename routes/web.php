<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PaymentWebController;

Route::get('/', function () {
    return view('welcome');
});

// QR Payment - When scanned, this URL opens in the browser
Route::get('/payment/pay/{token}', [PaymentWebController::class, 'pay'])->name('payment.pay');
