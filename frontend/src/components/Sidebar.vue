<template>
    <aside :class="[
        'bg-white/40 backdrop-blur-xl border-r border-white/20',
        'transition-all duration-300 ease-in-out',
        'relative z-20 shadow-xl shadow-gray-900/5',
        collapsed ? 'w-20' : 'w-64'
    ]">
        <!-- Logo Section -->
        <div class="h-16 flex items-center justify-center border-b border-gray-200/30">
            <transition name="fade" mode="out-in">
                <div v-if="!collapsed" class="flex items-center gap-3 px-6">
                    <div
                        class="w-9 h-9 bg-linear-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <i class="pi pi-shopping-cart text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-800">POS</h1>
                        <p class="text-xs text-gray-500">System</p>
                    </div>
                </div>
                <div v-else
                    class="w-9 h-9 bg-linear-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <i class="pi pi-shopping-cart text-white text-lg"></i>
                </div>
            </transition>
        </div>

        <!-- Navigation Menu -->
        <nav class="mt-4 px-3 space-y-1">
            <div v-for="item in menuItems" :key="item.label" :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg',
                'transition-all duration-200 cursor-pointer group relative',
                'hover:bg-white/60 hover:shadow-md',
                item.active ? 'bg-white/70 shadow-md' : ''
            ]">
                <i :class="[
                    item.icon,
                    'text-lg transition-colors',
                    item.active ? 'text-indigo-600' : 'text-gray-600 group-hover:text-indigo-600'
                ]"></i>

                <transition name="fade" mode="out-in">
                    <div v-if="!collapsed" class="flex-1 flex items-center justify-between">
                        <span :class="[
                            'text-sm font-medium transition-colors',
                            item.active ? 'text-gray-900' : 'text-gray-700 group-hover:text-gray-900'
                        ]">
                            {{ item.label }}
                        </span>
                        <span v-if="item.badge"
                            class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full shadow-sm">
                            {{ item.badge }}
                        </span>
                    </div>
                </transition>

                <!-- Tooltip for collapsed state -->
                <div v-if="collapsed"
                    class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-xl">
                    {{ item.label }}
                </div>
            </div>
        </nav>

        <!-- Collapse Button -->
        <button @click="toggleCollapse"
            class="absolute -right-3 top-20 w-6 h-6 bg-white border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:scale-110 hover:shadow-xl transition-all duration-200">
            <i :class="['pi text-xs text-gray-600', collapsed ? 'pi-chevron-right' : 'pi-chevron-left']"></i>
        </button>

        <!-- User Profile (Bottom) -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200/30 bg-white/30 backdrop-blur-sm">
            <div :class="['flex items-center gap-3', collapsed ? 'justify-center' : '']">
                <div
                    class="w-9 h-9 bg-linear-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center font-semibold text-white text-sm shadow-lg">
                    JD
                </div>
                <transition name="fade" mode="out-in">
                    <div v-if="!collapsed">
                        <p class="font-semibold text-sm text-gray-800">John Doe</p>
                        <p class="text-xs text-gray-500">Admin</p>
                    </div>
                </transition>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { ref } from 'vue'

const collapsed = ref(false)

const toggleCollapse = () => {
    collapsed.value = !collapsed.value
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
