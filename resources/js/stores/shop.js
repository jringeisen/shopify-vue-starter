import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useShopStore = defineStore('shop', () => {
    const shop = ref(null);
    const loading = ref(false);
    const error = ref(null);

    async function fetchShop() {
        loading.value = true;
        error.value = null;

        try {
            const response = await window.axios.get('/shop');
            shop.value = response.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch shop details';
            console.error('Error fetching shop:', err);
        } finally {
            loading.value = false;
        }
    }

    return {
        shop,
        loading,
        error,
        fetchShop,
    };
});
