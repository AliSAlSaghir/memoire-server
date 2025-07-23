<?php

namespace App\Policies;

use App\Models\Capsule;
use App\Models\User;

class CapsulePolicy {
  public function modify(User $user, Capsule $capsule): bool {
    return $user->id === $capsule->user_id;
  }
}
