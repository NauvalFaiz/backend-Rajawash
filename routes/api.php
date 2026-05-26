<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\VoucherPromoController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sini kita memisahkan antara API untuk Web (Owner/Admin) 
| dan API untuk Mobile (User/Kurir)
|
*/

// ========================================================================
// 📱 MOBILE API (KHUSUS USER & KURIR)
// ========================================================================
Route::prefix('mobile')->group(function () {
    // Public Mobile
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);
    Route::post('/register', [AuthController::class, 'registerUser']);
    Route::post('/register/courier', [AuthController::class, 'registerCourier']);

    // Protected Mobile
    Route::middleware('auth:sanctum')->group(function () {
        // Auth Me
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // User (Customer)
        Route::post('/orders', [OrderController::class, 'createOrder']);
        Route::get('/my-orders', [OrderController::class, 'myOrders']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::get('/reviews/{owner_id}', [ReviewController::class, 'index']);

        // Payment
        Route::post('/orders/{id}/payment-url', [PaymentController::class, 'generateUrl']);
        Route::get('/orders/{id}/payment-status', [PaymentController::class, 'status']);

        // Courier (Kurir)
        Route::prefix('courier')->group(function () {
            Route::post('/step', [CourierController::class, 'updateStep']);
            Route::post('/weight', [CourierController::class, 'inputWeight']);
            Route::post('/delivery-back', [CourierController::class, 'deliveryBack']);
            Route::post('/orders/{id}/confirm', [CourierController::class, 'confirmPayment']);
        });

        // Shared Mobile Profile
        Route::get('/user', [ProfileController::class, 'profile']);
        Route::put('/user', [ProfileController::class, 'update']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/fcm-token', [NotificationController::class, 'updateFcmToken']);

        // Voucher, Promo, Membership
        Route::get('/promos', [VoucherPromoController::class, 'promos']);
        Route::post('/voucher/validate', [VoucherPromoController::class, 'validateVoucher']);
        Route::get('/memberships', [VoucherPromoController::class, 'memberships']);
        Route::get('/my-membership', [VoucherPromoController::class, 'myMembership']);

        // Services listing (for customer ordering)
        Route::get('/services', function () {
            return response()->json([
                'success' => true,
                'data' => \App\Models\Service::with('owner')->where('is_active', true)->get()
            ]);
        });

        // Laundries listing
        Route::get('/laundries', function () {
            return response()->json([
                'success' => true,
                'data' => \App\Models\Owner::where('status', 'approved')->get()
            ]);
        });
    });
});


// ========================================================================
// 💻 WEB API (KHUSUS OWNER & ADMIN)
// ========================================================================
// Public Web
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/owner', [AuthController::class, 'registerOwner']);
Route::post('/register/courier', [AuthController::class, 'registerCourier']);

// Protected Web
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile Web
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [ProfileController::class, 'profile']);
    Route::put('/user', [ProfileController::class, 'update']);

    // Notifications Web
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/fcm-token', [NotificationController::class, 'updateFcmToken']);

    // Orders (shared)
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{owner_id}', [ReviewController::class, 'index']);

    // Courier Routes
    Route::prefix('courier')->group(function () {
        Route::get('/orders', [CourierController::class, 'orders']);
        Route::post('/assign', [CourierController::class, 'assignOrder']);
        Route::post('/step', [CourierController::class, 'updateStep']);
        Route::post('/weight', [CourierController::class, 'inputWeight']);
        Route::post('/delivery-back', [CourierController::class, 'deliveryBack']);
        Route::post('/orders/{id}/confirm', [CourierController::class, 'confirmPayment']);
    });

    // Owner Routes
    Route::prefix('owner')->group(function () {
        Route::get('/dashboard', [OwnerController::class, 'dashboard']);
        Route::get('/orders', [OwnerController::class, 'orders']);
        Route::post('/orders/{id}/receive', [OwnerController::class, 'receiveOrder']);
        Route::post('/orders/{id}/confirm', [OwnerController::class, 'confirmPayment']);
        Route::post('/orders/{id}/status', [OwnerController::class, 'updateOrderStatus']);
        Route::get('/services', [OwnerController::class, 'getServices']);
        Route::post('/services', [OwnerController::class, 'addService']);
        Route::put('/services/{id}', [OwnerController::class, 'updateService']);
        Route::post('/orders/offline', [OwnerController::class, 'createOfflineOrder']);
        Route::delete('/orders/{id}', [OwnerController::class, 'deleteOrder']);
        Route::get('/customers', [OwnerController::class, 'getCustomers']);
        Route::put('/customers/{id}/discount', [OwnerController::class, 'updateCustomerDiscount']);
    });

    // Admin Routes
    Route::prefix('admin')->group(function () {
        Route::post('/laundry', [AdminController::class, 'addLaundry']);
        Route::get('/laundries', [AdminController::class, 'getLaundries']);
        Route::post('/laundries/{id}/approve', [AdminController::class, 'approveOwner']);
        Route::post('/couriers/{id}/approve', [AdminController::class, 'approveCourier']);
        Route::get('/orders', [AdminController::class, 'monitorOrders']);
        Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
        Route::get('/couriers/active', [AdminController::class, 'monitorCouriers']);
        Route::post('/services', [AdminController::class, 'addService']);
        Route::get('/couriers', [AdminController::class, 'getCouriers']);
    });
});
