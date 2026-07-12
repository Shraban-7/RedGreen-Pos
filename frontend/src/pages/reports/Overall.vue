<script setup>
import { ref, onMounted } from 'vue';
import { useReportStore } from '@/stores/report';
import DateRangePicker from '@/components/DateRangePicker.vue';
import Chart from 'primevue/chart';

const store = useReportStore();
const from = ref('');
const to = ref('');

const load = () => store.fetchOverall({ from: from.value, to: to.value });

const chartData = ref({ labels: [], datasets: [] });
const chartOptions = { responsive: true, maintainAspectRatio: false };

const buildChart = () => {
    const trend = store.overall?.trend || { labels: [], sales: [], expenses: [] };
    chartData.value = {
        labels: trend.labels,
        datasets: [
            {
                label: 'Sales (৳)',
                data: trend.sales,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.15)',
                fill: true,
                tension: 0.4,
            },
            {
                label: 'Expenses (৳)',
                data: trend.expenses,
                borderColor: '#dc2626',
                backgroundColor: 'rgba(220, 38, 38, 0.15)',
                fill: true,
                tension: 0.4,
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
            <h1 class="text-2xl font-bold text-gray-900">Overall Report</h1>
            <DateRangePicker v-model:from="from" v-model:to="to" @change="async () => { await load(); buildChart(); }" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Total Sales</p>
                <p class="text-2xl font-bold text-gray-900">৳ {{ Number(store.overall?.total_sales || 0).toFixed(2) }}</p>
            </div>
            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Total Expenses</p>
                <p class="text-2xl font-bold text-red-600">৳ {{ Number(store.overall?.total_expenses || 0).toFixed(2) }}</p>
            </div>
            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Net Profit</p>
                <p class="text-2xl font-bold text-green-600">৳ {{ Number(store.overall?.net_profit || 0).toFixed(2) }}</p>
            </div>
        </div>

        <div class="bg-white border rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Sales vs Expenses Trend</h2>
            <div class="h-80">
                <Chart type="line" :data="chartData" :options="chartOptions" />
            </div>
        </div>
    </div>
</template>