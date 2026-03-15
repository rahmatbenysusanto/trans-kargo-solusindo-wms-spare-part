<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
    /**
     * Login dan dapatkan JWT token.
     *
     * POST /api/auth/login
     * Body: { "username": "...", "password": "..." }
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid username or password.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'status'  => false,
                'message' => 'Your account is inactive. Please contact administrator.',
            ], 403);
        }

        $clientIds = $user->isAdminWMS()
            ? []
            : $user->clients()->pluck('client_id')->toArray();

        $token = $this->generateToken([
            'sub'        => $user->id,
            'username'   => $user->username,
            'name'       => $user->name,
            'role'       => $user->role,
            'client_ids' => $clientIds,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'role'       => $user->role,
                'client_ids' => $clientIds,
            ],
        ]);
    }

    /**
     * Generate JWT token (HS256, custom implementation).
     */
    private function generateToken(array $customClaims): string
    {
        $secret = config('app.key');

        $header = rtrim(strtr(base64_encode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ])), '+/', '-_'), '=');

        $payload = array_merge($customClaims, [
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24), // 24 jam
        ]);

        $payloadB64 = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        $signature = hash_hmac('sha256', "$header.$payloadB64", $secret, true);
        $signatureB64 = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "$header.$payloadB64.$signatureB64";
    }
}
