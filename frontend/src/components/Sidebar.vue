<template>
  <!-- Mobile menu overlay -->
  <div 
    v-if="mobileOpen"
    class="fixed inset-0 bg-black/50 z-30 md:hidden"
    @click="mobileOpen = false"
  ></div>

  <aside 
    :class="[
      'flex flex-col shadow-xl transition-all duration-300 fixed md:static left-0 top-0 z-40',
      'bg-[#131722] text-gray-100 h-screen',
      collapsed ? 'w-20' : 'w-64',
      mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
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

      <!-- Collapse button -->
      <button 
        class="hidden md:flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-800 transition"
        @click="toggleCollapse"
      >
        <i :class="collapsed ? 'pi pi-angle-right' : 'pi pi-angle-left'"></i>
      </button>
    </div>

    <!-- Sidebar Menu -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-2">

      <div v-for="item in links" :key="item.name">
        
        <!-- Simple Link -->
        <router-link 
          v-if="!item.children"
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
            <span v-if="!collapsed" class="ml-3 text-sm font-medium">{{ item.name }}</span>
          </div>
        </router-link>

        <!-- Parent with children (tree menu) -->
        <div v-else>
          <div 
            class="flex items-center justify-between px-4 py-3 rounded-lg cursor-pointer text-gray-300 hover:bg-gray-800"
            @click="toggleTree(item)"
          >
            <div class="flex items-center">
              <i :class="['text-lg', item.icon]"></i>
              <span v-if="!collapsed" class="ml-3 text-sm font-medium">{{ item.name }}</span>
            </div>

            <i 
              v-if="!collapsed"
              :class="['pi', item.show ? 'pi-chevron-up' : 'pi-chevron-down']"
            ></i>
          </div>

          <!-- TREE CHILDREN -->
          <transition name="fade">
            <div 
              v-if="item.show && !collapsed" 
              class="ml-8 mt-1 space-y-1"
            >
              <router-link 
                v-for="child in item.children"
                :key="child.name"
                :to="child.path"
                class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-700"
                :class="route.path === child.path ? 'bg-indigo-600 text-white' : ''"
              >
                - {{ child.name }}
              </router-link>
            </div>
          </transition>
        </div>

      </div>

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

  <!-- Mobile menu button -->
  <button 
    class="md:hidden fixed top-4 left-4 bg-indigo-600 text-white p-2 rounded-lg z-50"
    @click="mobileOpen = true"
  >
    <i class="pi pi-bars"></i>
  </button>
</template>

<script setup>
import { ref } from "vue";
import { useRoute } from "vue-router";
import { menuLinks } from "@/config/sidebarLinks";

const collapsed = ref(false);
const mobileOpen = ref(false);

const route = useRoute();

// Make menu reactive + tree state
const links = ref(
  menuLinks.map(item => ({
    ...item,
    show: false,
    children: item.children
      ? item.children.map(c => ({ ...c }))
      : null
  }))
);

// Toggle parent open/close
const toggleTree = (item) => {
  item.show = !item.show;
};

// Collapse sidebar
const toggleCollapse = () => (collapsed.value = !collapsed.value);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
