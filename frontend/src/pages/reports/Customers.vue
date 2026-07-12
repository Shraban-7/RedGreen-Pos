<script setup>
import { ref, onMounted } from 'vue';
import { useReportStore } from '@/stores/report';
import DateRangePicker from '@/components/DateRangePicker.vue';

const store = useReportStore();
const from = ref('');
const to = ref('');

const load = () => store.fetchCustomers({ from: from.value, to: to.value });

onMounted(load);
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Customers Report</h1>
            <DateRangePicker v-model:from="from" v-model:to="to" @change="load" />
        </div>

        <div class="bg-white border rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Customer Spending ({{ store.customers?.from }} → {{ store.customers?.to }})</h2>
            <DataTable :value="store.customers?.customers || []" stripedRows paginator :rows="10" tableStyle="min-width: 50rem;">
                <Column field="name" header="Customer"></Column>
                <Column field="sales_count" header="Orders"></Column>
                <Column header="Total Spent">
                    <template #body="{ data }">৳ {{ Number(data.total_spent).toFixed(2) }}</template>
                </Column>
                <Column header="Avg Order">
                    <template #body="{ data }">৳ {{ Number(data.average_order_value).toFixed(2) }}</template>
                </Column>
                <Column field="last_purchase" header="Last Purchase"></Column>
            </DataTable>
        </div>
    </div>
</template>