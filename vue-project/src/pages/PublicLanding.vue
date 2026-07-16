<template>
  <div
    class="min-h-screen flex flex-col bg-gradient-to-br from-slate-50 via-blue-50/50 to-indigo-50 relative overflow-hidden">

    <div
      class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-200/40 rounded-full blur-[100px] pointer-events-none">
    </div>
    <div
      class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-emerald-200/30 rounded-full blur-[100px] pointer-events-none">
    </div>

    <header
      class="flex items-center justify-between px-6 py-4 bg-white/80 backdrop-blur-md border-b border-slate-200/80 shadow-sm z-10">
      <div class="flex items-center gap-3">
        <img :src="logoPemasyarakatan" alt="Logo Pemasyarakatan" class="w-12 h-12 object-contain" />

        <div>
          <h1 class="text-xl md:text-2xl font-bold text-slate-800 tracking-tight">
            PRISMA
          </h1>
          <p class="text-xs md:text-sm text-slate-500 font-medium">
            Platform Pengelolaan Rutan Kelas I Pondok Bambu

          </p>
        </div>
      </div>

      <Button severity="secondary" outlined
        class="text-slate-700 border-slate-300 hover:bg-slate-50 px-4 py-2 flex items-center gap-2 text-sm rounded-xl transition-all"
        @click="goToLogin">
        <span>Login</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
          class="w-4 h-4 text-slate-600">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
        </svg>
      </Button>
    </header>

    <!-- <main class="flex-1 flex items-center justify-center px-4 py-12 z-10">
      <div class="w-full max-w-3xl bg-white/90 backdrop-blur-md border border-slate-200 shadow-xl rounded-3xl p-8 md:p-14 text-center">
        
      

        <h2 class="text-4xl md:text-5xl font-extrabold text-slate-800 mb-4 tracking-tight leading-tight">
          Selamat Datang di PRISMA
        </h2>

        <p class="text-base md:text-lg text-slate-500 mb-10 leading-relaxed max-w-xl mx-auto">
         Platform Pengelolaan Rutan Kelas I Pondok Bambu <br class="hidden md:inline" /> 
          <span class="text-blue-500 font-semibold">untuk Informasi, Sistem, dan Administrasi</span>
        </p>

        <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
          
          <Button class="w-full sm:w-auto px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md shadow-blue-600/10 transition-all flex items-center justify-center gap-3 border-none" @click="goToPengajuanBarang">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l3 1.75M9 20.25v-7.5" />
            </svg>
            <span>Pengajuan Barang</span>
          </Button>
 

        </div>
      </div>
    </main> -->

    <main class="flex-1 flex items-center justify-center px-4 py-12 z-10 select-none">
      <div
        class="w-full max-w-4xl bg-white/80 backdrop-blur-lg border border-slate-200/60 shadow-2xl rounded-3xl p-8 md:p-14 text-center transition-all duration-300">

        <!-- 1. IDENTITAS & LOGO GAMBAR -->
        <div class="flex flex-col items-center justify-center gap-3 mb-6 animate-fade-in">
          <!-- Tag <img> untuk logo (Tinggal ganti src sesuai path gambar Anda) -->
          <div class="w-25 h-25 rounded-2xl   flex items-center justify-center p-2 shadow-sm border border-emerald-100">
            <img :src="logoPemasyarakatan" alt="Logo PRISMA" class="w-full h-full object-contain" />
          </div>

        </div>

        <!-- 2. JUDUL UTAMA (NUANSA HIJAU) -->
        <h2 class="text-4xl md:text-5xl font-black text-slate-800 mb-4 tracking-tight leading-none">
          Selamat Datang di <span
            class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">PRISMA</span>
        </h2>

        <!-- 3. SUBTITLE / PENJELASAN -->
        <p class="text-base md:text-lg text-slate-500 mb-10 leading-relaxed max-w-2xl mx-auto font-medium">
          Platform Pengelolaan Rutan Kelas I Pondok Bambu <br class="hidden md:inline" />
          <span class="text-slate-600 font-semibold border-b-2 border-emerald-100 pb-0.5">untuk Informasi, Sistem, dan
            Administrasi</span>
        </p>

        <!-- 4. AREA BUTTON DI TENGAH (LEBIH INTERAKTIF) -->
        <div class="flex justify-center items-center w-full select-none">
          <button
            class="group w-full max-w-md p-5 bg-slate-50/50 hover:bg-emerald-600 border border-slate-200 hover:border-emerald-600 shadow-md hover:shadow-xl hover:shadow-emerald-600/20 rounded-2xl transition-all duration-300 hover:-translate-y-1 flex items-center justify-between gap-4 text-left cursor-pointer"
            @click="goToPengajuanBarang">
            <!-- Sisi Kiri: Logo & Teks Info -->
            <div class="flex items-center gap-4">
              <div
                class="w-12 h-12 flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-110">
                <img :src="sipanda" alt="Logo SIPANDA" class="w-full h-full object-contain filter drop-shadow-sm" />
              </div>
              <div>
                <h3
                  class="font-bold text-slate-800 group-hover:text-white text-base transition-colors flex items-center gap-1.5">
                  SIPANDA
                </h3>
                <p class="text-xs text-slate-400 group-hover:text-emerald-100 transition-colors mt-0.5">
                  Sistem Informasi Permintaan dan Pendistribusian Barang
                </p>
              </div>
            </div>

            <!-- Sisi Kanan: Indikator Panah (Kunci utama penunjuk "Bisa Diklik") -->
            <div
              class="text-slate-300 group-hover:text-white transition-all duration-300 transform group-hover:translate-x-1 shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </button>
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
import sipanda from '@/assets/sipanda.png'

const router = useRouter()

const goToLogin = () => { router.push('/login') }
const goToPengajuanBarang = () => { router.push('/requests') }
const goToLaporanKejadian = () => { router.push('/laporan-kejadian') }
</script>