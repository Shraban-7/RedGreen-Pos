import { defineStore } from "pinia";
import api from "@/config/axios";

export const useDashboardStore = defineStore("dashboard", {
    state: () => ({
        data: null,
        loading: false,
    }),

    actions: {
        async fetchDashboard() {
            this.loading = true;
            try {
                const res = await api.get("/dashboard");
                this.data = res.data.data;
            } finally {
                this.loading = false;
            }
        },
    },
});