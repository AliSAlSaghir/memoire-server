<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\Request;

class TagController extends Controller {

  public function index() {
    $tags = TagService::getAllTags();
    return $this->responseJSON($tags);
  }

  public function store(Request $request) {
    $request->validate([
      'name' => 'required|string|unique:tags,name|max:255',
    ]);

    $tag = TagService::createTag($request->all());
    return $this->responseJSON($tag);
  }

  public function show(Tag $tag) {
    $tag = TagService::getTag($tag);
    return $this->responseJSON($tag);
  }

  public function update(Request $request, Tag $tag) {
    $request->validate([
      'name' => 'required|string|unique:tags,name,' . $tag->id . '|max:255',
    ]);

    $updatedTag = TagService::updateTag($tag, $request->all());
    return $this->responseJSON($updatedTag);
  }

  public function destroy(Tag $tag) {
    TagService::deleteTag($tag);
    return response()->noContent();
  }
}
