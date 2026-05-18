<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Memberikan review (setelah status completed)
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk mereview order ini'], 403);
        }

        if ($order->status !== 'completed') {
            return response()->json(['message' => 'Anda hanya bisa memberikan review jika status pesanan sudah selesai (completed)'], 400);
        }

        $review = Review::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'owner_id' => $order->owner_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas ulasan Anda!',
            'data' => $review
        ]);
    }

    // Melihat review per toko
    public function index($owner_id)
    {
        $reviews = Review::with('user:id,name')->where('owner_id', $owner_id)->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }
}
