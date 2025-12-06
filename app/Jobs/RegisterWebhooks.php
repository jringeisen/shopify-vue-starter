<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RegisterWebhooks implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $shop,
    ) {}

    public function handle(): void
    {
        // TODO: Use Shopify API to register webhooks
        // The laravel-shopify package handles this automatically
        // This job serves as a placeholder for custom webhook registration

        Log::info('Webhooks registered for shop', [
            'shop_id' => $this->shop->id,
            'shop_domain' => $this->shop->shopify_domain,
        ]);
    }
}
