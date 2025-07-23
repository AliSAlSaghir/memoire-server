<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCapsuleRequest;
use App\Http\Requests\UpdateCapsuleRequest;
use App\Http\Resources\CapsuleResource;
use App\Jobs\SendCapsuleRevealEmail;
use App\Models\Capsule;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

use function Illuminate\Log\log;

class CapsuleController extends Controller {
  use AuthorizesRequests;

  public function index(Request $request) {
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
      $query->whereHas('tags', function ($q) use ($request) {
        $q->where('name', $request->tag);
      });
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

    $perPage = $request->input('per_page', 30);

    return CapsuleResource::collection(
      $query->latest()->paginate($perPage)
    );
  }


  public function store(CreateCapsuleRequest $request) {

    $validated = $request->validated();

    // Step 1: Get user IP
    // $ip = $request->ip();
    $ip = "213.204.87.253";

    // Step 2: Get geo info from ip-api.com
    $country = null;
    $city = null;
    $lat = null;
    $long = null;

    $response = @file_get_contents("http://ip-api.com/json/{$ip}");
    if ($response) {
      $data = json_decode($response);
      if ($data && $data->status === 'success') {
        $country = $data->country;
        $city = $data->city;
        $lat = $data->lat;
        $long = $data->lon;
      }
    }

    // Step 3: Create capsule with geo info
    $capsule = Capsule::create([
      ...$validated,
      'reveal_at' => Carbon::parse($validated['reveal_at'])->format('Y-m-d H:i:s'),
      'user_id' => auth('api')->id(),
      'share_token' => Str::uuid()->toString(),
      'ip_address' => $ip,
      'country' => $country,
      'city' => $city,
      'lat' => $lat,
      'long' => $long,
    ]);

    if (!empty($validated['tags'])) {
      // 1. Convert comma-separated string to array of trimmed names
      $tagNames = array_map('trim', explode(',', $validated['tags']));

      // 2. Get or create tag IDs
      $tagIds = [];
      foreach ($tagNames as $tagName) {
        $tag = Tag::firstOrCreate(['name' => $tagName]);
        $tagIds[] = $tag->id;
      }

      // 3. Attach to capsule (many-to-many pivot table)
      $capsule->tags()->attach($tagIds);
    }

    if (!empty($validated['attachments'])) {
      foreach ($validated['attachments'] as $attachment) {
        $type = $attachment['type'];

        // Only allow 'image' or 'audio'
        if (!in_array($type, ['image', 'audio'])) {
          continue; // skip invalid types
        }

        $base64 = $attachment['base64'];

        // Extract mime type from base64 string
        preg_match("/^data:(.*?);base64,/", $base64, $matches);
        $mime = $matches[1] ?? null;

        // Clean base64 string (remove mime prefix)
        if ($mime) {
          $escapedMime = preg_quote($mime, '/');
          $base64 = preg_replace("/^data:$escapedMime;base64,/", '', $base64);
        }

        $base64 = str_replace(' ', '+', $base64);

        // Guess extension based on mime type
        $extension = 'bin';
        if (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
          $extension = 'jpg';
        } elseif (str_contains($mime, 'png')) {
          $extension = 'png';
        } elseif (str_contains($mime, 'gif')) {
          $extension = 'gif';
        } elseif (str_contains($mime, 'mpeg') || str_contains($mime, 'mp3')) {
          $extension = 'mp3';
        } elseif (str_contains($mime, 'wav')) {
          $extension = 'wav';
        } elseif (str_contains($mime, 'ogg')) {
          $extension = 'ogg';
        }

        $filename = uniqid() . '.' . $extension;
        $relativePath = "capsule_attachments/$filename";

        Storage::disk('public')->put($relativePath, base64_decode($base64));

        // Laravel auto fills capsule_id in attachments relation
        $capsule->attachments()->create([
          'type' => $type,
          'file_path' => $relativePath,
        ]);
      }

      $firstImage = $capsule->attachments()
        ->where('type', 'image')
        ->orderBy('id')
        ->first();

      if ($firstImage) {
        $capsule->cover_image_url = asset('storage/' . $firstImage->file_path);
        $capsule->save();
      }
    }

    SendCapsuleRevealEmail::dispatch($capsule)
      ->delay($capsule->reveal_at);

    return new CapsuleResource($capsule->load(['user', 'tags', 'attachments']));
  }



  public function show($identifier) {
    $query = Capsule::with(['user', 'tags', 'attachments']);

    if (is_numeric($identifier)) {
      $capsule = $query->findOrFail($identifier);
    } else {
      $capsule = $query
        ->where('share_token', $identifier)
        ->where('privacy', 'unlisted')
        ->firstOrFail();
    }

    return new CapsuleResource($capsule);
  }



  public function update(UpdateCapsuleRequest $request, Capsule $capsule) {
    $this->authorize('modify', $capsule);

    $validated = $request->validated();

    $updates = $validated;

    // Format reveal_at if present
    if (isset($validated['reveal_at'])) {
      $updates['reveal_at'] = Carbon::parse($validated['reveal_at'])->format('Y-m-d H:i:s');
    }

    // Remove tags and attachments from $updates so they aren't mass assigned
    unset($updates['tags'], $updates['attachments']);

    // Add IP & geo info
    // $ip = $request->ip();
    $ip = "213.204.87.253";
    $updates['ip_address'] = $ip;

    $response = @file_get_contents("http://ip-api.com/json/{$ip}");
    if ($response) {
      $data = json_decode($response);
      if ($data && $data->status === 'success') {
        $updates['country'] = $data->country;
        $updates['city'] = $data->city;
        $updates['lat'] = $data->lat;
        $updates['long'] = $data->lon;
      }
    }

    // Update capsule with all other fields
    $capsule->update($updates);

    // Handle tags separately (sync)
    if (array_key_exists('tags', $validated)) {
      $tagNames = array_filter(array_map('trim', explode(',', $validated['tags'])));
      $tagIds = [];

      foreach ($tagNames as $tagName) {
        $tag = Tag::firstOrCreate(['name' => $tagName]);
        $tagIds[] = $tag->id;
      }

      $capsule->tags()->sync($tagIds);
    }

    // Handle attachments (adds new ones only)
    if (!empty($validated['attachments'])) {
      foreach ($validated['attachments'] as $attachment) {
        $type = $attachment['type'];
        $base64 = $attachment['base64'];

        preg_match("/^data:(.*?);base64,/", $base64, $matches);
        $mime = $matches[1] ?? null;

        if ($mime) {
          $escapedMime = preg_quote($mime, '/');
          $base64 = preg_replace("/^data:$escapedMime;base64,/", '', $base64);
        }

        $base64 = str_replace(' ', '+', $base64);

        $extension = match (true) {
          str_contains($mime, 'jpeg'), str_contains($mime, 'jpg') => 'jpg',
          str_contains($mime, 'png') => 'png',
          str_contains($mime, 'gif') => 'gif',
          str_contains($mime, 'mpeg'), str_contains($mime, 'mp3') => 'mp3',
          str_contains($mime, 'wav') => 'wav',
          str_contains($mime, 'ogg') => 'ogg',
          default => 'bin'
        };

        $filename = uniqid() . '.' . $extension;
        $relativePath = "capsule_attachments/$filename";

        Storage::disk('public')->put($relativePath, base64_decode($base64));

        $capsule->attachments()->create([
          'type' => $type,
          'file_path' => $relativePath,
        ]);

        $firstImage = $capsule->attachments()
          ->where('type', 'image')
          ->orderBy('id')
          ->first();

        if ($firstImage) {
          $capsule->cover_image_url = asset('storage/' . $firstImage->file_path);
          $capsule->save();
        }
      }
    }

    // Reschedule email if reveal_at was updated
    if (isset($validated['reveal_at'])) {
      SendCapsuleRevealEmail::dispatch($capsule)
        ->delay($capsule->reveal_at);
    }

    return new CapsuleResource($capsule->load(['user', 'tags', 'attachments']));
  }



  public function destroy(Capsule $capsule) {
    $this->authorize('modify', $capsule);


    $capsule->delete();

    return response()->noContent();
  }


  public function getShareToken(Capsule $capsule) {
    $this->authorize('modify', $capsule);

    return response()->json([
      'share_token' => $capsule->share_token
    ]);
  }



  public function moods() {
    $moods = Capsule::query()
      ->where('reveal_at', '<=', now())
      ->where('privacy', 'public')
      ->whereNotNull('mood')
      ->where('mood', '<>', '')
      ->distinct()
      ->pluck('mood');

    return response()->json([
      'data' => $moods,
    ]);
  }
}
