<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\AttachmentService;

class AttachmentController extends Controller {

  public function download($filename) {
    $response = AttachmentService::downloadFile($filename);

    if (!$response) {
      return $this->responseJSON('File not found', 'error', 404);
    }

    return $response;
  }
}
