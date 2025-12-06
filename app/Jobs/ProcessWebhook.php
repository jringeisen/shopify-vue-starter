<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\WebhookLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public WebhookLog $log,
    ) {}

    public function handle(): void
    {
        try {
            match ($this->log->topic) {
                'app/uninstalled' => $this->handleAppUninstalled(),
                'products/create' => $this->handleProductsCreate(),
                'products/update' => $this->handleProductsUpdate(),
                'products/delete' => $this->handleProductsDelete(),
                default => Log::info("Unhandled webhook: {$this->log->topic}"),
            };

            $this->log->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->log->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'processed_at' => now(),
            ]);

            Log::error("Webhook processing failed: {$e->getMessage()}", [
                'log_id' => $this->log->id,
                'topic' => $this->log->topic,
            ]);
        }
    }

    protected function handleAppUninstalled(): void
    {
        if (! $this->log->shop_id) {
            Log::warning('App uninstalled webhook received without shop association');

            return;
        }

        $shop = $this->log->shop;

        if ($shop) {
            $shop->products()->delete();

            $shop->forceFill([
                'shopify_token' => null,
            ])->save();

            Log::info('App uninstalled - cleaned up shop data', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->name,
            ]);
        }
    }

    protected function handleProductsCreate(): void
    {
        if (! $this->log->shop_id) {
            Log::warning('Product create webhook received without shop association');

            return;
        }

        $payload = $this->log->payload;

        Product::updateOrCreate(
            [
                'shop_id' => $this->log->shop_id,
                'shopify_product_id' => $payload['id'],
            ],
            $this->mapProductPayload($payload)
        );

        Log::info('Product created from Shopify webhook', [
            'shop_id' => $this->log->shop_id,
            'shopify_product_id' => $payload['id'],
        ]);
    }

    protected function handleProductsUpdate(): void
    {
        if (! $this->log->shop_id) {
            Log::warning('Product update webhook received without shop association');

            return;
        }

        $payload = $this->log->payload;

        $product = Product::where('shop_id', $this->log->shop_id)
            ->where('shopify_product_id', $payload['id'])
            ->first();

        if ($product) {
            $product->update($this->mapProductPayload($payload));

            Log::info('Product updated from Shopify webhook', [
                'product_id' => $product->id,
                'shopify_product_id' => $payload['id'],
            ]);
        } else {
            Product::create([
                'shop_id' => $this->log->shop_id,
                'shopify_product_id' => $payload['id'],
                ...$this->mapProductPayload($payload),
            ]);

            Log::info('Product created from update webhook (did not exist locally)', [
                'shop_id' => $this->log->shop_id,
                'shopify_product_id' => $payload['id'],
            ]);
        }
    }

    protected function handleProductsDelete(): void
    {
        if (! $this->log->shop_id) {
            Log::warning('Product delete webhook received without shop association');

            return;
        }

        $payload = $this->log->payload;

        $deleted = Product::where('shop_id', $this->log->shop_id)
            ->where('shopify_product_id', $payload['id'])
            ->delete();

        Log::info('Product deleted from Shopify webhook', [
            'shop_id' => $this->log->shop_id,
            'shopify_product_id' => $payload['id'],
            'deleted' => $deleted > 0,
        ]);
    }

    /**
     * Map Shopify webhook payload to local product attributes.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function mapProductPayload(array $payload): array
    {
        $price = null;
        if (isset($payload['variants'][0]['price'])) {
            $price = $payload['variants'][0]['price'];
        }

        $tags = [];
        if (! empty($payload['tags'])) {
            $tags = array_map('trim', explode(',', $payload['tags']));
        }

        return [
            'title' => $payload['title'] ?? '',
            'description' => $payload['body_html'] ?? null,
            'price' => $price,
            'vendor' => $payload['vendor'] ?? null,
            'product_type' => $payload['product_type'] ?? null,
            'tags' => $tags,
            'status' => $payload['status'] ?? 'draft',
        ];
    }
}
