import { defineStore } from "pinia";
import api from "@/config/axios";

export const usePosStore = defineStore("pos", {
    state: () => ({
        cart: [],
        products: [],
        customers: [],
        sales: [],
        sale: null,
        loading: false,
        receipt: null,
    }),

    getters: {
        subtotal: (state) => {
            return state.cart.reduce((sum, item) => sum + parseFloat(item.subtotal || 0), 0);
        },

        totalDiscount: (state) => {
            return state.cart.reduce((sum, item) => sum + parseFloat(item.discountAmount || 0), 0);
        },

        totalVat: (state) => {
            return state.cart.reduce((sum, item) => sum + parseFloat(item.vatAmount || 0), 0);
        },

        grandTotal: (state) => {
            const subtotal = state.cart.reduce((sum, item) => sum + parseFloat(item.subtotal || 0), 0);
            const discount = state.cart.reduce((sum, item) => sum + parseFloat(item.discountAmount || 0), 0);
            const vat = state.cart.reduce((sum, item) => sum + parseFloat(item.vatAmount || 0), 0);
            return (subtotal - discount) + vat;
        },

        totalItems: (state) => {
            return state.cart.reduce((sum, item) => sum + parseInt(item.quantity || 0), 0);
        },
    },

    actions: {
        async fetchSellableProducts() {
            this.loading = true;
            try {
                const res = await api.get("/pos/products");
                this.products = res.data.data;
            } finally {
                this.loading = false;
            }
        },

        async fetchCustomers() {
            this.loading = true;
            try {
                const res = await api.get("/suppliers");
                this.customers = res.data.data;
            } finally {
                this.loading = false;
            }
        },

        addToCart(product, quantity = 1) {
            const existingItem = this.cart.find((item) => item.product_id === product.id);

            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    sku: product.sku,
                    quantity: quantity,
                    unit_price: product.pricing?.selling_price || product.selling_price || 0,
                    subtotal: (product.pricing?.selling_price || product.selling_price || 0) * quantity,
                    discountAmount: 0,
                    vatAmount: 0,
                    total: (product.pricing?.selling_price || product.selling_price || 0) * quantity,
                });
            }
        },

        updateQty(itemId, quantity) {
            const item = this.cart.find((i) => i.product_id === itemId);
            if (item) {
                item.quantity = quantity;
                item.subtotal = item.unit_price * quantity;
                item.total = item.subtotal;
            }
        },

        removeItem(productId) {
            this.cart = this.cart.filter((item) => item.product_id !== productId);
        },

        applyDiscount(productId, discountType, discountValue) {
            const item = this.cart.find((i) => i.product_id === productId);
            if (item) {
                const discountAmount = this.calculateDiscountAmount(item.subtotal, discountType, discountValue);
                item.discountAmount = discountAmount;
                item.total = item.subtotal - discountAmount;
            }
        },

        calculateDiscountAmount(price, type, value) {
            if (!type || !value) return 0;
            return type === "percentage" ? (price * value) / 100 : value;
        },

        async checkout(form) {
            this.loading = true;
            try {
                const res = await api.post("/pos/sales", {
                    ...form,
                    items: this.cart.map((item) => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                    })),
                });
                this.receipt = res.data.data;
                this.cart = [];
                return res;
            } finally {
                this.loading = false;
            }
        },

        async fetchSales() {
            this.loading = true;
            try {
                const res = await api.get("/pos/sales");
                this.sales = res.data.data;
            } finally {
                this.loading = false;
            }
        },

        async loadSale(id) {
            this.loading = true;
            try {
                const res = await api.get(`/pos/sales/${id}`);
                this.sale = res.data.data;
                return res.data.data;
            } finally {
                this.loading = false;
            }
        },

        async updateSale(id, form) {
            this.loading = true;
            try {
                const res = await api.put(`/pos/sales/${id}`, form);
                this.sale = res.data.data;
                return res;
            } finally {
                this.loading = false;
            }
        },

        async voidSale(id) {
            this.loading = true;
            try {
                const res = await api.post(`/pos/sales/${id}/void`);
                return res;
            } finally {
                this.loading = false;
            }
        },

        async refundItems(id, items) {
            this.loading = true;
            try {
                const res = await api.post(`/pos/sales/${id}/refund`, { items });
                return res;
            } finally {
                this.loading = false;
            }
        },

        async addPayment(id, form) {
            this.loading = true;
            try {
                const res = await api.post(`/pos/sales/${id}/payments`, form);
                this.sale = res.data.data;
                return res;
            } finally {
                this.loading = false;
            }
        },
    },
});