<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OwnerController extends Controller
{
    // lihat order
    public function orders()
    {
        return Order::query()->where('owner_id', Auth::id())->get();
    }

    // 1. Konfirmasi Terima dari Kurir (Status: received)
    public function receiveOrder($id)
    {
        $order = Order::query()->where('owner_id', Auth::id())->findOrFail($id);
        $order->update(['status' => 'received']);

        return response()->json([
            'success' => true,
            'message' => 'Cucian berhasil diterima di toko'
        ]);
    }

    // 2. Update Status Laundry (received -> process -> done)
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:process,done,completed']);
        
        $order = Order::query()->where('owner_id', Auth::id())->findOrFail($id);
        $order->update(['status' => $request->status]);

        if ($request->status === 'done') {
            $this->grantPoints($order);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status laundry berhasil diubah ke ' . $request->status
        ]);
    }

    // 3. Konfirmasi Pembayaran
    public function confirmPayment($id)
    {
        $order = Order::findOrFail($id);

        $order->update([
            'payment_status' => 'paid'
        ]);

        $this->grantPoints($order);

        return response()->json(['message' => 'Pembayaran dikonfirmasi']);
    }

    // Dashboard Owner
    public function dashboard()
    {
        $ownerId = Auth::id();

        /** @var \Illuminate\Database\Eloquent\Builder $q1 */
        $q1 = Order::query()->where('owner_id', $ownerId);
        $totalOrders = $q1->count();
        
        /** @var \Illuminate\Database\Eloquent\Builder $q2 */
        $q2 = Order::query()->where('owner_id', $ownerId)->where('payment_status', 'paid');
        $totalRevenue = $q2->sum('total_price');

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = Order::query()->where('owner_id', $ownerId);
        $activeOrders = $query->whereNotIn('status', ['completed', 'cancel'])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'active_orders' => $activeOrders
            ]
        ]);
    }

    // Owner CRUD Services
    public function getServices()
    {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Service::query()->where('owner_id', Auth::id())->get()
        ]);
    }

    public function addService(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'unit_type' => 'required|in:kg,pcs',
            'price' => 'required|numeric',
            'image_url' => 'nullable|string'
        ]);

        $service = \App\Models\Service::create([
            'owner_id' => Auth::id(),
            'name' => $request->name,
            'unit_type' => $request->unit_type,
            'price' => $request->price,
            'image_url' => $request->image_url,
            'is_active' => true
        ]);

        return response()->json(['message' => 'Layanan berhasil ditambahkan.', 'data' => $service]);
    }

    public function updateService(Request $request, $id)
    {
        $service = \App\Models\Service::query()->where('owner_id', Auth::id())->findOrFail($id);
        $service->update($request->only(['name', 'unit_type', 'price', 'is_active', 'image_url']));

        return response()->json(['message' => 'Layanan berhasil diupdate.', 'data' => $service]);
    }

    public function createOfflineOrder(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'weight' => 'required|numeric',
            'payment_method' => 'required|in:tunai,qris,loan',
            'image_url' => 'nullable|string',
            'user_phone' => 'nullable|string',
            'manual_discount' => 'nullable|numeric|min:0'
        ]);

        $service = \App\Models\Service::findOrFail($request->service_id);
        $total = $request->weight * $service->price;
        
        $userId = null;
        $discountAmount = 0;

        if ($request->user_phone) {
            $user = \App\Models\User::where('phone', $request->user_phone)->first();
            if ($user) {
                $userId = $user->id;
                $levelDiscountPercent = $user->level_discount;
                $manualDiscountPercent = $user->manual_discount;
                $totalDiscountPercent = $levelDiscountPercent + $manualDiscountPercent;
                
                $discountAmount = ($total * $totalDiscountPercent) / 100;
                $total = $total - $discountAmount;
            }
        }

        // Tambahkan diskon manual yang diinput owner (fixed amount)
        if ($request->manual_discount) {
            $discountAmount += $request->manual_discount;
            $total = max(0, $total - $request->manual_discount);
        }

        $order = Order::create([
            'user_id' => $userId,
            'owner_id' => Auth::id(),
            'service_id' => $request->service_id,
            'laundry_location' => 'Offline',
            'pickup_type' => 'self',
            'payment_method' => $request->payment_method,
            'payment_code' => 'OFF-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'discount' => $discountAmount,
            'total_price' => $total,
            'status' => 'received', 
            'payment_status' => 'unpaid',
            'image_url' => $request->image_url
        ]);

        return response()->json(['success' => true, 'message' => 'Pesanan offline berhasil dibuat', 'data' => $order]);
    }

    public function deleteOrder($id)
    {
        $order = Order::query()->where('owner_id', Auth::id())->findOrFail($id);
        $order->delete();
        return response()->json(['success' => true, 'message' => 'Pesanan berhasil dihapus']);
    }

    // Manage Customers & Manual Discount
    public function getCustomers()
    {
        // Ambil semua user yang pernah order di toko ini
        $userIds = Order::query()->where('owner_id', Auth::id())->pluck('user_id')->unique()->filter();
        $customers = \App\Models\User::whereIn('id', $userIds)->get();

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    public function updateCustomerDiscount(Request $request, $id)
    {
        $request->validate([
            'manual_discount' => 'required|numeric|min:0|max:100'
        ]);

        $user = \App\Models\User::findOrFail($id);
        $user->update(['manual_discount' => $request->manual_discount]);

        return response()->json([
            'success' => true,
            'message' => 'Diskon manual untuk user ' . $user->name . ' berhasil diupdate menjadi ' . $request->manual_discount . '%'
        ]);
    }

    // Helper untuk tambah poin
    private function grantPoints($order)
    {
        if ($order->user && !$order->points_granted) {
            $pointsEarned = floor($order->total_price / 1000); // 1 point per 1000 IDR
            $order->user->increment('points', $pointsEarned);
            $order->user->updateLevel();
            
            // Tandai poin sudah diberikan
            $order->update(['points_granted' => true]);
        }
    }
}