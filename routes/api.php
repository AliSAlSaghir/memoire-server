<?php

use App\Http\Controllers\User\AttachmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\CapsuleController;
use App\Http\Controllers\User\TagController;
use App\Http\Controllers\User\UserController;

Route::group(['prefix' => 'v0.1'], function () {

  Route::group(['prefix' => 'auth'], function () {
    Route::middleware(['custom.guest'])->group(function () {
      Route::post('login', [AuthController::class, 'login']);
      Route::post('register', [AuthController::class, 'register']);
    });

    Route::get('google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('google/callback', [AuthController::class, 'handleGoogleCallback']);

    Route::middleware(['cookie.auth'])->group(function () {
      Route::get('check_token', [AuthController::class, 'checkToken']);
      Route::post('refresh', [AuthController::class, 'refresh']);
      Route::post('me', [AuthController::class, 'me']);
      Route::post('logout', [AuthController::class, 'logout']);
    });
  });

  Route::middleware(['cookie.auth'])->group(function () {
    Route::get('capsules/moods', [CapsuleController::class, 'moods']);
    Route::apiResource('capsules', CapsuleController::class)->except('show');
    Route::get('/capsules/{identifier}', [CapsuleController::class, 'show']);


    Route::get('/users/{user}/capsules', [UserController::class, 'getUserCapsules']);

    Route::get('capsules/{capsule}/share-token', [CapsuleController::class, 'getShareToken']);

    Route::apiResource('tags', TagController::class);

    Route::put('updateMe', [UserController::class, 'update']);

    Route::get('/capsule_attachments/{filename}', [AttachmentController::class, 'download']);
  });
});
