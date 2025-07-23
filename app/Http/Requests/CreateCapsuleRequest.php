<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCapsuleRequest extends FormRequest {
  public function authorize(): bool {
    return true;
  }

  public function rules(): array {
    return [
      'title' => 'required|string|max:255',
      'message' => 'required|string',
      'emoji' => 'nullable|string|max:10',
      'color' => 'nullable|string|max:20',
      'mood' => 'nullable|string|max:50',
      'privacy' => 'required|in:private,public,unlisted',
      'reveal_at' => 'required|date|after_or_equal:now',
      'surprise' => ['sometimes', 'boolean'],
      'tags' => 'nullable|string|max:255',

      'attachments' => ['nullable', 'array'],
      'attachments.*.type' => ['required', 'in:image,audio'],
      'attachments.*.base64' => ['required', 'string'],

    ];
  }
}
