<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CapsuleResource extends JsonResource {
  public function toArray(Request $request): array {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'message' => $this->message,
      'mood' => $this->mood,
      'emoji' => $this->emoji,
      'color' => $this->color,
      'cover_image_url' => $this->cover_image_url,
      'ip_address' => $this->ip_address,
      'privacy' => $this->privacy,
      'reveal_at' => $this->reveal_at,
      'surprise' => $this->surprise,
      'created_at' => $this->created_at,
      'user' => [
        'id' => $this->user->id,
        'name' => $this->user->name,
      ],
      'tags' => $this->tags->pluck('name'),
      'attachments' => $this->attachments->map(fn($a) => [
        'id' => $a->id,
        'type' => $a->type,
        'url' => asset('storage/' . $a->file_path),
      ]),
      'location' => [
        'lat' => $this->lat,
        'long' => $this->long,
        'city' => $this->city,
        'country' => $this->country,
      ],
    ];
  }
}
