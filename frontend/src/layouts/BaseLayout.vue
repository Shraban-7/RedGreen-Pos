<template>
  <div class="min-h-screen bg-linear-to-br from-gray-50 via-slate-50 to-gray-100">
    <div class="flex h-screen">
      <Sidebar />

      <div class="flex flex-col flex-1 overflow-hidden">
        <Navbar />

        <main class="flex-1 overflow-y-auto">
          <div class="container mx-auto px-6 py-6">
            <router-view />
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
