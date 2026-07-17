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
        class="text-slate-700 border-slate-300 hover:bg-slate-50 px-4 py-2 flex items-center gap-2 text-sm rounded-xl transition-all cursor-pointer"
        @click="goToLogin">
        <span>Login</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
          class="w-4 h-4 text-slate-600">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
        </svg>
      </Button>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-5 z-10 select-none">
      <div
        class="w-full max-w-8xl bg-white/80 backdrop-blur-lg border border-slate-200/60 shadow-2xl rounded-3xl p-6 md:p-12 transition-all duration-300">

        <!-- LAYOUT 2 KOLOM -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-center">

          <!-- KOLOM KIRI: LOGO PRISMA SANGAT BESAR -->
          <div
            class="lg:col-span-5 flex flex-col items-center justify-center animate-fade-in border-b lg:border-b-0 lg:border-r border-slate-200/60 pb-10 lg:pb-0 lg:pr-10">
            <!-- Frame Logo diperbesar menjadi w-80 h-80 di mobile dan w-[400px] h-[400px] di desktop -->
            <div
              class="w-80 h-80 md:w-[500px] md:h-[500px] rounded-3xl flex items-center justify-center p-6 bg-white/60 backdrop-blur-sm shadow-md border border-emerald-100/70 transition-transform duration-300 hover:scale-105">
              <img :src="logoPemasyarakatan" alt="Logo PRISMA"
                class="w-full h-full object-contain filter drop-shadow-2xl p-10" />
            </div>
          </div>

      
          <div class="lg:col-span-7 flex flex-col text-center lg:text-left">

         
            <div class="mb-8">
              <h2 class="text-4xl md:text-4xl font-black text-slate-800 mb-1 tracking-tight leading-tight">
                Selamat Datang di <span
                  class="bg-gradient-to-r from-slate-900 via-blue-950 to-indigo-900 bg-clip-text text-transparent">PRISMA</span>
              </h2>
              <p class="text-base md:text-md text-slate-500 font-medium ">
                Silakan pilih layanan sistem di bawah ini untuk melanjutkan
              </p>
            </div>

         
            <div class="flex flex-col gap-4 w-full">

              <!-- Tombol 1: SIPANDA -->
              <button
                class="group w-full p-4 md:p-5 bg-slate-50/60 hover:bg-emerald-600 border border-slate-200 hover:border-emerald-600 shadow-sm hover:shadow-xl hover:shadow-emerald-600/15 rounded-2xl transition-all duration-300 hover:-translate-y-1 flex items-center justify-between gap-4 text-left cursor-pointer"
                @click="goToPengajuanBarang">
                <!-- Sisi Kiri: Logo SIPANDA Jumbo & Teks Info -->
                <div class="flex items-center gap-5">
                  <!-- Container logo diperbesar maksimal ke w-24 h-24 (96px) dengan padding minimal agar logo terlihat sangat dominan -->
                  <div
                    class="w-24 h-24 md:w-40 md:h-40 flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-105 p-1">
                    <img :src="sipanda" alt="Logo SIPANDA" class="w-full h-full object-contain filter drop-shadow-md" />
                  </div>
                  <div>
                    <h3
                      class="font-black text-slate-800 group-hover:text-white text-xl md:text-2xl transition-colors flex items-center gap-1.5 leading-none">
                      SIPANDA
                    </h3>
                    <p
                      class="text-xs md:text-sm text-slate-400 group-hover:text-emerald-100 transition-colors mt-1.5 leading-normal max-w-md">
                      Sistem Informasi Permintaan dan Pendistribusian Barang
                    </p>
                  </div>
                </div>

                <!-- Sisi Kanan: Indikator Panah -->
                <div
                  class="text-slate-300 group-hover:text-white transition-all duration-300 transform group-hover:translate-x-1.5 shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                  </svg>
                </div>
              </button>

              <!-- Tombol 2: KEPEGAWAIAN -->
              <button
                class="group w-full p-4 md:p-5 bg-slate-50/60 hover:bg-emerald-600 border border-slate-200 hover:border-emerald-600 shadow-sm hover:shadow-xl hover:shadow-emerald-600/15 rounded-2xl transition-all duration-300 hover:-translate-y-1 flex items-center justify-between gap-4 text-left cursor-pointer"
                @click="goToLogin">

                <div class="flex items-center gap-5">

                  <div
                    class="w-24 h-24 md:w-40 md:h-40 flex items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-500/20 group-hover:text-white shrink-0 transition-all duration-300 group-hover:scale-105">
                    <!-- Ukuran ikon SVG di dalam disesuaikan menjadi w-12 h-12 -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="currentColor" class="w-12 h-12">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 7.588 15m0 3.72a9.094 9.094 0 0 1-3.741-.479 3 3 0 0 1 4.682-2.72m-.94 3.198-.002.031c0 .225.012.447.037.666A11.944 11.944 0 0 0 12 21c2.17 0 4.207-.576 5.963-1.584A6.062 6.062 0 0 0 18 18.722m-12 0a5.971 5.971 0 0 1 .94-3.197m0 0a5.995 5.995 0 0 1 9.471 0M12 10.5a3 3 0 1 1 0-6 3 3 0 0 1 0 6ZM19.5 8.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM6.75 8.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                  </div>
                  <div>
                    <h3
                      class="font-black text-slate-800 group-hover:text-white text-xl md:text-2xl transition-colors flex items-center gap-1.5 leading-none">
                      Kepegawaian
                    </h3>
                    <p
                      class="text-xs md:text-sm text-slate-400 group-hover:text-emerald-100 transition-colors mt-1.5 leading-normal max-w-md">
                      Sistem Informasi Pegawai
                    </p>
                  </div>
                </div>


                <div
                  class="text-slate-300 group-hover:text-white transition-all duration-300 transform group-hover:translate-x-1.5 shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                  </svg>
                </div>
              </button>

            </div>
          </div>

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