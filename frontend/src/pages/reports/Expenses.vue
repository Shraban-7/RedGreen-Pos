<script setup>
import { ref, onMounted } from 'vue';
import { useReportStore } from '@/stores/report';
import DateRangePicker from '@/components/DateRangePicker.vue';

const store = useReportStore();
const from = ref('');
const to = ref('');

const load = () => store.fetchExpenses({ from: from.value, to: to.value });

onMounted(load);
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Expenses Report</h1>
            <DateRangePicker v-model:from="from" v-model:to="to" @change="load" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Total Expenses</p>
                <p class="text-2xl font-bold text-red-600">৳ {{ Number(store.expenses?.total || 0).toFixed(2) }}</p>
            </div>
            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Date Range</p>
                <p class="text-sm font-medium text-gray-700 mt-2">{{ store.expenses?.from }} → {{ store.expenses?.to }}</p>
            </div>
        </div>

        <div class="bg-white border rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Breakdown by Category</h2>
            <DataTable :value="store.expenses?.by_category || []" stripedRows tableStyle="min-width: 40rem;">
                <Column header="Category">
                    <template #body="{ data }">{{ data.category?.name || 'Uncategorized' }}</template>
                </Column>
                <Column header="Total">
                    <template #body="{ data }">৳ {{ Number(data.total).toFixed(2) }}</template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>