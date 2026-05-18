<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourierTask;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CourierController extends Controller
{
    // List semua pesanan yang tersedia untuk kurir + tugas kurir sendiri
    public function orders()
    {
        $courierId = Auth::id();

        // Pesanan pending (belum diambil siapapun) + pesanan yang diambil kurir ini
        $orders = Order::with(['service', 'user'])
            ->where(function ($q) use ($courierId) {
                $q->where('status', 'pending') // Available
                  ->orWhereHas('courierTask', function ($q2) use ($courierId) {
                      $q2->where('courier_id', $courierId);
                  });
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $orders]);
    }


    // 1. Ambil Orderan (Status: pickup)
    public function assignOrder(Request $request)
    {
        $request->validate(['order_id' => 'required|exists:orders,id']);
        
        $order = Order::findOrFail($request->order_id);
        $order->update(['status' => 'pickup']);

        $task = CourierTask::create([
            'order_id' => $order->id,
            'courier_id' => Auth::id(),
            'status' => 'pickup'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil diambil, silakan menuju lokasi user',
            'data' => $task
        ]);
    }

    // 2. Update Status Perjalanan (pickup -> weighing -> to_laundry)
    public function updateStep(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:weighing,to_laundry'
        ]);

        $order = Order::query()->where('id', $request->order_id)->firstOrFail();
        $order->update(['status' => $request->status]);

        $task = CourierTask::query()->where('order_id', $request->order_id)->first();
        if ($task) {
            $task->update(['status' => $request->status]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui ke ' . $request->status
        ]);
    }

    // 3. Input Berat (Harga otomatis ambil dari layanan)
    public function inputWeight(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'weight' => 'required|numeric',
        ]);

        $order = Order::with('service')->findOrFail($request->order_id);
        $price_per_unit = $order->service->price; // Ambil harga asli dari layanan
        $total = $request->weight * $price_per_unit;

        $order->update([
            'total_price' => $total, // Mengatur total harga berdasarkan berat asli
            'status' => 'to_laundry'
        ]);

        $task = CourierTask::query()->where('order_id', $request->order_id)->first();
        if ($task) {
            $task->update([
                'weight' => $request->weight,
                'price_per_kg' => $price_per_unit,
                'total_price' => $total,
                'status' => 'to_laundry'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berat berhasil diinput. Total biaya: Rp ' . number_format($total, 0, ',', '.')
        ]);
    }

    // 4. Pengantaran Balik (done -> delivery_back -> shipped -> completed)
    public function deliveryBack(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:delivery_back,shipped,completed'
        ]);

        $order = Order::query()->where('id', $request->order_id)->firstOrFail();
        $order->update(['status' => $request->status]);

        if ($request->status === 'completed' && $order->user && !$order->points_granted) {
            $pointsEarned = floor($order->total_price / 1000); // 1 point per 1000 IDR
            $order->user->increment('points', $pointsEarned);
            $order->user->updateLevel();

            $order->update(['points_granted' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pengantaran balik: ' . $request->status
        ]);
    }

    // 5. Konfirmasi Pembayaran oleh Kurir
    public function confirmPayment($id)
    {
        $order = Order::findOrFail($id);
        $order->update([
            'payment_status' => 'paid'
        ]);

        return response()->json(['success' => true, 'message' => 'Pembayaran berhasil dikonfirmasi oleh kurir']);
    }
}
