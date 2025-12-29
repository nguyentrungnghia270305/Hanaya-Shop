<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('body', 'like', "%{$q}%")
                  ->orWhere('excerpt', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('author_id')) {
            $query->where('author_id', $request->get('author_id'));
        }

        $perPage = (int) $request->get('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $posts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($posts)->response()->getData(true),
        ]);
    }

    public function store(PostRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->storePublicly('posts', ['disk' => 'public']);
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug(substr($data['title'] ?? Str::random(10), 0, 50)) . '-' . Str::random(6);
        }

        // Normalize tags to CSV if provided as array
        if (!empty($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = implode(',', $data['tags']);
        }

        $post = Post::create($data);

        return response()->json(['success' => true, 'message' => 'Post created', 'data' => new PostResource($post)], 201);
    }

    public function show($id)
    {
        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        return response()->json(['success' => true, 'data' => new PostResource($post)]);
    }

    public function update(PostRequest $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            if (!empty($post->featured_image) && Storage::disk('public')->exists($post->featured_image)) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->storePublicly('posts', ['disk' => 'public']);
        }

        if (!empty($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = implode(',', $data['tags']);
        }

        $post->fill($data);
        $post->save();

        return response()->json(['success' => true, 'message' => 'Post updated', 'data' => new PostResource($post)]);
    }

    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $post->delete();

        return response()->json(['success' => true, 'message' => 'Post deleted']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Invalid ids provided'], 400);
        }

        $deleted = Post::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => "Deleted {$deleted} posts"]);
    }

    public function restore($id)
    {
        try {
            $post = Post::withTrashed()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        if (!$post->trashed()) {
            return response()->json(['success' => false, 'message' => 'Post is not deleted'], 400);
        }

        $post->restore();

        return response()->json(['success' => true, 'message' => 'Post restored', 'data' => new PostResource($post)]);
    }

    public function forceDelete($id)
    {
        try {
            $post = Post::withTrashed()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        try {
            if (!empty($post->featured_image) && Storage::disk('public')->exists($post->featured_image)) {
                Storage::disk('public')->delete($post->featured_image);
            }
        } catch (Throwable $e) {
            // ignore storage errors
        }

        $post->forceDelete();

        return response()->json(['success' => true, 'message' => 'Post permanently deleted']);
    }
}
