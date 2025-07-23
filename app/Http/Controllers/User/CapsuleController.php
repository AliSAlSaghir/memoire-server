<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCapsuleRequest;
use App\Http\Requests\UpdateCapsuleRequest;
use App\Http\Resources\CapsuleResource;
use App\Models\Capsule;
use App\Services\CapsuleService;
use App\Traits\ResponseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CapsuleController extends Controller {
  use AuthorizesRequests, ResponseTrait;

  public function index(Request $request) {
    $capsules = CapsuleService::getFilteredCapsules($request);
    return CapsuleResource::collection($capsules);
  }

  public function store(CreateCapsuleRequest $request) {
    $capsule = CapsuleService::createCapsule($request);
    return new CapsuleResource($capsule);
  }

  public function show($identifier) {
    $capsule = is_numeric($identifier)
      ? Capsule::with(['user', 'tags', 'attachments'])->findOrFail($identifier)
      : Capsule::with(['user', 'tags', 'attachments'])
      ->where('share_token', $identifier)
      ->where('privacy', 'unlisted')
      ->firstOrFail();

    return new CapsuleResource($capsule);
  }

  public function update(UpdateCapsuleRequest $request, Capsule $capsule) {
    $this->authorize('modify', $capsule);
    $updatedCapsule = CapsuleService::updateCapsule($request, $capsule);
    return new CapsuleResource($updatedCapsule);
  }

  public function destroy(Capsule $capsule) {
    $this->authorize('modify', $capsule);
    $capsule->delete();
    return response()->noContent();
  }

  public function getShareToken(Capsule $capsule) {
    $this->authorize('modify', $capsule);
    return $this->responseJSON(['share_token' => $capsule->share_token]);
  }

  public function moods() {
    $moods = CapsuleService::getPublicMoods();
    return $this->responseJSON(['data' => $moods]);
  }
}
