<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Laravel\Socialite\Facades\Socialite;

class AuthService {
  public static function register(array $data) {
    $user = User::create([
      'name' => $data['name'],
      'email' => $data['email'],
      'password' => bcrypt($data['password']),
    ]);

    $token = auth('api')->login($user);
    return [$token, $user];
  }

  public static function login(array $credentials) {
    if (!$token = auth('api')->attempt($credentials)) {
      return null;
    }
    return $token;
  }

  public static function logout($token) {
    if ($token) {
      JWTAuth::setToken($token)->invalidate();
    }
  }

  public static function refresh($token) {
    if (!$token) {
      return null;
    }

    JWTAuth::setToken($token);
    return JWTAuth::refresh($token);
  }


  public static function handleGoogleCallback() {
    /** @var \Laravel\Socialite\Contracts\Provider|\Laravel\Socialite\Two\GoogleProvider $provider */
    $provider = Socialite::driver('google');
    $googleUser = $provider->stateless()->user();

    $user = User::updateOrCreate(
      ['email' => $googleUser->getEmail()],
      [
        'name' => $googleUser->getName(),
        'password' => bcrypt(Str::random(16)),
        'profile_picture_url' => $googleUser->getAvatar(),
      ]
    );

    $token = auth('api')->login($user);
    return [$token, $user];
  }
}
