<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CustomGuest {
  public function handle($request, Closure $next) {
    try {
      $token = $request->cookie('jwt');

      if ($token && JWTAuth::setToken($token)->check()) {
        return response()->json(['message' => 'Already authenticated'], JsonResponse::HTTP_FORBIDDEN);
      }
    } catch (\Exception $e) {
    }

    return $next($request);
  }
}
