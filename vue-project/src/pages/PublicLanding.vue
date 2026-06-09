<template>
  <div class="min-h-screen flex flex-col bg-slate-100">
    <!-- HEADER -->
    <header
      class="flex items-center justify-between px-6 py-4 bg-white shadow-md border-b border-slate-200"
    >
      <div>
        <h1 class="text-2xl font-bold text-primary mb-1">
          Portal Pengelolaan Rutan
        </h1>
        <p class="text-sm text-slate-500">
          Rutan Kelas I Pondok Bambu
        </p>
      </div>

      <Button
        label="Login"
        icon="pi pi-sign-in"
        outlined
        @click="goToLogin"
      />
    </header>

    <!-- MAIN -->
    <main class="flex-1 flex items-center justify-center px-4">
      <div
        class="w-full max-w-3xl bg-white rounded-3xl shadow-lg p-8 md:p-12 text-center"
      >
        <h2 class="text-4xl md:text-5xl font-bold text-slate-800 mb-4">
          Selamat Datang
        </h2>

        <p class="text-lg md:text-xl text-slate-500 mb-8 leading-relaxed">
          di Portal Layanan Pengelolaan
          <br />
          Rutan Kelas I Pondok Bambu
        </p>

        <div class="flex flex-wrap justify-center gap-4">
          <Button
            label="Pengajuan Barang"
            icon="pi pi-box"
            size="large"
            @click="goToPengajuanBarang"
          />

          <Button
            label="Laporan Kejadian"
            icon="pi pi-file-edit"
            severity="secondary"
            size="large"
            @click="goToLaporanKejadian"
          />
        </div>
      </div>
    </main>

    <!-- FOOTER -->
    <footer
      class="text-center py-4 bg-white border-t border-slate-200 text-slate-500 text-sm"
    >
      © 2026 Portal Pengelolaan Rutan
    </footer>
  </div>
</template>

<script setup>
import Button from 'primevue/button'
import { useRouter } from 'vue-router'

const router = useRouter()

const goToLogin = () => {
  router.push('/login')
}

const goToPengajuanBarang = () => {
  router.push('/requests')
}

const goToLaporanKejadian = () => {
  router.push('/laporan-kejadian')
}
</script>