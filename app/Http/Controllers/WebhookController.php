<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhook;
use App\Models\User;
use App\Models\WebhookLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request, string $type): JsonResponse
    {
        $shopDomain = $request->header('X-Shopify-Shop-Domain');

        $shop = $shopDomain
            ? User::where('name', $shopDomain)->first()
            : null;

        $log = WebhookLog::create([
            'shop_id' => $shop?->id,
            'topic' => $type,
            'payload' => $request->all(),
            'status' => 'pending',
        ]);

        ProcessWebhook::dispatch($log);

        return response()->json(['message' => 'Webhook received'], 200);
    }
}
