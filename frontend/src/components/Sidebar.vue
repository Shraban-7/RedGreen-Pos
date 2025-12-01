<template>
    <aside :class="[
        'bg-gray-900 border-r border-gray-800',
        'transition-all duration-300 ease-in-out',
        'relative z-20 shadow-xl shadow-black/40',
        collapsed ? 'w-20' : 'w-64'
    ]">
        <!-- Logo -->
        <div class="h-16 flex items-center justify-center border-b border-gray-800">
            <transition name="fade">
                <div v-if="!collapsed" class="flex items-center gap-3 px-6">
                    <div
                        class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-900/40">
                        <i class="pi pi-shop text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white">POS</h1>
                        <p class="text-xs text-gray-400">Management</p>
                    </div>
                </div>

                <div v-else
                    class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-900/40">
                    <i class="fa-solid fa-store text-white text-lg"></i>
                </div>
            </transition>
        </div>

        <!-- Menu -->
        <nav class="mt-4 px-3 space-y-1">
            <router-link v-for="link in links" :key="link.name" :to="link.path" class="block">
                <div :class="[
                    'flex items-center gap-3 px-4 py-3 rounded-lg group transition-all',
                    isActive(link.path)
                        ? 'bg-gray-800 shadow-lg'
                        : 'hover:bg-gray-800/70 hover:shadow'
                ]">
                    <i :class="[
                        link.icon,
                        'text-lg',
                        isActive(link.path)
                            ? 'text-indigo-400'
                            : 'text-gray-400 group-hover:text-indigo-400'
                    ]"></i>

                    <!-- Name (hidden when collapsed) -->
                    <transition name="fade">
                        <span v-if="!collapsed" :class="[
                            'text-sm font-medium transition-colors',
                            isActive(link.path)
                                ? 'text-white'
                                : 'text-gray-300 group-hover:text-white'
                        ]">
                            {{ link.name }}
                        </span>
                    </transition>

                    <!-- Collapsed tooltip -->
                    <div v-if="collapsed" class="absolute left-full ml-2 px-3 py-1 bg-black text-white text-xs rounded-md 
                              opacity-0 group-hover:opacity-100 transition-opacity shadow-xl whitespace-nowrap">
                        {{ link.name }}
                    </div>
                </div>
            </router-link>
        </nav>

        <!-- Collapse Button -->
        <button @click="toggleCollapse" class="absolute -right-3 top-20 w-7 h-7 bg-gray-900 border border-gray-700 rounded-full shadow-xl
                   flex items-center justify-center hover:scale-110 transition-all">
            <i
                :class="['text-xs text-gray-300', collapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left']"></i>
        </button>

        <!-- User Profile -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800 bg-gray-800/40">
            <div :class="['flex items-center gap-3', collapsed ? 'justify-center' : '']">
                <div
                    class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                    JD
                </div>
                <transition name="fade">
                    <div v-if="!collapsed">
                        <p class="text-sm text-white font-semibold">John Doe</p>
                        <p class="text-xs text-gray-400">Admin</p>
                    </div>
                </transition>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { ref } from "vue"
import { useRoute } from "vue-router"
import { menuLinks } from "@/config/sidebarLinks"

const collapsed = ref(false)
const links = menuLinks
const route = useRoute()

const toggleCollapse = () => (collapsed.value = !collapsed.value)

const isActive = (path) => route.path.startsWith(path)
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
