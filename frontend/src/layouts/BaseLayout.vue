<script setup>
import Sidebar from "@/components/Sidebar.vue";
import Navbar from "@/components/Navbar.vue";
import { useToast } from "primevue/usetoast";

const toast = useToast();
</script>

<template>
  <div class="min-h-screen flex overflow-hidden">

    <div class="flex flex-1 bg-linear-to-br from-gray-50 via-gray-100 to-gray-200">

      <!-- Sidebar -->
      <Sidebar />

      <!-- Main content -->
      <div class="flex flex-col flex-1 overflow-hidden">

        <!-- Navbar -->
        <Navbar />

        <!-- Page Body -->
        <main class="flex-1 overflow-y-auto p-6 transition-colors duration-300">
          <Toast position="top-right" />
          <router-view />
        </main>

      </div>

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
