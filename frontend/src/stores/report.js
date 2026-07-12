import { defineStore } from "pinia";
import api from "@/config/axios";

export const useReportStore = defineStore("report", {
    state: () => ({
        sales: null,
        customers: null,
        expenses: null,
        overall: null,
        loading: false,
    }),

    actions: {
        async fetchSales(params = {}) {
            this.loading = true;
            try {
                const res = await api.get("/reports/sales", { params });
                this.sales = res.data.data;
            } finally {
                this.loading = false;
            }
        },

        async fetchCustomers(params = {}) {
            this.loading = true;
            try {
                const res = await api.get("/reports/customers", { params });
                this.customers = res.data.data;
            } finally {
                this.loading = false;
            }
        },

        async fetchExpenses(params = {}) {
            this.loading = true;
            try {
                const res = await api.get("/reports/expenses", { params });
                this.expenses = res.data.data;
            } finally {
                this.loading = false;
            }
        },

        async fetchOverall(params = {}) {
            this.loading = true;
            try {
                const res = await api.get("/reports/overall", { params });
                this.overall = res.data.data;
            } finally {
                this.loading = false;
            }
        },
    },
});