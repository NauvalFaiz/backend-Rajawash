<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Owner;
use App\Models\UserProfile;

class ProfileController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        
        // Load relationships only if they exist on the model
        $relations = [];
        if (method_exists($user, 'courierProfile')) $relations[] = 'courierProfile';
        if (method_exists($user, 'userProfile')) $relations[] = 'userProfile';
        if (method_exists($user, 'ownerProfile')) $relations[] = 'ownerProfile';
        
        if (!empty($relations)) {
            $user->load($relations);
        }

        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $isOwnerModel = $user instanceof Owner;
        $table = $isOwnerModel ? 'owners' : 'users';

        // 1. Validasi Dasar
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:'.$table.',email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        // 2. Update Data User
        $user->name = $request->name;
        $user->email = $request->email;
        if (isset($request->phone)) {
            $user->phone = $request->phone;
        }

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // 3. Update Detail berdasarkan Role
        if ($user->role === 'owner' || $isOwnerModel) {
            // Jika dia login pake model User (admin buat owner) atau model Owner langsung
            $owner = $isOwnerModel ? $user : Owner::query()->where('email', $user->email)->first();
            if ($owner) {
                $owner->update([
                    'laundry_address' => $request->address,
                    'laundry_name' => $request->laundry_name ?? $owner->laundry_name,
                    'phone' => $request->phone ?? $owner->phone
                ]);
            }
        } elseif ($user->role === 'kurir') {
            $profile = \App\Models\CourierProfile::query()->where('user_id', $user->id)->first();
            if ($profile && $request->address) {
                $profile->update(['address' => $request->address]);
            }
        } else {
            $profile = UserProfile::query()->where('user_id', $user->id)->first();
            if ($profile && $request->address) {
                $profile->update(['address' => $request->address]);
            }
        }

        // 4. Pastikan atribut role tetap ada (untuk owner yang tidak punya kolom role)
        if ($isOwnerModel) {
            $user->role = 'owner';
        }

        // 5. Load relasi yang ada saja
        $relations = [];
        if (method_exists($user, 'courierProfile')) $relations[] = 'courierProfile';
        if (method_exists($user, 'userProfile')) $relations[] = 'userProfile';
        if (method_exists($user, 'ownerProfile')) $relations[] = 'ownerProfile';
        
        if (!empty($relations)) {
            $user->load($relations);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ]);
    }
}