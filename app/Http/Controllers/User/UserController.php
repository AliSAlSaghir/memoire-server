<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\CapsuleResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {

  public function update(UpdateUserRequest $request) {
    $user = Auth::guard('api')->user();

    $updatedUser = UserService::updateUser($user, $request->validated());

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
