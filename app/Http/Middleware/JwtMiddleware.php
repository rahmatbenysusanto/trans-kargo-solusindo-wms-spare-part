<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status'  => false,
                'message' => 'Token not provided.',
            ], 401);
        }

        $payload = $this->validateToken($token);

        if (!$payload) {
            return response()->json([
                'status'  => false,
                'message' => 'Token is invalid or expired.',
            ], 401);
        }

        // Inject user info into request
        $request->attributes->set('jwt_user_id',   $payload['sub']);
        $request->attributes->set('jwt_username',  $payload['username']);
        $request->attributes->set('jwt_role',      $payload['role']);
        $request->attributes->set('jwt_client_ids', $payload['client_ids'] ?? []);

        return $next($request);
    }

    /**
     * Validate JWT token and return payload or null on failure.
     */
    private function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $secret    = config('app.key');
        $expected  = hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true);
        $expected  = rtrim(strtr(base64_encode($expected), '+/', '-_'), '=');

        if (!hash_equals($expected, $signatureB64)) {
            return null;
        }

        $payload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);

        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }
}
