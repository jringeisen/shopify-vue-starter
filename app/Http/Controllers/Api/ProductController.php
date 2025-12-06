<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Osiset\ShopifyApp\Services\ShopifyApp;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $request->user()
            ->products()
            ->latest()
            ->paginate(15);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        $product = $request->user()->products()->create($request->validated());

        return ProductResource::make($product);
    }

    public function show(Request $request, Product $product): ProductResource
    {
        $this->authorize('view', $product);

        return ProductResource::make($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $this->authorize('update', $product);

        $product->update($request->validated());

        return ProductResource::make($product->fresh());
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function syncWithShopify(Request $request, Product $product, ShopifyApp $shopify): ProductResource|JsonResponse
    {
        $this->authorize('update', $product);

        $shop = $request->user();
        $apiVersion = config('shopify-app.api_version');

        $productData = [
            'product' => [
                'title' => $product->title,
                'body_html' => $product->description,
                'vendor' => $product->vendor,
                'product_type' => $product->product_type,
                'tags' => is_array($product->tags) ? implode(', ', $product->tags) : $product->tags,
                'status' => $product->status,
                'variants' => [
                    [
                        'price' => $product->price,
                    ],
                ],
            ],
        ];

        if ($product->shopify_product_id) {
            $response = $shopify->setShop($shop)->rest(
                'PUT',
                "/admin/api/{$apiVersion}/products/{$product->shopify_product_id}.json",
                $productData
            );
        } else {
            $response = $shopify->setShop($shop)->rest(
                'POST',
                "/admin/api/{$apiVersion}/products.json",
                $productData
            );
        }

        if (isset($response['errors']) || ! isset($response['body']['product'])) {
            return response()->json([
                'message' => 'Failed to sync with Shopify',
                'errors' => $response['errors'] ?? $response['body'] ?? 'Unknown error',
            ], 422);
        }

        $shopifyProduct = $response['body']['product'];

        $product->update([
            'shopify_product_id' => $shopifyProduct['id'],
        ]);

        return ProductResource::make($product->fresh());
    }
}
