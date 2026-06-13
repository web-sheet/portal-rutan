<template>
  <div class="min-h-screen flex flex-col bg-gradient-to-br from-slate-50 via-blue-50/50 to-indigo-50 relative overflow-hidden">
    
    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-200/40 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-emerald-200/30 rounded-full blur-[100px] pointer-events-none"></div>

    <header class="flex items-center justify-between px-6 py-4 bg-white/80 backdrop-blur-md border-b border-slate-200/80 shadow-sm z-10">
      <div class="flex items-center gap-3">
        <img :src="logoPemasyarakatan" alt="Logo Pemasyarakatan" class="w-12 h-12 object-contain" />

        <div>
          <h1 class="text-xl md:text-2xl font-bold text-slate-800 tracking-tight">
            Portal Pengelolaan
          </h1>
          <p class="text-xs md:text-sm text-slate-500 font-medium">
            Rutan Kelas I Pondok Bambu
          </p>
        </div>
      </div>

      <Button severity="secondary" outlined class="text-slate-700 border-slate-300 hover:bg-slate-50 px-4 py-2 flex items-center gap-2 text-sm rounded-xl transition-all" @click="goToLogin">
        <span>Login</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-600">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
        </svg>
      </Button>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-12 z-10">
      <div class="w-full max-w-3xl bg-white/90 backdrop-blur-md border border-slate-200 shadow-xl rounded-3xl p-8 md:p-14 text-center">
        
      

        <h2 class="text-4xl md:text-5xl font-extrabold text-slate-800 mb-4 tracking-tight leading-tight">
          Selamat Datang
        </h2>

        <p class="text-base md:text-lg text-slate-500 mb-10 leading-relaxed max-w-xl mx-auto">
          Sistem Informasi Layanan Pengelolaan Internal <br class="hidden md:inline" /> 
          <span class="text-blue-500 font-semibold">Rumah Tahanan Negara Kelas I Pondok Bambu</span>
        </p>

        <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
          
          <Button class="w-full sm:w-auto px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md shadow-blue-600/10 transition-all flex items-center justify-center gap-3 border-none" @click="goToPengajuanBarang">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l3 1.75M9 20.25v-7.5" />
            </svg>
            <span>Pengajuan Barang</span>
          </Button>

          <!-- <Button class="w-full sm:w-auto px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl border border-slate-200 transition-all flex items-center justify-center gap-3" @click="goToLaporanKejadian">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-slate-500">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            <span>Laporan Kejadian</span>
          </Button> -->

        </div>
      </div>
    </main>

    <footer class="text-center py-4 bg-white/60 border-t border-slate-200 text-slate-400 text-xs tracking-wider z-10">
      © 2026 Rutan Kelas I Pondok Bambu. Hak Cipta Dilindungi.
    </footer>
  </div>
</template>

<script setup>
import Button from 'primevue/button'
import { useRouter } from 'vue-router'
import logoPemasyarakatan from '@/assets/logo-pemasyarakatan.png'

const router = useRouter()

const goToLogin = () => { router.push('/login') }
const goToPengajuanBarang = () => { router.push('/requests') }
const goToLaporanKejadian = () => { router.push('/laporan-kejadian') }
</script>