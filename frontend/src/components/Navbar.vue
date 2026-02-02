<template>
  <header
    class="h-16 bg-white/50 backdrop-blur-xl border-b border-gray-200/50 shadow-sm sticky top-0 z-10"
  >
    <div class="h-full px-6 flex items-center justify-between">

      <!-- Search Bar -->
      <div class="flex-1 max-w-xl">
        <IconField>
          <InputIcon class="pi pi-search" />
          <InputText v-model="value1" placeholder="Search" />
        </IconField>
      </div>

      <!-- Right Actions -->
      <div class="flex items-center gap-4 ml-6">

        <!-- Notifications -->
        <button
          class="relative p-2 hover:bg-gray-100 rounded-lg transition group"
        >
          <i class="pi pi-bell text-gray-600 group-hover:text-indigo-600"></i>
          <span
            class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"
          ></span>
        </button>

        <!-- Divider -->
        <div class="w-px h-6 bg-gray-200/50"></div>

        <!-- Avatar + Dropdown -->
        <div class="relative">
          <div
            class="flex items-center gap-3 cursor-pointer select-none"
            @click="toggleMenu"
          >
            <img
              src="https://ui-avatars.com/api/?background=6366f1&color=fff&name=Admin"
              class="w-10 h-10 rounded-full shadow border border-gray-300"
              alt="User avatar"
            />
            <i class="pi pi-chevron-down text-gray-600"></i>
          </div>

          <!-- Dropdown -->
          <transition name="fade">
            <div
              v-if="menuOpen"
              class="absolute right-0 mt-3 w-44 bg-white rounded-xl shadow-xl p-2 z-30"
            >
              <button
                class="w-full text-left flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-gray-100"
              >
                <i class="pi pi-user"></i>
                Profile
              </button>

              <button
                @click="logout"
                class="w-full text-left flex items-center gap-2 px-3 py-2 text-sm text-red-600 rounded-lg hover:bg-red-50"
              >
                <i class="pi pi-sign-out"></i>
                Logout
              </button>
            </div>
          </transition>
        </div>

      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

const auth = useAuthStore();
const router = useRouter();

const menuOpen = ref(false);

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value;
};

const logout = () => {
  auth.logout();
  router.push("/login");
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
