<script setup>
import { ref, onMounted, computed } from "vue";
import { usePosStore } from "@/stores/pos";
import { useToast } from "primevue/usetoast";
import api from "@/config/axios";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Dialog from "primevue/dialog";

const posStore = usePosStore();
const toast = useToast();

const searchTerm = ref("");
const showReceipt = ref(false);
const selectedCustomer = ref("");
const paymentMethod = ref("cash");
const paidAmount = ref("");
const customers = ref([]);

onMounted(async () => {
    await posStore.fetchSellableProducts();
    await fetchCustomers();
});

const fetchCustomers = async () => {
    try {
        const res = await api.get("/suppliers");
        customers.value = res.data.data;
    } catch (err) {
        console.error(err);
    }
};

const filteredProducts = computed(() => {
    return posStore.products.filter(
        (p) => p.name?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        p.sku?.toLowerCase().includes(searchTerm.value.toLowerCase())
    );
});

const addToCart = (product) => {
    posStore.addToCart(product, 1);
};

const removeFromCart = (productId) => {
    posStore.removeItem(productId);
};

const updateQuantity = (productId, quantity) => {
    posStore.updateQty(productId, parseInt(quantity));
};

const checkout = async () => {
    try {
        const res = await posStore.checkout({
            customer_id: selectedCustomer.value,
            payment_method: paymentMethod.value,
            paid_amount: parseFloat(paidAmount.value),
        });

        toast.add({
            severity: "success",
            summary: "Success",
            detail: "Sale completed successfully",
            life: 3000,
        });
        showReceipt.value = true;
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to complete sale",
            life: 3000,
        });
    }
};

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <div class="space-y-6">
        <!-- HEADER -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">POS - New Sale</h1>
            <Button 
                v-if="posStore.receipt" 
                label="View Receipt" 
                icon="pi pi-print" 
                @click="showReceipt = true" 
                class="bg-blue-600 hover:bg-blue-700"
            />
        </div>

        <div class="grid grid-cols-3 gap-6">
            <!-- PRODUCTS PANEL -->
            <div class="col-span-2 bg-white border rounded-xl shadow p-4">
                <div class="mb-4">
                    <input 
                        v-model="searchTerm" 
                        placeholder="Search products..." 
                        class="w-full p-3 bg-gray-50 border rounded-lg"
                    />
                </div>

                <DataTable 
                    :value="filteredProducts" 
                    stripedRows 
                    :rows="10"
                    tableStyle="min-width: 50rem;"
                >
                    <Column field="name" header="Product">
                        <template #body="{ data }">
                            <div>
                                <p class="font-medium">{{ data.name }}</p>
                                <p class="text-sm text-gray-500">{{ data.sku }}</p>
                            </div>
                        </template>
                    </Column>
                    <Column field="pricing.selling_price" header="Price">
                        <template #body="{ data }">
                            ৳ {{ data.pricing?.selling_price || 0 }}
                        </template>
                    </Column>
                    <Column header="Stock">
                        <template #body="{ data }">
                            {{ data.stock?.available_quantity || 0 }}
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="{ data }">
                            <Button 
                                icon="pi pi-plus" 
                                @click="addToCart(data)" 
                                class="p-button-rounded p-button-success p-button-sm"
                                :disabled="(data.stock?.available_quantity || 0) <= 0"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- CART PANEL -->
            <div class="bg-white border rounded-xl shadow p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Cart</h2>

                <div v-if="posStore.cart.length === 0" class="text-center py-8 text-gray-500">
                    Cart is empty
                </div>

                <div v-else class="space-y-3 max-h-96 overflow-y-auto">
                    <div 
                        v-for="item in posStore.cart" 
                        :key="item.product_id" 
                        class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                    >
                        <div class="flex-1">
                            <p class="font-medium">{{ item.name }}</p>
                            <p class="text-sm text-gray-500">৳ {{ item.unit_price }} x {{ item.quantity }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input 
                                type="number" 
                                v-model="item.quantity" 
                                @change="updateQuantity(item.product_id, item.quantity)"
                                min="1" 
                                class="w-16 p-1 border rounded"
                            />
                            <span class="font-bold">৳ {{ item.total }}</span>
                            <Button 
                                icon="pi pi-times" 
                                @click="removeFromCart(item.product_id)" 
                                class="p-button-rounded p-button-danger p-button-sm"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="posStore.cart.length > 0" class="border-t pt-4 mt-4 space-y-2">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span class="font-bold">৳ {{ posStore.subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Total Items:</span>
                        <span class="font-bold">{{ posStore.totalItems }}</span>
                    </div>
                    <div class="flex justify-between text-lg">
                        <span class="font-semibold">Grand Total:</span>
                        <span class="font-bold text-blue-600">৳ {{ posStore.grandTotal.toFixed(2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHECKOUT FORM -->
        <div v-if="posStore.cart.length > 0" class="bg-white border rounded-xl shadow p-6">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block mb-1 font-medium">Customer (Optional)</label>
                    <select v-model="selectedCustomer" class="w-full p-3 bg-gray-50 border rounded-lg">
                        <option value="">Walk-in Customer</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">
                            {{ c.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-medium">Payment Method</label>
                    <select v-model="paymentMethod" class="w-full p-3 bg-gray-50 border rounded-lg">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="mobile_banking">Mobile Banking</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 font-medium">Paid Amount</label>
                    <input 
                        v-model="paidAmount" 
                        type="number" 
                        step="0.01" 
                        class="w-full p-3 bg-gray-50 border rounded-lg" 
                    />
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <Button 
                    label="Complete Sale" 
                    icon="pi pi-check" 
                    @click="checkout" 
                    :loading="posStore.loading"
                    class="bg-green-600 hover:bg-green-700"
                />
            </div>
        </div>

        <!-- RECEIPT DIALOG -->
        <Dialog 
            v-model:visible="showReceipt" 
            modal 
            header="Receipt" 
            :style="{ width: '500px' }"
        >
            <div v-if="posStore.receipt" class="space-y-4">
                <div class="text-center">
                    <h3 class="text-xl font-bold">SALE {{ posStore.receipt.sale_code }}</h3>
                    <p class="text-sm text-gray-500">{{ posStore.receipt.created_at }}</p>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left">Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in posStore.receipt.items" :key="item.id" class="border-b">
                            <td>{{ item.product?.name }}</td>
                            <td class="text-center">{{ item.quantity }}</td>
                            <td class="text-right">৳ {{ item.total }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="border-t pt-4 space-y-2">
                    <div class="flex justify-between">
                        <span>Total:</span>
                        <span class="font-bold">৳ {{ posStore.receipt.pricing?.grand_total }}</span>
                    </div>
                    <div class="flex justify-between" v-if="posStore.receipt.pricing?.discount_amount > 0">
                        <span>Discount:</span>
                        <span>৳ {{ posStore.receipt.pricing.discount_amount }}</span>
                    </div>
                    <div class="flex justify-between" v-if="posStore.receipt.pricing?.vat_amount > 0">
                        <span>VAT:</span>
                        <span>৳ {{ posStore.receipt.pricing.vat_amount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Paid:</span>
                        <span>৳ {{ posStore.receipt.pricing?.paid_amount }}</span>
                    </div>
                    <div class="flex justify-between" v-if="posStore.receipt.pricing?.due_amount > 0">
                        <span class="font-semibold">Due:</span>
                        <span class="font-bold">৳ {{ posStore.receipt.pricing.due_amount }}</span>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button label="Close" icon="pi pi-times" @click="showReceipt = false" />
                    <Button label="Print" icon="pi pi-print" @click="printReceipt" class="bg-blue-600" />
                </div>
            </div>
        </Dialog>
    </div>
</template>

<style scoped>
@media print {
    .no-print {
        display: none;
    }
}
</style>
