<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where('name', 'like', "%{$q}%");
        }

        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        $categories = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories)->response()->getData(true),
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        // optional image handling
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->storePublicly('categories', ['disk' => 'public']);
        }

        $category = Category::create($data);

        return response()->json(['success' => true, 'message' => 'Category created', 'data' => new CategoryResource($category)], 201);
    }

    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json(['success' => true, 'data' => new CategoryResource($category)]);
    }

    public function update(CategoryRequest $request, $id)
    {
        try {
            $category = Category::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if (!empty($category->image) && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->storePublicly('categories', ['disk' => 'public']);
        }

        $category->fill($data);
        $category->save();

        return response()->json(['success' => true, 'message' => 'Category updated', 'data' => new CategoryResource($category)]);
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Category deleted']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Invalid ids'], 400);
        }

        $deleted = Category::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => "Deleted {$deleted} categories"]);
    }

    public function restore($id)
    {
        try {
            $category = Category::withTrashed()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        if (!$category->trashed()) {
            return response()->json(['success' => false, 'message' => 'Category is not deleted'], 400);
        }

        $category->restore();

        return response()->json(['success' => true, 'message' => 'Category restored', 'data' => new CategoryResource($category)]);
    }

    public function forceDelete($id)
    {
        try {
            $category = Category::withTrashed()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        try {
            if (!empty($category->image) && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
        } catch (Throwable $e) {
            // ignore
        }

        $category->forceDelete();

        return response()->json(['success' => true, 'message' => 'Category permanently deleted']);
    }
}
