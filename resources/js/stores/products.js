import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useProductsStore = defineStore('products', () => {
    const products = ref([]);
    const currentProduct = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref(null);

    async function fetchProducts(page = 1) {
        loading.value = true;
        error.value = null;

        try {
            const response = await window.axios.get(`/products?page=${page}`);
            products.value = response.data.data;
            pagination.value = response.data.meta;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch products';
            console.error('Error fetching products:', err);
        } finally {
            loading.value = false;
        }
    }

    async function fetchProduct(id) {
        loading.value = true;
        error.value = null;

        try {
            const response = await window.axios.get(`/products/${id}`);
            currentProduct.value = response.data.data;
            return response.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch product';
            console.error('Error fetching product:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function createProduct(productData) {
        loading.value = true;
        error.value = null;

        try {
            const response = await window.axios.post('/products', productData);
            products.value.unshift(response.data.data);
            return response.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create product';
            console.error('Error creating product:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function updateProduct(id, productData) {
        loading.value = true;
        error.value = null;

        try {
            const response = await window.axios.put(`/products/${id}`, productData);
            const index = products.value.findIndex((p) => p.id === id);
            if (index !== -1) {
                products.value[index] = response.data.data;
            }
            currentProduct.value = response.data.data;
            return response.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update product';
            console.error('Error updating product:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function deleteProduct(id) {
        loading.value = true;
        error.value = null;

        try {
            await window.axios.delete(`/products/${id}`);
            products.value = products.value.filter((p) => p.id !== id);
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete product';
            console.error('Error deleting product:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    }

    return {
        products,
        currentProduct,
        loading,
        error,
        pagination,
        fetchProducts,
        fetchProduct,
        createProduct,
        updateProduct,
        deleteProduct,
    };
});
