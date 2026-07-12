<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { usePosStore } from "@/stores/pos";
import { useToast } from "primevue/usetoast";
import api from "@/config/axios";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import ConfirmDialog from "primevue/confirmdialog";
import { useConfirm } from "primevue/useconfirm";

const route = useRoute();
const router = useRouter();
const posStore = usePosStore();
const toast = useToast();
const confirm = useConfirm();

const saleId = route.params.id;
const showPaymentDialog = ref(false);
const paymentAmount = ref("");
const paymentMethod = ref("cash");

onMounted(async () => {
    await posStore.loadSale(saleId);
});

const voidSale = () => {
    confirm.require({
        message: "Are you sure you want to void this sale? This will restore all stock.",
        header: "Void Sale",
        icon: "pi pi-exclamation-triangle",
        acceptClass: "p-button-danger",
        accept: async () => {
            try {
                const res = await posStore.voidSale(saleId);
                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Sale voided successfully",
                    life: 3000,
                });
            } catch (err) {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: err.response?.data?.message || "Failed to void sale",
                    life: 3000,
                });
            }
        },
    });
};

const addPayment = async () => {
    try {
        await posStore.addPayment(saleId, {
            amount: parseFloat(paymentAmount.value),
            payment_method: paymentMethod.value,
        });

        toast.add({
            severity: "success",
            summary: "Success",
            detail: "Payment added successfully",
            life: 3000,
        });
        showPaymentDialog.value = false;
        paymentAmount.value = "";
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: err.response?.data?.message || "Failed to add payment",
            life: 3000,
        });
    }
};
</script>

<template>
    <div class="space-y-6" v-if="posStore.sale">
        <!-- HEADER -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Sale Details</h1>
            <div class="flex gap-2">
                <Button 
                    v-if="posStore.sale.status === 'completed' && posStore.sale.due_amount > 0"
                    label="Add Payment" 
                    icon="pi pi-plus" 
                    @click="showPaymentDialog = true" 
                    class="bg-green-600 hover:bg-green-700"
                />
                <Button 
                    v-if="posStore.sale.status === 'completed'"
                    label="Void Sale" 
                    icon="pi pi-ban" 
                    @click="voidSale" 
                    class="p-button-danger"
                />
            </div>
        </div>

        <!-- SALE INFO -->
        <div class="bg-white border rounded-xl shadow p-6 space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Sale Code</p>
                    <p class="font-bold">{{ posStore.sale.sale_code }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Cashier</p>
                    <p class="font-bold">{{ posStore.sale.user?.name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Customer</p>
                    <p class="font-bold">{{ posStore.sale.customer?.name || 'Walk-in' }}</p>
                </div>
            </div>

            <div class="border-t pt-4">
                <h3 class="font-semibold mb-2">Items</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left">Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in posStore.sale.items" :key="item.id" class="border-b">
                            <td>{{ item.product?.name }}</td>
                            <td class="text-center">{{ item.quantity }}</td>
                            <td class="text-right">৳ {{ item.total }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="border-t pt-4 space-y-2">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>৳ {{ posStore.sale.pricing?.subtotal }}</span>
                </div>
                <div class="flex justify-between" v-if="posStore.sale.pricing?.discount_amount > 0">
                    <span>Discount:</span>
                    <span>৳ {{ posStore.sale.pricing.discount_amount }}</span>
                </div>
                <div class="flex justify-between" v-if="posStore.sale.pricing?.vat_amount > 0">
                    <span>VAT:</span>
                    <span>৳ {{ posStore.sale.pricing.vat_amount }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold border-t pt-2">
                    <span>Total:</span>
                    <span>৳ {{ posStore.sale.pricing?.grand_total }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Paid:</span>
                    <span>৳ {{ posStore.sale.pricing?.paid_amount }}</span>
                </div>
                <div class="flex justify-between" v-if="posStore.sale.pricing?.due_amount > 0">
                    <span class="font-semibold text-red-600">Due:</span>
                    <span class="font-bold text-red-600">৳ {{ posStore.sale.pricing.due_amount }}</span>
                </div>
            </div>
        </div>

        <!-- PAYMENTS TABLE -->
        <div class="bg-white border rounded-xl shadow p-6" v-if="posStore.sale.payments?.length">
            <h3 class="font-semibold mb-2">Payments</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="payment in posStore.sale.payments" :key="payment.id" class="border-b">
                        <td>{{ payment.created_at?.split('T')[0] }}</td>
                        <td>{{ payment.method }}</td>
                        <td class="text-right">৳ {{ payment.amount }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAYMENT DIALOG -->
        <Dialog 
            v-model:visible="showPaymentDialog" 
            modal 
            header="Add Payment" 
            :style="{ width: '400px' }"
        >
            <div class="space-y-4">
                <div>
                    <label class="block mb-1 font-medium">Amount</label>
                    <input 
                        v-model="paymentAmount" 
                        type="number" 
                        step="0.01" 
                        class="w-full p-3 bg-gray-50 border rounded-lg" 
                    />
                </div>
                <div>
                    <label class="block mb-1 font-medium">Payment Method</label>
                    <select v-model="paymentMethod" class="w-full p-3 bg-gray-50 border rounded-lg">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="mobile_banking">Mobile Banking</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2">
                    <Button label="Cancel" @click="showPaymentDialog = false" />
                    <Button label="Add Payment" @click="addPayment" class="bg-green-600" />
                </div>
            </div>
        </Dialog>

        <ConfirmDialog />
    </div>

    <div v-else class="flex justify-center items-center h-64">
        <p class="text-gray-500">Loading...</p>
    </div>
</template>

<style scoped></style>