import { defineStore } from "pinia";
import api from "@/config/axios";

export const useExpenseStore = defineStore("expense", {
    state: () => ({
        expenses: [],
        categories: [],
        expense: null,
        loading: false,
        total: 0,
    }),

    actions: {
        async fetchExpenses(params = {}) {
            this.loading = true;
            try {
                const res = await api.get("/expenses", { params });
                this.expenses = res.data.data;
                this.total = res.data.meta?.total ?? res.data.data.length;
            } finally {
                this.loading = false;
            }
        },

        async fetchCategories() {
            try {
                const res = await api.get("/expense-categories");
                this.categories = res.data.data;
            } catch (e) {
                this.categories = [];
            }
        },

        async fetchExpense(id) {
            this.loading = true;
            try {
                const res = await api.get(`/expenses/${id}`);
                this.expense = res.data.data;
                return res.data.data;
            } finally {
                this.loading = false;
            }
        },

        async createExpense(form) {
            this.loading = true;
            try {
                const fd = new FormData();
                Object.keys(form).forEach((key) => {
                    if (form[key] !== null && form[key] !== undefined && form[key] !== "") {
                        fd.append(key, form[key]);
                    }
                });

                const res = await api.post("/expenses", fd, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
                return res;
            } finally {
                this.loading = false;
            }
        },

        async updateExpense(id, form) {
            this.loading = true;
            try {
                const fd = new FormData();
                Object.keys(form).forEach((key) => {
                    if (form[key] !== null && form[key] !== undefined && form[key] !== "") {
                        fd.append(key, form[key]);
                    }
                });
                fd.append("_method", "PUT");

                const res = await api.post(`/expenses/${id}`, fd, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
                return res;
            } finally {
                this.loading = false;
            }
        },

        async deleteExpense(id) {
            this.loading = true;
            try {
                const res = await api.delete(`/expenses/${id}`);
                this.expenses = this.expenses.filter((e) => e.id !== id);
                return res;
            } finally {
                this.loading = false;
            }
        },
    },
});