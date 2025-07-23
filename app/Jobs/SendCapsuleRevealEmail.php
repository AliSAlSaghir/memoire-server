<?php

namespace App\Jobs;

use App\Mail\CapsuleRevealMail;
use App\Models\Capsule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCapsuleRevealEmail implements ShouldQueue {
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected Capsule $capsule;

  public function __construct(Capsule $capsule) {
    $this->capsule = $capsule;
  }

  public function handle() {
    Mail::to($this->capsule->user->email)
      ->send(new CapsuleRevealMail($this->capsule));
  }
}
