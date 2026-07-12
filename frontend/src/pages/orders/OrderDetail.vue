<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useOrderStore } from '@/stores/order';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const route = useRoute();
const router = useRouter();
const store = useOrderStore();
const toast = useToast();

const order = ref(null);

const fetchOrder = async () => {
    try {
        order.value = await store.fetchOrder(route.params.id);
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Failed",
            detail: "Could not load order details",
            life: 2500,
        });
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'completed': return 'success';
        case 'voided': return 'danger';
        case 'refunded': return 'warn';
        case 'partial_refund': return 'warn';
        default: return 'info';
    }
};

const formatStatus = (status) => {
    return status ? status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '—';
};

onMounted(fetchOrder);
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Button icon="pi pi-arrow-left" text @click="router.push('/orders')" />
                <h1 class="text-2xl font-bold text-gray-900">Order Details</h1>
            </div>
        </div>

        <div v-if="order" class="space-y-6">
            <!-- Order Header -->
            <div class="bg-white border rounded-xl shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Order Code</p>
                        <p class="text-lg font-semibold text-gray-900">{{ order.sale_code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Customer</p>
                        <p class="text-lg font-semibold text-gray-900">{{ order.customer?.name || 'Walk-in Customer' }}</p>
                        <p v-if="order.customer?.phone" class="text-sm text-gray-500">{{ order.customer.phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <Tag :value="formatStatus(order.status)" :severity="getStatusSeverity(order.status)" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Date</p>
                        <p class="text-lg font-semibold text-gray-900">{{ order.created_at }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white border rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Items</h2>
                <DataTable :value="order.items" stripedRows tableStyle="min-width: 50rem;">
                    <Column header="#">
                        <template #body="{ index }">{{ index + 1 }}</template>
                    </Column>
                    <Column header="Product">
                        <template #body="{ data }">
                            {{ data.product?.name || '—' }}
                            <br>
                            <span class="text-sm text-gray-500">SKU: {{ data.product?.sku || '—' }}</span>
                        </template>
                    </Column>
                    <Column field="quantity" header="Qty"></Column>
                    <Column header="Unit Price">
                        <template #body="{ data }">
                            ৳ {{ Number(data.unit_price).toFixed(2) }}
                        </template>
                    </Column>
                    <Column header="Subtotal">
                        <template #body="{ data }">
                            ৳ {{ Number(data.subtotal).toFixed(2) }}
                        </template>
                    </Column>
                    <Column header="Discount">
                        <template #body="{ data }">
                            ৳ {{ Number(data.discount_amount).toFixed(2) }}
                        </template>
                    </Column>
                    <Column header="VAT">
                        <template #body="{ data }">
                            ৳ {{ Number(data.vat_amount).toFixed(2) }}
                        </template>
                    </Column>
                    <Column header="Total">
                        <template #body="{ data }">
                            ৳ {{ Number(data.total).toFixed(2) }}
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- Pricing Summary -->
            <div class="bg-white border rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Pricing Summary</h2>
                <div class="max-w-md space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">৳ {{ Number(order.pricing?.subtotal || 0).toFixed(2) }}</span>
                    </div>
                    <div v-if="order.pricing?.discount_amount > 0" class="flex justify-between">
                        <span class="text-gray-600">Discount</span>
                        <span class="font-medium text-red-600">- ৳ {{ Number(order.pricing?.discount_amount || 0).toFixed(2) }}</span>
                    </div>
                    <div v-if="order.pricing?.vat_amount > 0" class="flex justify-between">
                        <span class="text-gray-600">VAT ({{ order.pricing?.vat_percentage || 0 }}%)</span>
                        <span class="font-medium">৳ {{ Number(order.pricing?.vat_amount || 0).toFixed(2) }}</span>
                    </div>
                    <hr>
                    <div class="flex justify-between text-lg font-bold">
                        <span>Grand Total</span>
                        <span>৳ {{ Number(order.pricing?.grand_total || 0).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between text-green-600 font-semibold">
                        <span>Paid</span>
                        <span>৳ {{ Number(order.pricing?.paid_amount || 0).toFixed(2) }}</span>
                    </div>
                    <div v-if="order.pricing?.due_amount > 0" class="flex justify-between text-red-600 font-semibold">
                        <span>Due</span>
                        <span>৳ {{ Number(order.pricing?.due_amount || 0).toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payments -->
            <div v-if="order.payments?.length" class="bg-white border rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Payments</h2>
                <DataTable :value="order.payments" stripedRows tableStyle="min-width: 50rem;">
                    <Column field="payment_code" header="Payment Code"></Column>
                    <Column header="Amount">
                        <template #body="{ data }">
                            <span :class="data.type === 'refund' ? 'text-red-600' : 'text-green-600'">
                                ৳ {{ Number(data.amount).toFixed(2) }}
                            </span>
                        </template>
                    </Column>
                    <Column field="method" header="Method"></Column>
                    <Column field="type" header="Type"></Column>
                    <Column field="created_at" header="Date"></Column>
                </DataTable>
            </div>

            <!-- Notes -->
            <div v-if="order.notes" class="bg-white border rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Notes</h2>
                <p class="text-gray-700">{{ order.notes }}</p>
            </div>
        </div>

        <div v-else-if="store.loading" class="text-center py-12">
            <p class="text-gray-500">Loading order details...</p>
        </div>
    </div>
</template>