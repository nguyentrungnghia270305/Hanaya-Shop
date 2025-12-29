<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class ProductController extends Controller
{
    /**
     * List products with optional filters and pagination.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $perPage = (int) $request->get('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)->response()->getData(true),
        ]);
    }

    /**
     * Store a new product.
     */
    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->storePublicly('products', ['disk' => 'public']);
            $data['image'] = $path;
        }

        // Auto-generate SKU if missing
        if (empty($data['sku'])) {
            $data['sku'] = strtoupper(Str::random(8));
        }

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Product created',
            'data' => new ProductResource($product),
        ], 201);
    }

    /**
     * Show single product.
     */
    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'data' => new ProductResource($product)]);
    }

    /**
     * Update product.
     */
    public function update(ProductRequest $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            // delete old image if exists
            if (!empty($product->image) && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->storePublicly('products', ['disk' => 'public']);
        }

        $product->fill($data);
        $product->save();

        return response()->json(['success' => true, 'message' => 'Product updated', 'data' => new ProductResource($product)]);
    }

    /**
     * Soft delete product.
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted']);
    }

    /**
     * Bulk delete (soft) by ids array.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Invalid ids provided'], 400);
        }

        $deleted = Product::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => "Deleted {$deleted} products"]);
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restore($id)
    {
        try {
            $product = Product::withTrashed()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        if (!$product->trashed()) {
            return response()->json(['success' => false, 'message' => 'Product is not deleted'], 400);
        }

        $product->restore();

        return response()->json(['success' => true, 'message' => 'Product restored', 'data' => new ProductResource($product)]);
    }

    /**
     * Force delete (permanent) product.
     */
    public function forceDelete($id)
    {
        try {
            $product = Product::withTrashed()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        // remove image if exists
        try {
            if (!empty($product->image) && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
        } catch (Throwable $e) {
            // ignore storage errors
        }

        $product->forceDelete();

        return response()->json(['success' => true, 'message' => 'Product permanently deleted']);
    }
}
