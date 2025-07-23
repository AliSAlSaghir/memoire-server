<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\CapsuleResource;
use App\Services\UserService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {
  use ResponseTrait;

  public function update(Request $request) {
    $user = Auth::guard('api')->user();

    $request->validate([
      'name' => 'sometimes|string|max:255',
      'profile_picture' => 'sometimes|string',
    ]);

    if ($request->has('email') || $request->has('password')) {
      return $this->responseJSON('Email and password cannot be updated', 'error', 403);
    }

    $updatedUser = UserService::updateUser($user, $request->all());

    return $this->responseJSON([
      'message' => 'User updated successfully',
      'user' => $updatedUser
    ]);
  }

  public function getUserCapsules($userId) {
    $capsules = UserService::getUserCapsules($userId);
    return CapsuleResource::collection($capsules);
  }
}
