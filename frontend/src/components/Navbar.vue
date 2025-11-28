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

      <!-- Header Actions -->
      <div class="flex items-center gap-2 ml-6">
        <!-- Notifications -->
        <button
          class="relative p-2 hover:bg-white/60 rounded-lg transition-all duration-200 group"
        >
          <i
            class="pi pi-bell text-gray-600 group-hover:text-indigo-600 transition-colors"
          ></i>
          <span
            class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"
          ></span>
        </button>

        <!-- Settings -->
        <button
          class="p-2 hover:bg-white/60 rounded-lg transition-all duration-200 group"
        >
          <i
            class="pi pi-cog text-gray-600 group-hover:text-indigo-600 transition-colors"
          ></i>
        </button>

        <!-- Divider -->
        <div class="w-px h-6 bg-gray-200/50 mx-1"></div>

        <!-- Quick Actions -->
        <button
          class="px-4 py-2 bg-linear-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-lg hover:shadow-indigo-500/30 hover:scale-105 transition-all duration-200 font-medium text-sm flex items-center gap-2"
        >
          <i class="pi pi-plus text-xs"></i>
          <span>New Sale</span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup></script>

<style scoped></style>
