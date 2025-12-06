<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useShopStore } from '@/stores/shop';

const shopStore = useShopStore();

onMounted(() => {
  shopStore.fetchShop();
});
</script>
