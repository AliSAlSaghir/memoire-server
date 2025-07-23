<?php

namespace App\Services;

use App\Models\Capsule;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserService {
  static function updateUser(User $user, $data) {
    if (isset($data['name'])) {
      $user->name = $data['name'];
    }

    if (isset($data['profile_picture'])) {
      $user->profile_picture_url = self::storeProfilePicture($data['profile_picture']);
    }

    $user->save();
    return $user;
  }

  static function getUserCapsules($userId) {
    return Capsule::where('user_id', $userId)->latest()->get();
  }

  private static function storeProfilePicture($imageData) {
    preg_match("/^data:image\/(\w+);base64,/", $imageData, $type);
    $image = preg_replace("/^data:image\/\w+;base64,/", '', $imageData);
    $image = str_replace(' ', '+', $image);

    $fileName = uniqid() . '.' . ($type[1] ?? 'png');
    Storage::disk('public')->put("profile_pictures/$fileName", base64_decode($image));

    return asset("storage/profile_pictures/$fileName");
  }
}
