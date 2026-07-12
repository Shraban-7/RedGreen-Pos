<script setup>
import { onMounted, computed } from 'vue';
import { useDashboardStore } from '@/stores/dashboard';
import { useRouter } from 'vue-router';
import Chart from 'primevue/chart';

const store = useDashboardStore();
const router = useRouter();

const d = computed(() => store.data || {});

const chartData = computed(() => {
    const chart = d.value.salesChart || { labels: [], data: [] };
    return {
        labels: chart.labels,
        datasets: [
            {
                label: 'Sales (৳)',
                data: chart.data,
                fill: true,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.15)',
                tension: 0.4,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: true },
    },
    scales: {
        y: { beginAtZero: true },
    },
};

onMounted(() => {
    store.fetchDashboard();
});
</script>

<template>
    <div class="space-y-6" v-if="store.data">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>

        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Today's Sales</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    ৳ {{ Number(d.todaySales?.total || 0).toFixed(2) }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ d.todaySales?.count || 0 }} orders</p>
            </div>

            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Today's Expenses</p>
                <p class="text-2xl font-bold text-red-600 mt-1">
                    ৳ {{ Number(d.todayExpenses || 0).toFixed(2) }}
                </p>
            </div>

            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Month's Sales</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    ৳ {{ Number(d.monthSales?.total || 0).toFixed(2) }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ d.monthSales?.count || 0 }} orders</p>
            </div>

            <div class="bg-white border rounded-xl shadow p-5">
                <p class="text-sm text-gray-500">Month's Expenses</p>
                <p class="text-2xl font-bold text-red-600 mt-1">
                    ৳ {{ Number(d.monthExpenses || 0).toFixed(2) }}
                </p>
            </div>
        </div>

        <!-- CHART -->
        <div class="bg-white border rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Sales Trend (This Month)</h2>
            <div class="h-72">
                <Chart type="line" :data="chartData" :options="chartOptions" />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- LOW STOCK -->
            <div class="bg-white border rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Low Stock Products</h2>
                    <button class="text-sm text-blue-600 hover:underline" @click="router.push('/products')">
                        View all
                    </button>
                </div>
                <ul class="divide-y">
                    <li v-for="p in (d.lowStockProducts || [])" :key="p.id"
                        class="py-2 flex items-center justify-between">
                        <span class="text-gray-700">{{ p.name }}</span>
                        <span class="text-red-600 font-medium">{{ p.stock }} left</span>
                    </li>
                    <li v-if="!(d.lowStockProducts || []).length" class="py-2 text-gray-400">
                        No low stock products 🎉
                    </li>
                </ul>
            </div>

            <!-- RECENT SALES -->
            <div class="bg-white border rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Sales</h2>
                <ul class="divide-y">
                    <li v-for="s in (d.recentSales || [])" :key="s.id"
                        class="py-2 flex items-center justify-between">
                        <div>
                            <p class="text-gray-700 font-medium">{{ s.sale_code }}</p>
                            <p class="text-xs text-gray-400">
                                {{ s.customer?.name || 'Walk-in' }} · {{ s.created_at }}
                            </p>
                        </div>
                        <span class="text-gray-900 font-semibold">
                            ৳ {{ Number(s.grand_total).toFixed(2) }}
                        </span>
                    </li>
                    <li v-if="!(d.recentSales || []).length" class="py-2 text-gray-400">
                        No recent sales
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div v-else class="flex justify-center items-center h-64 text-gray-400">
        Loading dashboard...
    </div>
</template>