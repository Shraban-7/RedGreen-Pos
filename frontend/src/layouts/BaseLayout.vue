<script setup>
import Sidebar from "@/components/Sidebar.vue";
import Navbar from "@/components/Navbar.vue";
import { useToast } from "primevue/usetoast";

const toast = useToast();
</script>

<template>
  <div class="h-screen flex overflow-hidden">  <!-- FIXED: h-screen -->

    <!-- Sidebar -->
    <Sidebar />

    <!-- Right Content Layout -->
    <div class="flex flex-col flex-1 overflow-hidden bg-gray-100">

      <!-- Navbar (Fixed & non-scrollable) -->
      <Navbar />

      <!-- Main Scrollable Content -->
      <main class="flex-1 overflow-y-auto p-6">
        <Toast position="top-right" />
        <router-view />
      </main>

    </div>
  </div>
</template>

<style scoped>
main::-webkit-scrollbar {
  width: 8px;
}
main::-webkit-scrollbar-thumb {
  background: #c6cbd4;
  border-radius: 10px;
}
</style>
