<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller {
  /**
   * Display a listing of the tags.
   */
  public function index() {
    return response()->json(Tag::latest()->get());
  }

  /**
   * Store a newly created tag in storage.
   */
  public function store(Request $request) {
    $request->validate([
      'name' => 'required|string|unique:tags,name|max:255',
    ]);
    $tag = Tag::create($request->all());
    return response()->json($tag);
  }

  /**
   * Display the specified tag.
   */
  public function show(Tag $tag) {
    return response()->json($tag);
  }

  /**
   * Update the specified tag in storage.
   */
  public function update(Request $request, Tag $tag) {
    $request->validate([
      'name' => 'required|string|unique:tags,name,' . $tag->id . '|max:255',
    ]);

    $tag->update($request->all());
    return response()->json($tag);
  }

  /**
   * Remove the specified tag from storage.
   */
  public function destroy(Tag $tag) {
    $tag->delete();
    return response()->noContent();
  }
}
