<script setup>
import { onMounted, computed } from 'vue'
import { useBillingStore } from '@/stores/billing'

const billingStore = useBillingStore()

const plans = computed(() => billingStore.plans)
const loading = computed(() => billingStore.loading)
const error = computed(() => billingStore.error)
const hasSubscription = computed(() => billingStore.hasSubscription)
const currentPlan = computed(() => billingStore.currentPlan)

const formatPrice = (price, interval) => {
    const formattedPrice = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price)

    if (interval === 'ANNUAL') {
        return `${formattedPrice}/year`
    }
    return `${formattedPrice}/month`
}

const formatInterval = (interval) => {
    switch (interval) {
        case 'ANNUAL':
            return 'Billed annually'
        case 'EVERY_30_DAYS':
            return 'Billed monthly'
        default:
            return interval
    }
}

const handleSubscribe = async (planId) => {
    try {
        await billingStore.subscribe(planId)
    } catch (err) {
        console.error('Subscription failed:', err)
    }
}

const isCurrentPlan = (planId) => {
    return currentPlan.value?.id === planId
}

onMounted(async () => {
    await Promise.all([
        billingStore.fetchPlans(),
        billingStore.fetchStatus(),
    ])
})
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Choose Your Plan
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    Select the plan that best fits your needs
                </p>
            </div>

            <div v-if="hasSubscription" class="mb-8 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-200">
                        You're currently subscribed to the <strong>{{ currentPlan?.name }}</strong> plan
                    </p>
                </div>
            </div>

            <div v-if="error" class="mb-8 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-800 dark:text-red-200">{{ error }}</p>
            </div>

            <div v-if="loading && plans.length === 0" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-indigo-600"></div>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Loading plans...</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden"
                    :class="{
                        'ring-2 ring-indigo-500': isCurrentPlan(plan.id),
                    }"
                >
                    <div class="p-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ plan.name }}
                        </h2>

                        <div class="mt-4">
                            <span class="text-4xl font-extrabold text-gray-900 dark:text-white">
                                {{ formatPrice(plan.price, plan.interval).split('/')[0] }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400">
                                /{{ plan.interval === 'ANNUAL' ? 'year' : 'month' }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ formatInterval(plan.interval) }}
                        </p>

                        <div v-if="plan.trial_days" class="mt-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                                {{ plan.trial_days }} day free trial
                            </span>
                        </div>

                        <ul class="mt-8 space-y-4">
                            <li class="flex items-start">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-3 text-gray-600 dark:text-gray-300">
                                    Full product management
                                </span>
                            </li>
                            <li class="flex items-start">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-3 text-gray-600 dark:text-gray-300">
                                    Shopify sync
                                </span>
                            </li>
                            <li class="flex items-start">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-3 text-gray-600 dark:text-gray-300">
                                    Webhook support
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div class="px-8 pb-8">
                        <button
                            @click="handleSubscribe(plan.id)"
                            :disabled="loading || isCurrentPlan(plan.id)"
                            class="w-full py-3 px-4 rounded-lg font-semibold text-white transition-colors duration-200"
                            :class="{
                                'bg-gray-400 cursor-not-allowed': isCurrentPlan(plan.id),
                                'bg-indigo-600 hover:bg-indigo-700': !isCurrentPlan(plan.id) && !loading,
                                'bg-indigo-400 cursor-wait': loading && !isCurrentPlan(plan.id),
                            }"
                        >
                            <span v-if="isCurrentPlan(plan.id)">Current Plan</span>
                            <span v-else-if="loading">Processing...</span>
                            <span v-else>Subscribe</span>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="plans.length === 0 && !loading" class="text-center py-12">
                <p class="text-gray-600 dark:text-gray-400">
                    No billing plans available. Please configure billing plans in the database.
                </p>
            </div>
        </div>
    </div>
</template>
