<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Owner;
use Illuminate\Support\Facades\Hash;
use App\Services\SupabaseJwtService;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthController extends Controller
{
    protected $supabaseJwt;

    public function __construct(SupabaseJwtService $supabaseJwt)
    {
        $this->supabaseJwt = $supabaseJwt;
    }

    // LOGIN DENGAN GOOGLE (OAUTH NATIVE)
    public function loginWithGoogle(Request $request)
    {
        $request->validate([
            'access_token' => 'required'
        ]);

        try {
            // 1. Verifikasi JWT Supabase (Backend verifikasi access_token yang dikirim Flutter)
            $supabaseUser = $this->supabaseJwt->verifyToken($request->access_token);
            
            $email = $supabaseUser->email;
            $providerId = $supabaseUser->sub;
            $name = $supabaseUser->user_metadata->full_name ?? $supabaseUser->user_metadata->name ?? 'Google User';
            $avatar = $supabaseUser->user_metadata->avatar_url ?? null;

            // 2. Cari user berdasarkan email (karena provider_id mungkin berbeda jika ganti provider)
            // Namun kita utamakan email sebagai unik identifier
            $user = User::where('email', $email)->first();

            if (!$user) {
                // First Login Auto Register
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(str()->random(32)), 
                    'role' => 'user', // Default role
                    'provider' => 'google',
                    'provider_id' => $providerId,
                    'avatar' => $avatar,
                    'last_login_at' => now()
                ]);
            } else {
                $user->update([
                    'provider' => 'google',
                    'provider_id' => $providerId,
                    'avatar' => $avatar ?: $user->avatar,
                    'last_login_at' => now()
                ]);
            }

            // Refresh user data to get the updated fields (especially since some were not fillable before)
            $user->refresh();

            // 3. Buat Sanctum Token untuk Mobile Session
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Google berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi Google: ' . $e->getMessage()
            ], 401);
        }
    }

    // REGISTER USER
    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users',
            'phone' => 'nullable|unique:users',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'user',
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token
        ]);
    }

    // LOGIN MANUAL
    public function login(Request $request)
    {
        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $account = User::query()->where($loginField, $request->login)->first();
        
        if (!$account) {
            $account = Owner::query()->where($loginField, $request->login)->first();
        }

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Email atau Nomor HP tidak ditemukan'], 401);
        }

        if (!Hash::check($request->password, $account->password)) {
            return response()->json(['success' => false, 'message' => 'Password yang Anda masukkan salah'], 401);
        }

        $account->update(['last_login_at' => now()]);
        $token = $account->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $account,
            'token' => $token
        ]);
    }

    // GET PROFILE ME
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    // REGISTER COURIER
    public function registerCourier(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users',
            'phone' => 'nullable|unique:users',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'kurir',
            'password' => Hash::make($request->password),
        ]);

        \App\Models\CourierProfile::create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi kurir berhasil, menunggu persetujuan admin.',
            'data' => $user
        ]);
    }

    // REGISTER OWNER
    public function registerOwner(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:owners',
            'password' => 'required|min:6|confirmed',
            'laundry_name' => 'required|string',
            'laundry_address' => 'required|string',
            'phone' => 'nullable|string'
        ]);

        $owner = \App\Models\Owner::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'laundry_name' => $request->laundry_name,
            'laundry_address' => $request->laundry_address,
            'phone' => $request->phone,
            'status' => 'inactive' 
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi toko berhasil, menunggu persetujuan admin.',
            'data' => $owner
        ]);
    }
}