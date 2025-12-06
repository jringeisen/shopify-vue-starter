<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Osiset\ShopifyApp\Actions\GetPlanUrl;
use Osiset\ShopifyApp\Objects\Values\PlanId;
use Osiset\ShopifyApp\Storage\Models\Plan;

class BillingController extends Controller
{
    /**
     * Get all available billing plans.
     */
    public function plans(): JsonResponse
    {
        $plans = Plan::all()->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $plan->price,
                'interval' => $plan->interval,
                'trial_days' => $plan->trial_days,
                'type' => $plan->type,
                'on_install' => $plan->on_install,
            ];
        });

        return response()->json(['data' => $plans]);
    }

    /**
     * Get the current shop's subscription status.
     */
    public function status(Request $request): JsonResponse
    {
        $shop = $request->user();

        $activePlan = $shop->plan;
        $activeCharge = $shop->charges()
            ->where('status', 'ACTIVE')
            ->latest()
            ->first();

        return response()->json([
            'data' => [
                'has_subscription' => $activePlan !== null,
                'plan' => $activePlan ? [
                    'id' => $activePlan->id,
                    'name' => $activePlan->name,
                    'price' => $activePlan->price,
                    'interval' => $activePlan->interval,
                ] : null,
                'charge' => $activeCharge ? [
                    'id' => $activeCharge->id,
                    'status' => $activeCharge->status,
                    'activated_on' => $activeCharge->activated_on,
                    'billing_on' => $activeCharge->billing_on,
                ] : null,
            ],
        ]);
    }

    /**
     * Get the billing URL for a specific plan.
     *
     * This returns a URL that the frontend should redirect to for Shopify billing.
     */
    public function subscribe(Request $request, GetPlanUrl $getPlanUrl): JsonResponse
    {
        $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $shop = $request->user();
        $host = $request->get('host', '');

        $billingUrl = $getPlanUrl(
            $shop->getId(),
            PlanId::fromNative((int) $request->plan_id),
            $host
        );

        return response()->json([
            'data' => [
                'billing_url' => $billingUrl,
            ],
        ]);
    }
}
