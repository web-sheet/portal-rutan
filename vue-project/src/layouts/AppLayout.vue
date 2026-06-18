<template>
  <div
    class="min-h-screen w-full flex flex-row bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/50 overflow-hidden relative">

    <div
      class="absolute top-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-200/20 rounded-full blur-[130px] pointer-events-none">
    </div>
    <div
      class="absolute bottom-[-20%] left-[10%] w-[500px] h-[500px] bg-emerald-200/20 rounded-full blur-[120px] pointer-events-none">
    </div>

    <Sidebar v-model:visible="sidebarVisible" @close="sidebarVisible = false" class="z-30" />

    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden z-10">

      <header
        class="md:hidden bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-4 py-3 flex items-center justify-between flex-shrink-0">
        <Button icon="pi pi-bars" text rounded @click="sidebarVisible = true"
          class="active:scale-95 transition-transform duration-100" />
        <h1 class="font-semibold text-slate-800 tracking-wide">RUTAN APP</h1>
        <div class="w-9"></div>
      </header>

      <main class="flex-1 p-4 md:p-6 overflow-auto relative"> <router-view v-slot="{ Component }">
          <transition name="page-fade">
            <component :is="Component" :key="$route.fullPath" />
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
/* Saat halaman baru masuk dan halaman lama keluar bersamaan */
.page-fade-enter-active,
.page-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
  will-change: opacity, transform;
}

/* Trik Utama: Saat halaman lama sedang memudar keluar, 
   buat posisinya absolute agar tidak menabrak/mendorong halaman baru */
.page-fade-leave-active {
  position: absolute;
  /* Sesuaikan padding dengan main container agar posisinya tidak bergeser */
  right: 1.5rem;
  left: 1.5rem;
}

.page-fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>