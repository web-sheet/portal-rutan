<template>
  <div class="min-h-screen w-full flex flex-row bg-slate-100 overflow-hidden">
    
    <Sidebar 
      v-model:visible="sidebarVisible" 
      @close="sidebarVisible = false" 
    />

    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
      <header class="md:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between flex-shrink-0">
        <Button icon="pi pi-bars" text rounded @click="sidebarVisible = true" />
        <h1 class="font-semibold text-slate-800">RUTAN APP</h1>
        <div class="w-9"></div>
      </header>

      <main class="flex-1 p-4 md:p-6 overflow-auto">
        <router-view />
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import Button from 'primevue/button'
import Sidebar from '@/components/Sidebar.vue' // Sesuaikan path-nya

const sidebarVisible = ref(false)
</script>