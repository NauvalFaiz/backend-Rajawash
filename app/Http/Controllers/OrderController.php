<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:owners,id',
            'service_id' => 'required|exists:services,id',
            'laundry_location' => 'required|string',
            'delivery_type' => 'nullable|in:standart,hemat,kilat',
            'return_method' => 'nullable|in:delivery,self',
            'pickup_type' => 'required|in:pickup,self',
            'payment_method' => 'required|in:qris,tunai',
            'items' => 'required|array',
            'items.*.service_name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.qty' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $total_price = 0;
            foreach ($request->items as $item) {
                $total_price += ($item['price'] * $item['qty']);
            }

            $user = Auth::user();
            $levelDiscountPercent = $user->level_discount;
            $manualDiscountPercent = $user->manual_discount;
            $totalDiscountPercent = $levelDiscountPercent + $manualDiscountPercent;
            
            $discountAmount = ($total_price * $totalDiscountPercent) / 100;
            $finalPrice = $total_price - $discountAmount;

            $order = Order::create([
                'user_id' => Auth::id(),
                'owner_id' => $request->owner_id,
                'service_id' => $request->service_id,
                'laundry_location' => $request->laundry_location,
                'delivery_type' => $request->delivery_type,
                'return_method' => $request->return_method ?? 'delivery',
                'pickup_type' => $request->pickup_type,
                'payment_method' => $request->payment_method,
                'payment_code' => Str::uuid(),
                'discount' => $discountAmount,
                'total_price' => $finalPrice,
                'status' => 'pending',
                'payment_status' => 'unpaid'
            ]);

            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'service_name' => $item['service_name'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'data' => $order->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function myOrders(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Order::query()->where('user_id', Auth::id())->with('items')->get()
        ]);
    }
}