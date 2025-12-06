<template>
  <div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Dashboard
        </h1>
        <p v-if="shopStore.shop" class="mt-2 text-gray-600 dark:text-gray-400">
          Welcome to {{ shopStore.shop.name }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            Products
          </h3>
          <p class="text-3xl font-bold text-blue-600">{{ productsCount }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            Shop Domain
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ shopStore.shop?.shopify_domain || 'Loading...' }}
          </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            Status
          </h3>
          <p class="text-sm font-medium text-green-600">Active</p>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
          Quick Actions
        </h2>
        <div class="flex flex-wrap gap-4">
          <router-link
            to="/products"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition"
          >
            View Products
          </router-link>
          <router-link
            to="/products/create"
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition"
          >
            Create Product
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useShopStore } from '@/stores/shop';
import { useProductsStore } from '@/stores/products';

const shopStore = useShopStore();
const productsStore = useProductsStore();

const productsCount = computed(() => {
  return productsStore.pagination?.total ?? productsStore.products.length;
});

onMounted(async () => {
  try {
    await productsStore.fetchProducts();
  } catch (error) {
    console.error('Error loading products:', error);
  }
});
</script>
