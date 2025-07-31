<?php

namespace App\Services;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentService {
  static function downloadFile($filename) {
    $path = 'capsule_attachments/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
      return null;
    }

    return self::fileResponse($path);
  }

  private static function fileResponse($path): StreamedResponse {
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
