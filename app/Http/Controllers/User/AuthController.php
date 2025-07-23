<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller {

  public function register(UserRegisterRequest $request) {
    $validatedData = $request->validated();

    $user = User::create([
      'name' => $validatedData['name'],
      'email' => $validatedData['email'],
      'password' => bcrypt($validatedData['password']),
    ]);

    $token = auth('api')->login($user);
    return $this->respondWithToken($token);
  }

  public function login(Request $request) {
    $credentials = $request->validate([
      'email' => 'required|string|email|max:255',
      'password' => 'required|string|min:6',
    ]);

    if (! $token = auth('api')->attempt($credentials)) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    return $this->respondWithToken($token);
  }


  public function me() {
    $user = User::findOrFail(auth('api')->id());
    return response()->json($user);
  }


  public function logout() {
    $token = request()->cookie('jwt');

    if ($token) {
      JWTAuth::setToken($token)->invalidate();
    }

    $cookie = cookie()->forget('jwt');

    return response()->json(['message' => 'Successfully logged out'])->withCookie($cookie);
  }



  public function refresh() {
    $token = request()->cookie('jwt');

    if (!$token) {
      return response()->json(['message' => 'No token provided'], 401);
    }

    JWTAuth::setToken($token);
    $newToken = JWTAuth::refresh($token);

    return $this->respondWithToken($newToken);
  }

  public function checkToken() {
    return response()->json(['status' => 'ok']);
  }



  protected function respondWithToken($token) {
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

    $user = User::findOrFail(auth('api')->id());
    return response()->json($user)
      ->withCookie($cookie);
  }


  /**
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   * @noinspection PhpUndefinedMethodInspection
   */
  public function redirectToGoogle() {
    /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
    $provider = Socialite::driver('google');

    return $provider->stateless()->redirect();
  }

  /**
   * @return \Illuminate\Http\JsonResponse
   * @noinspection PhpUndefinedMethodInspection
   */
  public function handleGoogleCallback() {
    /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
    $provider = Socialite::driver('google');

    $googleUser = $provider->stateless()->user();
    Log::info(['hello' => $googleUser]);

    $user = User::updateOrCreate(
      ['email' => $googleUser->getEmail()],
      [
        'name' => $googleUser->getName(),
        'password' => bcrypt(Str::random(16)),
        'profile_picture_url' => $googleUser->getAvatar(),
      ]
    );

    $token = auth('api')->login($user);

    return response()->view('oauth.success', [
      'token' => $token,
      'user' => $user,
    ]);
  }
}
