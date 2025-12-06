import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: () => import('@/views/Dashboard.vue'),
    },
    {
        path: '/products',
        name: 'products.index',
        component: () => import('@/views/products/Index.vue'),
    },
    {
        path: '/products/create',
        name: 'products.create',
        component: () => import('@/views/products/Create.vue'),
    },
    {
        path: '/products/:id/edit',
        name: 'products.edit',
        component: () => import('@/views/products/Edit.vue'),
    },
    {
        path: '/billing',
        name: 'billing',
        component: () => import('@/views/Billing.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
