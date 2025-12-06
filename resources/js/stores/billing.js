import { defineStore } from 'pinia'
import axios from 'axios'

export const useBillingStore = defineStore('billing', {
    state: () => ({
        plans: [],
        status: null,
        loading: false,
        error: null,
    }),

    getters: {
        hasSubscription: (state) => state.status?.has_subscription || false,
        currentPlan: (state) => state.status?.plan || null,
    },

    actions: {
        async fetchPlans() {
            this.loading = true
            this.error = null

            try {
                const response = await axios.get('/billing/plans')
                this.plans = response.data.data
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch plans'
                console.error('Error fetching plans:', error)
            } finally {
                this.loading = false
            }
        },

        async fetchStatus() {
            this.loading = true
            this.error = null

            try {
                const response = await axios.get('/billing/status')
                this.status = response.data.data
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch billing status'
                console.error('Error fetching billing status:', error)
            } finally {
                this.loading = false
            }
        },

        async subscribe(planId) {
            this.loading = true
            this.error = null

            try {
                const response = await axios.post('/billing/subscribe', {
                    plan_id: planId,
                    host: new URLSearchParams(window.location.search).get('host') || '',
                })

                const billingUrl = response.data.data.billing_url

                if (billingUrl) {
                    window.top.location.href = billingUrl
                }

                return response.data
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to initiate subscription'
                console.error('Error subscribing:', error)
                throw error
            } finally {
                this.loading = false
            }
        },
    },
})
