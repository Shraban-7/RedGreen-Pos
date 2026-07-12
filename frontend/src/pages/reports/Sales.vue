<script setup>
import { ref, onMounted } from 'vue';
import { useReportStore } from '@/stores/report';
import DateRangePicker from '@/components/DateRangePicker.vue';
import Chart from 'primevue/chart';

const store = useReportStore();
const from = ref('');
const to = ref('');

const load = () => store.fetchSales({ from: from.value, to: to.value });

const chartData = ref({ labels: [], datasets: [] });
const chartOptions = { responsive: true, maintainAspectRatio: false };

const buildChart = () => {
    const rows = store.sales?.daily_breakdown || [];
    chartData.value = {
        labels: rows.map((r) => r.date),
        datasets: [
            {
                label: 'Net Sales (৳)',
                data: rows.map((r) => parseFloat(r.net)),
                backgroundColor: '#2563eb',
            },
        ],
    };
};

onMounted(async () => {
    await load();
    buildChart();
});
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Sales Report</h1>
            <DateRangePicker v-model:from="from" v-model:to="to" @change="async () => { await load(); buildChart(); }" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Gross Total</p>
                <p class="text-2xl font-bold text-gray-900">৳ {{ Number(store.sales?.gross_total || 0).toFixed(2) }}</p>
            </div>
            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Total Sales</p>
                <p class="text-2xl font-bold text-gray-900">{{ store.sales?.sale_count || 0 }}</p>
            </div>
            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Date Range</p>
                <p class="text-sm font-medium text-gray-700 mt-2">{{ store.sales?.from }} → {{ store.sales?.to }}</p>
            </div>
        </div>

        <div class="bg-white border rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Daily Net Sales</h2>
            <div class="h-72">
                <Chart type="bar" :data="chartData" :options="chartOptions" />
            </div>
        </div>

        <div class="bg-white border rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Top Products</h2>
            <DataTable :value="store.sales?.top_products || []" stripedRows tableStyle="min-width: 40rem;">
                <Column field="name" header="Product"></Column>
                <Column field="quantity" header="Qty Sold"></Column>
                <Column header="Revenue">
                    <template #body="{ data }">৳ {{ Number(data.revenue).toFixed(2) }}</template>
                </Column>
            </DataTable>
        </div>

        <div class="bg-white border rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h2>
            <DataTable :value="store.sales?.payment_methods || []" stripedRows tableStyle="min-width: 30rem;">
                <Column field="method" header="Method"></Column>
                <Column field="count" header="Count"></Column>
                <Column header="Total">
                    <template #body="{ data }">৳ {{ Number(data.total).toFixed(2) }}</template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>