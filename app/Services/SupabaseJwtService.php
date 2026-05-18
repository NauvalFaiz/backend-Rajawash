<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class SupabaseJwtService
{
    protected $url;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL');
    }

    /**
     * Decode and verify JWT from Supabase using JWKS
     *
     * @param string $token
     * @return object
     * @throws Exception
     */
    public function verifyToken($token)
    {
        if (!$this->url) {
            throw new Exception("SUPABASE_URL not found in .env");
        }

        try {
            // 1. Get JWKS (Public Keys) from Supabase with Caching (24 hours)
            $jwks = Cache::remember('supabase_jwks', 86400, function () {
                \Illuminate\Support\Facades\Log::info("Fetching JWKS from Supabase...");
                
                $response = Http::timeout(5)->get($this->url . '/auth/v1/.well-known/jwks.json');
                
                if (!$response->successful()) {
                    \Illuminate\Support\Facades\Log::error("Failed to fetch JWKS: " . $response->status());
                    throw new Exception("Failed to fetch JWKS from Supabase. Status: " . $response->status());
                }

                return $response->json();
            });

            // 2. Parse JWKS and Decode Token
            // The library will automatically match the algorithm based on the kid in the header
            $keys = JWK::parseKeySet($jwks);
            
            // Decoded token payload
            $decoded = JWT::decode($token, $keys);

            // Optional: Verify audience (aud) if needed
            if (isset($decoded->aud) && $decoded->aud !== 'authenticated') {
                throw new Exception("Invalid audience");
            }

            return $decoded;
        } catch (Exception $e) {
            // If verification fails, clear cache and retry once if it was a fetch error
            if (str_contains($e->getMessage(), 'Failed to fetch JWKS')) {
                Cache::forget('supabase_jwks');
            }
            throw new Exception("JWT Verification Failed: " . $e->getMessage());
        }
    }
}
