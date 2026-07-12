<script setup>
import { ref, onMounted } from "vue";
import { usePosStore } from "@/stores/pos";
import { useRouter } from "vue-router";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Badge from "primevue/badge";

const posStore = usePosStore();
const router = useRouter();

onMounted(() => {
    posStore.fetchSales();
});

const viewSale = (sale) => {
    router.push(`/pos/sales/${sale.id}`);
};
</script>

<template>
    <div class="space-y-6">
        <!-- HEADER -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Sales History</h1>
            <Button 
                label="New Sale" 
                icon="pi pi-plus" 
                @click="$router.push('/pos/create')" 
                class="bg-blue-600 hover:bg-blue-700"
            />
        </div>

        <!-- SALES TABLE -->
        <div class="bg-white border rounded-xl shadow p-6">
            <DataTable 
                :value="posStore.sales" 
                stripedRows 
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50]"
                tableStyle="min-width: 50rem;"
            >
                <Column field="sale_code" header="Sale Code"></Column>
                <Column field="user.name" header="Cashier"></Column>
                <Column field="customer.name" header="Customer">
                    <template #body="{ data }">
                        {{ data.customer?.name || 'Walk-in' }}
                    </template>
                </Column>
                <Column header="Total">
                    <template #body="{ data }">
                        ৳ {{ data.grand_total }}
                    </template>
                </Column>
                <Column header="Paid">
                    <template #body="{ data }">
                        ৳ {{ data.paid_amount }}
                    </template>
                </Column>
                <Column header="Due">
                    <template #body="{ data }">
                        <span :class="data.due_amount > 0 ? 'text-red-600 font-bold' : ''">
                            ৳ {{ data.due_amount }}
                        </span>
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="{ data }">
                        <Badge 
                            :value="data.status" 
                            :severity="data.status === 'completed' ? 'success' : data.status === 'voided' ? 'danger' : 'warning'"
                        />
                    </template>
                </Column>
                <Column header="Date">
                    <template #body="{ data }">
                        {{ data.created_at?.split('T')[0] }}
                    </template>
                </Column>

                <!-- ACTIONS -->
                <Column header="Actions">
                    <template #body="{ data }">
                        <Button 
                            icon="pi pi-eye" 
                            @click="viewSale(data)" 
                            class="p-button-rounded p-button-info p-button-sm"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<style scoped></style>