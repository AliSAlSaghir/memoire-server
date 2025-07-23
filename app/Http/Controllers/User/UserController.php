<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\CapsuleResource;
use App\Models\Capsule;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller {
  public function update(Request $request) {
    $user = User::find(auth('api')->id());

    $validated = $request->validate([
      'name' => 'sometimes|string|max:255',
      'profile_picture' => 'sometimes|string',
    ]);

    if ($request->has('email') || $request->has('password')) {
      return response()->json(['message' => 'Email and password cannot be updated.'], 403);
    }

    if ($request->has('profile_picture')) {
      $image = $request->profile_picture;

      preg_match("/^data:image\/(\w+);base64,/", $image, $type);
      $image = preg_replace("/^data:image\/\w+;base64,/", '', $image);
      $image = str_replace(' ', '+', $image);

      $fileName = uniqid() . '.' . ($type[1] ?? 'png');
      $filePath = storage_path("app/public/profile_pictures/$fileName");

      file_put_contents($filePath, base64_decode($image));

      $user->profile_picture_url = asset("storage/profile_pictures/$fileName");
    }

    if (isset($validated['name'])) {
      $user->name = $validated['name'];
    }

    $user->save();

    return response()->json([
      'message' => 'User updated successfully.',
      'user' => $user,
    ]);
  }

  public function getUserCapsules($userId) {
    $capsules = Capsule::where('user_id', $userId);

    return CapsuleResource::collection(
      $capsules->latest()->get()
    );
  }
}
