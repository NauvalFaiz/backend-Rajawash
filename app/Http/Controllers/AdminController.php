<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\Order;
use App\Models\CourierTask;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Tambah Laundry / Owner Baru
    public function addLaundry(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:owners',
            'password' => 'required|min:6',
            'laundry_name' => 'required|string',
            'laundry_address' => 'required|string',
            'phone' => 'nullable|string'
        ]);

        $owner = Owner::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'laundry_name' => $request->laundry_name,
            'laundry_address' => $request->laundry_address,
            'phone' => $request->phone,
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laundry berhasil ditambahkan',
            'data' => $owner
        ], 201);
    }

    // Monitor Semua Order
    public function monitorOrders()
    {
        // Mengambil semua order lengkap dengan data user, owner, dan service
        $orders = Order::with(['user:id,name,phone', 'owner:id,laundry_name,phone', 'service'])->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    // Monitor Kurir Sedang Bekerja
    public function monitorCouriers()
    {
        // Mengambil tugas kurir yang sedang aktif
        $activeTasks = CourierTask::with(['order', 'courier:id,name,phone'])
            ->whereIn('status', ['assigned', 'picked_up', 'delivering'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activeTasks
        ]);
    }

    public function approveCourier($id)
    {
        $courierProfile = \App\Models\CourierProfile::firstOrCreate(
            ['user_id' => $id],
            ['status' => 'pending']
        );
        $courierProfile->update(['status' => 'approved']);

        return response()->json(['message' => 'Kurir berhasil disetujui.']);
    }

    // Confirm Owner Registration
    public function approveOwner($id)
    {
        $owner = Owner::findOrFail($id);
        $owner->update(['status' => 'active']);

        return response()->json(['message' => 'Toko Laundry berhasil diaktifkan.']);
    }

    // Get All Laundries (Active & Inactive)
    public function getLaundries()
    {
        return response()->json([
            'success' => true,
            'data' => Owner::all()
        ]);
    }

    // Manage Order (Update Status)
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'Status order berhasil diubah.']);
    }

    // Admin CRUD Services for a specific owner
    public function addService(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:owners,id',
            'name' => 'required|string',
            'unit_type' => 'required|in:kg,pcs',
            'price' => 'required|numeric'
        ]);

        $service = \App\Models\Service::create($request->all());

        return response()->json(['message' => 'Layanan berhasil ditambahkan.', 'data' => $service]);
    }

    // Get All Couriers
    public function getCouriers()
    {
        $couriers = \App\Models\User::where('role', 'kurir')
            ->with('courierProfile')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $couriers
        ]);
    }
}
