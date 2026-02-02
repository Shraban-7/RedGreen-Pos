<template>
  <aside
    :class="[
      'h-screen flex flex-col shadow-xl transition-all duration-300 fixed md:static left-0 top-0 z-40',
      'bg-[#131722] text-gray-100',
      collapsed ? 'w-20' : 'w-64'
    ]"
  >

    <!-- Logo -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800">
      <div class="flex items-center gap-3 overflow-hidden">
        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
          <i class="pi pi-shopping-bag text-white"></i>
        </div>

        <transition name="fade">
          <div v-if="!collapsed">
            <p class="font-bold text-white">POS System</p>
            <p class="text-xs text-gray-400 -mt-1">Management Panel</p>
          </div>
        </transition>
      </div>

      <button
        @click="toggleCollapse"
        class="hidden md:flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-800 transition"
      >
        <i :class="collapsed ? 'pi pi-angle-right' : 'pi pi-angle-left'"></i>
      </button>
    </div>

    <!-- Menu -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-2">
      <router-link
        v-for="item in links"
        :key="item.name"
        :to="item.path"
        class="group block relative"
      >
        <div
          :class="[
            'flex items-center px-4 py-3 rounded-lg transition-all duration-200',
            route.path.startsWith(item.path)
              ? 'bg-indigo-600 text-white shadow-lg'
              : 'hover:bg-gray-800/80 text-gray-300'
          ]"
        >
          <i :class="['text-lg', item.icon]"></i>

          <transition name="fade">
            <span v-if="!collapsed" class="ml-3 text-sm font-medium">
              {{ item.name }}
            </span>
          </transition>

          <!-- Tooltip (collapsed mode) -->
          <div
            v-if="collapsed"
            class="absolute left-full top-1/2 -translate-y-1/2 ml-2 py-1 px-3 rounded bg-black text-xs text-white
                   whitespace-nowrap opacity-0 group-hover:opacity-100 transition shadow-lg"
          >
            {{ item.name }}
          </div>
        </div>
      </router-link>
    </nav>

    <!-- Footer -->
    <div class="p-4 mt-auto border-t border-gray-800">
      <div class="flex items-center gap-3" :class="collapsed ? 'justify-center' : ''">
        <img
          src="https://ui-avatars.com/api/?background=6d28d9&color=fff&name=Admin"
          class="w-10 h-10 rounded-full shadow"
        />
        <transition name="fade">
          <div v-if="!collapsed">
            <p class="font-semibold text-white text-sm">Admin</p>
            <p class="text-xs text-gray-400">Manager</p>
          </div>
        </transition>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref } from "vue";
import { useRoute } from "vue-router";
import { menuLinks } from "@/config/sidebarLinks";

const collapsed = ref(false);
const route = useRoute();
const links = menuLinks;

const toggleCollapse = () => (collapsed.value = !collapsed.value);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity .2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
