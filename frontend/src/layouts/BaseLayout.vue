<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-slate-50 to-gray-100">
        <div class="flex h-screen">
            <!-- Sidebar Component -->
            <Sidebar />

            <!-- Main Content Area -->
            <div class="flex flex-col flex-1 overflow-hidden">
                <!-- Navbar Component -->
                <Navbar />

                <!-- Main Content -->
                <main class="flex-1 overflow-y-auto">
                    <div class="container mx-auto px-6 py-6">
                        <slot>
                            <router-view></router-view>
                        </slot>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import Sidebar from '@/components/Sidebar.vue'
import Navbar from '@/components/Navbar.vue'
</script>

<style scoped>
/* Custom scrollbar */
main::-webkit-scrollbar {
    width: 6px;
}

main::-webkit-scrollbar-track {
    background: transparent;
}

main::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #cbd5e1, #94a3b8);
    border-radius: 3px;
}

main::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #94a3b8, #64748b);
}
</style>