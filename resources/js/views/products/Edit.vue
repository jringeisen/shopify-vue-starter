<template>
  <div class="min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
        Edit Product
      </h1>

      <div v-if="loadingProduct" class="text-center py-12">
        <p class="text-gray-600 dark:text-gray-400">Loading product...</p>
      </div>

      <div v-else-if="!form.title" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
        <p class="text-red-800 dark:text-red-200">Product not found</p>
      </div>

      <template v-else>
        <div v-if="error" class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ error }}</p>
              <ul v-if="validationErrors && Object.keys(validationErrors).length" class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                <li v-for="(messages, field) in validationErrors" :key="field">
                  {{ messages[0] }}
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form @submit.prevent="handleSubmit">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Title *
              </label>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Description
              </label>
              <textarea
                v-model="form.description"
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Price
                </label>
                <input
                  v-model.number="form.price"
                  type="number"
                  step="0.01"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Status
                </label>
                <select
                  v-model="form.status"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                >
                  <option value="draft">Draft</option>
                  <option value="active">Active</option>
                </select>
              </div>
            </div>

            <div class="flex justify-between">
              <router-link
                to="/products"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
              >
                Cancel
              </router-link>
              <button
                type="submit"
                :disabled="loading"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition disabled:opacity-50"
              >
                {{ loading ? 'Updating...' : 'Update Product' }}
              </button>
            </div>
          </div>
        </form>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProductsStore } from '@/stores/products';

const route = useRoute();
const router = useRouter();
const productsStore = useProductsStore();
const loading = ref(false);
const loadingProduct = ref(true);
const error = ref(null);
const validationErrors = ref(null);

const form = reactive({
  title: '',
  description: '',
  price: null,
  status: 'draft',
});

onMounted(async () => {
  try {
    const product = await productsStore.fetchProduct(route.params.id);
    Object.assign(form, {
      title: product.title,
      description: product.description || '',
      price: product.price,
      status: product.status,
    });
  } catch (err) {
    error.value = 'Failed to load product';
    console.error('Error loading product:', err);
  } finally {
    loadingProduct.value = false;
  }
});

async function handleSubmit() {
  loading.value = true;
  error.value = null;
  validationErrors.value = null;

  try {
    await productsStore.updateProduct(route.params.id, form);
    router.push('/products');
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update product';
    validationErrors.value = err.response?.data?.errors || null;
  } finally {
    loading.value = false;
  }
}
</script>
