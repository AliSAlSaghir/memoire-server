<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCapsuleRequest extends FormRequest {
  public function authorize(): bool {
    return true;
  }

  public function rules(): array {
    return [
      'title' => 'sometimes|string|max:255',
      'message' => 'sometimes|string',
      'cover_image_url' => 'sometimes|string|max:255',
      'emoji' => 'sometimes|string|max:10',
      'color' => 'sometimes|string|max:20',
      'mood' => 'sometimes|string|max:50',
      'privacy' => 'sometimes|in:private,public,unlisted',
      'reveal_at' => 'sometimes|date|after_or_equal:now',
      'surprise' => 'sometimes|boolean',
      'tags' => 'sometimes|string|max:255',

      'attachments' => 'sometimes|array',
      'attachments.*.type' => 'required_with:attachments|string|in:image,audio',
      'attachments.*.base64' => 'required_with:attachments|string',
    ];
  }
}
