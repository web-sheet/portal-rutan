<template>
  <div class="min-h-screen w-full flex flex-row bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/50 overflow-hidden relative">
    
    <div class="absolute top-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-200/20 rounded-full blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-[-20%] left-[10%] w-[500px] h-[500px] bg-emerald-200/20 rounded-full blur-[120px] pointer-events-none"></div>

    <Sidebar 
      v-model:visible="sidebarVisible" 
      @close="sidebarVisible = false" 
      class="z-20"
    />

    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden z-10">
      
      <header class="md:hidden bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-4 py-3 flex items-center justify-between flex-shrink-0">
        <Button icon="pi pi-bars" text rounded @click="sidebarVisible = true" />
        <h1 class="font-semibold text-slate-800 tracking-wide">RUTAN APP</h1>
        <div class="w-9"></div>
      </header>

      <main class="flex-1 p-4 md:p-6 overflow-auto">
        <router-view v-slot="{ Component }">
          <transition name="page-fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import Button from 'primevue/button'
import Sidebar from '@/components/Sidebar.vue'

const sidebarVisible = ref(false)
</script>

<style scoped>
/* ─── ANIMASI TRANSISI HALAMAN (ANTI-FLICKER) ─── */

/* Durasi & timing function (memanfaatkan akselerasi hardware GPU) */
.page-fade-enter-active,
.page-fade-leave-active {
  transition: opacity 0.20s ease, transform 0.20s ease;
  will-change: opacity, transform;
}

/* Keadaan awal sebelum masuk & Keadaan akhir setelah keluar */
.page-fade-enter-from {
  opacity: 0;
  transform: translateY(8px); /* Halaman baru sedikit bergeser dari bawah ke atas */
}

.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px); /* Halaman lama sedikit meluncur ke atas saat memudar */
}
</style>