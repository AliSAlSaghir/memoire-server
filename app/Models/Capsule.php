<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Capsule extends Model {
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'user_id',
    'title',
    'message',
    'cover_image_url',
    'emoji',
    'color',
    'mood',
    'privacy',
    'share_token',
    'lat',
    'long',
    'ip_address',
    'country',
    'city',
    'reveal_at',
    'surprise',
    'email_sent',
  ];

  public function tags() {
    return $this->belongsToMany(Tag::class);
  }

  public function attachments() {
    return $this->hasMany(Attachment::class);
  }

  public function user() {
    return $this->belongsTo(User::class);
  }
}
