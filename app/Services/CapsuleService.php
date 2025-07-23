<?php

namespace App\Services;

use App\Http\Requests\CreateCapsuleRequest;
use App\Http\Requests\UpdateCapsuleRequest;
use App\Jobs\SendCapsuleRevealEmail;
use App\Models\Capsule;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CapsuleService {
  public static function getFilteredCapsules(Request $request) {
    $query = Capsule::with(['user', 'tags', 'attachments']);

    if ($request->boolean('revealed_only')) {
      $query->where('reveal_at', '<=', now());
    }

    if ($request->filled('user_id')) {
      $query->where('user_id', $request->user_id);
    }

    if ($request->filled('country')) {
      $query->where('country', $request->country);
    }

    if ($request->filled('tag')) {
      $query->whereHas('tags', fn($q) => $q->where('name', $request->tag));
    }

    if ($request->filled('privacy')) {
      $query->where('privacy', $request->privacy);
    }

    if ($request->filled('mood')) {
      $query->where('mood', $request->mood);
    }

    if ($request->filled('date')) {
      $query->where('reveal_at', '>=', $request->date);
    }

    return $query->latest()->paginate($request->input('per_page', 30));
  }

  public static function createCapsule(CreateCapsuleRequest $request) {
    $validated = $request->validated();
    $geoData = self::getGeoData($request->ip());

    $capsule = Capsule::create([
      ...$validated,
      'reveal_at' => Carbon::parse($validated['reveal_at']),
      'user_id' => auth('api')->id(),
      'share_token' => Str::uuid(),
      'ip_address' => $request->ip(),
      ...$geoData
    ]);

    self::processTags($capsule, $validated['tags'] ?? '');
    self::processAttachments($capsule, $validated['attachments'] ?? []);
    self::setCoverImage($capsule);
    self::scheduleRevealEmail($capsule);

    return $capsule->load(['user', 'tags', 'attachments']);
  }

  public static function updateCapsule(UpdateCapsuleRequest $request, Capsule $capsule) {
    $validated = $request->validated();
    $geoData = self::getGeoData($request->ip());

    $updates = [
      ...$validated,
      'ip_address' => $request->ip(),
      ...$geoData
    ];

    if (isset($validated['reveal_at'])) {
      $updates['reveal_at'] = Carbon::parse($validated['reveal_at']);
    }

    unset($updates['tags'], $updates['attachments']);

    $capsule->update($updates);

    if (array_key_exists('tags', $validated)) {
      self::processTags($capsule, $validated['tags'], true);
    }

    if (!empty($validated['attachments'])) {
      self::processAttachments($capsule, $validated['attachments']);
      self::setCoverImage($capsule);
    }

    if (isset($validated['reveal_at'])) {
      self::scheduleRevealEmail($capsule);
    }

    return $capsule->load(['user', 'tags', 'attachments']);
  }

  public static function getGeoData($ip) {
    $response = @file_get_contents("http://ip-api.com/json/{$ip}");

    if (!$response) return [];

    $data = json_decode($response);
    return ($data && $data->status === 'success') ? [
      'country' => $data->country,
      'city' => $data->city,
      'lat' => $data->lat,
      'long' => $data->lon
    ] : [];
  }

  public static function processTags(Capsule $capsule, $tagsString, $sync = false) {
    $tagNames = array_map('trim', explode(',', $tagsString));
    $tagIds = [];

    foreach ($tagNames as $tagName) {
      if (!empty($tagName)) {
        $tag = Tag::firstOrCreate(['name' => $tagName]);
        $tagIds[] = $tag->id;
      }
    }

    $sync ? $capsule->tags()->sync($tagIds) : $capsule->tags()->attach($tagIds);
  }

  public static function processAttachments(Capsule $capsule, array $attachments) {
    foreach ($attachments as $attachment) {
      if (!in_array($attachment['type'], ['image', 'audio'])) continue;

      $filePath = self::storeBase64File(
        $attachment['base64'],
        "capsule_attachments"
      );

      $capsule->attachments()->create([
        'type' => $attachment['type'],
        'file_path' => $filePath
      ]);
    }
  }

  public static function storeBase64File($base64, $directory) {
    preg_match("/^data:(.*?);base64,/", $base64, $matches);
    $mime = $matches[1] ?? null;
    $extension = self::getExtensionFromMime($mime);

    $base64 = preg_replace("/^data:.*?;base64,/", '', $base64);
    $base64 = str_replace(' ', '+', $base64);

    $filename = uniqid() . '.' . $extension;
    $relativePath = "$directory/$filename";

    Storage::disk('public')->put($relativePath, base64_decode($base64));
    return $relativePath;
  }

  public static function getExtensionFromMime($mime) {
    return match (true) {
      str_contains($mime, 'jpeg') => 'jpg',
      str_contains($mime, 'jpg') => 'jpg',
      str_contains($mime, 'png') => 'png',
      str_contains($mime, 'gif') => 'gif',
      str_contains($mime, 'mpeg') => 'mp3',
      str_contains($mime, 'mp3') => 'mp3',
      str_contains($mime, 'wav') => 'wav',
      str_contains($mime, 'ogg') => 'ogg',
      default => 'bin'
    };
  }

  public static function setCoverImage(Capsule $capsule) {
    $firstImage = $capsule->attachments()
      ->where('type', 'image')
      ->orderBy('id')
      ->first();

    if ($firstImage) {
      $capsule->cover_image_url = asset('storage/' . $firstImage->file_path);
      $capsule->save();
    }
  }

  public static function scheduleRevealEmail(Capsule $capsule) {
    SendCapsuleRevealEmail::dispatch($capsule)
      ->delay($capsule->reveal_at);
  }

  public static function getPublicMoods() {
    return Capsule::where('reveal_at', '<=', now())
      ->where('privacy', 'public')
      ->whereNotNull('mood')
      ->where('mood', '<>', '')
      ->distinct()
      ->pluck('mood');
  }
}
