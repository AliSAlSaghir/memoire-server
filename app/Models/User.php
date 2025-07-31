<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject {
  use HasFactory, Notifiable, SoftDeletes;

  protected $fillable = [
    'name',
    'email',
    'password',
    'profile_picture_url',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected function casts(): array {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function capsules() {
    return $this->hasMany(Capsule::class);
  }

  public function getJWTIdentifier() {
    return $this->getKey();
  }

  public function getJWTCustomClaims(): array {
    return [];
  }
}
