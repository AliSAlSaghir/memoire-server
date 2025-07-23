<?php

namespace App\Services;

use App\Models\Tag;

class TagService {
  static function getAllTags() {
    return Tag::latest()->get();
  }

  static function createTag($data) {
    return Tag::create($data);
  }

  static function getTag(Tag $tag) {
    return $tag;
  }

  static function updateTag(Tag $tag, $data) {
    $tag->update($data);
    return $tag;
  }

  static function deleteTag(Tag $tag) {
    $tag->delete();
  }
}
