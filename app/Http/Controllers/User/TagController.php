<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use App\Services\TagService;

class TagController extends Controller {

  public function index() {
    $tags = TagService::getAllTags();
    return $this->responseJSON($tags);
  }

  public function store(CreateTagRequest $request) {
    $tag = TagService::createTag($request->validated());
    return $this->responseJSON($tag);
  }


  public function show(Tag $tag) {
    $tag = TagService::getTag($tag);
    return $this->responseJSON($tag);
  }

  public function update(UpdateTagRequest $request, Tag $tag) {
    $updatedTag = TagService::updateTag($tag, $request->validated());
    return $this->responseJSON($updatedTag);
  }

  public function destroy(Tag $tag) {
    TagService::deleteTag($tag);
    return response()->noContent();
  }
}
