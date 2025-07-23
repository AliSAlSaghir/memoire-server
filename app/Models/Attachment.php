<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model {
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'capsule_id',
    'file_path',
    'type',
  ];

  public function capsule() {
    return $this->belongsTo(Capsule::class);
  }
}
