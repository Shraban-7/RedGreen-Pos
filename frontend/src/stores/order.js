import { defineStore } from "pinia";
import api from "@/config/axios";

export const useOrderStore = defineStore("order", {
    state: () => ({
        orders: [],
        order: null,
        loading: false,
        total: 0,
    }),

    actions: {
        async fetchOrders(params = {}) {
            this.loading = true;
            try {
                const res = await api.get("/orders", { params });
                this.orders = res.data.data;
                this.total = res.data.meta?.total ?? res.data.data.length;
            } finally {
                this.loading = false;
            }
        },

        async fetchOrder(id) {
            this.loading = true;
            try {
                const res = await api.get(`/orders/${id}`);
                this.order = res.data.data;
                return res.data.data;
            } finally {
                this.loading = false;
            }
        },
    },
});