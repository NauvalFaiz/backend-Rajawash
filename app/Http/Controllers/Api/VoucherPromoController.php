<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\Promo;
use App\Models\Membership;
use Illuminate\Http\Request;

class VoucherPromoController extends Controller
{
    /**
     * Get all active promos.
     */
    public function promos()
    {
        $promos = Promo::where(function ($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now());
        })->where(function ($q) {
            $q->whereNull('start_date')
              ->orWhere('start_date', '<=', now());
        })->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $promos
        ]);
    }

    /**
     * Validate and apply voucher code.
     */
    public function validateVoucher(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $voucher = Voucher::where('code', $request->code)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher tidak ditemukan'
            ], 404);
        }

        if (!$voucher->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher sudah expired atau sudah habis digunakan'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher valid',
            'data' => [
                'code' => $voucher->code,
                'name' => $voucher->name,
                'discount_amount' => $voucher->discount_amount,
                'discount_percentage' => $voucher->discount_percentage,
            ]
        ]);
    }

    /**
     * Get membership tiers.
     */
    public function memberships()
    {
        $memberships = Membership::orderBy('min_points', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $memberships
        ]);
    }

    /**
     * Get current user membership info.
     */
    public function myMembership(Request $request)
    {
        $user = $request->user();
        $membership = Membership::where('min_points', '<=', $user->points)
            ->orderBy('min_points', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'user_points' => $user->points,
                'user_level' => $user->level,
                'level_discount' => $user->level_discount,
                'membership' => $membership,
                'next_membership' => Membership::where('min_points', '>', $user->points)
                    ->orderBy('min_points', 'asc')
                    ->first(),
            ]
        ]);
    }
}
