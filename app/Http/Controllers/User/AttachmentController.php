<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller {
  public function download($filename) {
    $path = 'capsule_attachments/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
      return response()->json(['message' => 'File not found'], 404);
    }

    return $this->fileResponse($path);
  }

  private function fileResponse($path): StreamedResponse {
    $file = Storage::disk('public')->get($path);
    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
    $disk = Storage::disk('public');
    $mimeType = $disk->mimeType($path);
    $fileName = basename($path);

    return Response::stream(
      function () use ($file) {
        echo $file;
      },
      200,
      [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        'Access-Control-Allow-Origin' => '*',
      ]
    );
  }
}
