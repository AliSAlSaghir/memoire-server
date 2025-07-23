<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\ResponseTrait;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller {
  use ResponseTrait;

  public function register(UserRegisterRequest $request) {
    [$token, $user] = AuthService::register($request->validated());
    return $this->respondWithToken($token, $user);
  }

  public function login(UserLoginRequest $request) {
    $credentials = $request->validated();

    $token = AuthService::login($credentials);

    if (!$token) {
      return $this->responseJSON('Unauthorized', 'error', 401);
    }

    return $this->respondWithToken($token, auth('api')->user());
  }

  public function me() {
    return $this->responseJSON(auth('api')->user());
  }

  public function logout() {
    $token = request()->cookie('jwt');
    AuthService::logout($token);

    $cookie = cookie()->forget('jwt');
    return $this->responseJSON('Successfully logged out')
      ->withCookie($cookie);
  }

  public function refresh() {
    $token = request()->cookie('jwt');
    $newToken = AuthService::refresh($token);

    if (!$newToken) {
      return $this->responseJSON('No token provided', 'error', 401);
    }

    return $this->respondWithToken($newToken, auth('api')->user());
  }

  public function checkToken() {
    return $this->responseJSON(['status' => 'ok']);
  }

  protected function respondWithToken($token, $user) {
    $cookie = cookie(
      'jwt',
      $token,
      JWTAuth::factory()->getTTL(),
      '/',
      null,
      true,   // secure
      true,   // httpOnly
      false,
      'Strict'
    );

    return $this->responseJSON($user)->withCookie($cookie);
  }

  public function redirectToGoogle() {
    /** @var \Laravel\Socialite\Contracts\Provider|\Laravel\Socialite\Two\GoogleProvider $provider */
    $provider = Socialite::driver('google');
    $googleUser = $provider->stateless()->redirect();
  }

  public function handleGoogleCallback() {
    [$token, $user] = AuthService::handleGoogleCallback();

    $cookie = cookie(
      'jwt',
      $token,
      JWTAuth::factory()->getTTL(),
      '/',
      null,
      true,
      true,
      false,
      'Strict'
    );

    return response()->view('oauth.success', [
      'token' => $token,
      'user' => $user,
    ])->withCookie($cookie);
  }
}
