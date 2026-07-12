<script setup>
import { ref, onMounted } from 'vue';
import { useOrderStore } from '@/stores/order';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const store = useOrderStore();
const toast = useToast();
const router = useRouter();

const search = ref('');

const fetchOrders = async () => {
    try {
        await store.fetchOrders({ search: search.value });
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Failed",
            detail: "Could not load orders",
            life: 2500,
        });
    }
};

const onSearch = () => {
    fetchOrders();
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

onMounted(fetchOrders);
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Manage Orders</h1>
        </div>

        <div class="bg-white border rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Order List</h2>
                <span class="p-input-icon-left">
                    <InputText v-model="search" placeholder="Search orders..." @input="onSearch" />
                </span>
            </div>

            <DataTable :value="store.orders" stripedRows paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]"
                tableStyle="min-width: 50rem;" :loading="store.loading">
                <Column field="sale_code" header="Order Code"></Column>
                <Column header="Customer">
                    <template #body="{ data }">
                        {{ data.customer?.name || 'Walk-in Customer' }}
                    </template>
                </Column>
                <Column header="Items">
                    <template #body="{ data }">
                        {{ data.items?.length || 0 }} item(s)
                    </template>
                </Column>
                <Column header="Total">
                    <template #body="{ data }">
                        ৳ {{ Number(data.pricing?.grand_total || 0).toFixed(2) }}
                    </template>
                </Column>
                <Column header="Paid">
                    <template #body="{ data }">
                        ৳ {{ Number(data.pricing?.paid_amount || 0).toFixed(2) }}
                    </template>
                </Column>
                <Column header="Due">
                    <template #body="{ data }">
                        <span :class="Number(data.pricing?.due_amount || 0) > 0 ? 'text-red-600 font-semibold' : 'text-green-600'">
                            ৳ {{ Number(data.pricing?.due_amount || 0).toFixed(2) }}
                        </span>
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="{ data }">
                        <Tag :value="formatStatus(data.status)" :severity="getStatusSeverity(data.status)" />
                    </template>
                </Column>
                <Column field="created_at" header="Date"></Column>
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex items-center gap-3">
                            <button @click="router.push(`/orders/${data.id}`)"
                                class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                View
                            </button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>