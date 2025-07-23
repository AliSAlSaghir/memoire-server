<?php

namespace App\Mail;

use App\Models\Capsule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CapsuleRevealMail extends Mailable {
  use Queueable, SerializesModels;

  public Capsule $capsule;

  public function __construct(Capsule $capsule) {
    $this->capsule = $capsule;
  }

  public function build() {
    return $this->subject('Your Capsule is Revealed!')
      ->view('emails.capsule_reveal')
      ->with([
        'capsule' => $this->capsule,
      ]);
  }
}
