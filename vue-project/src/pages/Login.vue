<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'

const router = useRouter()
const auth = useAuthStore()
const toast = useToast()

const form = reactive({
  login: '',
  password: '',
})

const loading = reactive({
  submit: false,
})

const submit = async () => {
  loading.submit = true;

  try {
    // 1. Jalankan proses login
    await auth.login(form.login, form.password);

    // 2. Ambil role
    const userRole = auth.user?.role;

    // 3. Logika pengalihan (Routing)
    // Jika role adalah 'kepegawaian', arahkan ke dashboard absensi
    if (userRole === 'kepegawaian') {
      router.push('/absensi/dashboard');
    }
    // Untuk semua role lainnya (termasuk staf_perlengkapan), arahkan ke dashboard umum
    else {
      router.push('/dashboard');
    }

  } catch (err) {
    toast.add({
      severity: 'error',
      summary: 'Login Gagal',
      detail: 'Username atau password salah',
      life: 3000,
    });
  } finally {
    loading.submit = false;
  }
};
</script>

<template>
  <Toast />

  <div class="min-h-screen flex items-center justify-center px-3"
    style="background: linear-gradient(135deg, #f8fafc, #eef2ff)">
    <!-- class disesuaikan ke Tailwind (shadow-md rounded-2xl) -->
    <Card style="width: 420px" class="shadow-md rounded-2xl overflow-hidden">
      <template #title>
        <div class="text-center">
          <h2 class="text-2xl font-bold mb-2 text-slate-800">Portal Pengelolaan Rutan</h2>
          <span class="text-slate-500 text-sm">
            Silakan login untuk melanjutkan
          </span>
        </div>
      </template>

      <template #content>
        <!-- Menggunakan tag <form> untuk aksesibilitas (bisa submit pakai tombol Enter) -->
        <form @submit.prevent="submit" class="flex flex-col gap-4 mt-3">

          <div class="flex flex-col gap-2">
            <label class="text-sm font-medium text-slate-700">Username / Email</label>
            <InputText v-model="form.login" placeholder="Masukkan email" class="w-full" required />
          </div>

          <div class="flex flex-col gap-2">
            <label class="text-sm font-medium text-slate-700">Password</label>
            <!-- Di PrimeVue 4, styling full-width dilempar ke inputStyle atau class -->
            <Password v-model="form.password" placeholder="Masukkan password" toggleMask :feedback="false"
              class="w-full" inputClass="w-full" required />
          </div>

          <Button type="submit" label="Login" icon="pi pi-sign-in" class="w-full mt-2" :loading="loading.submit" />
        </form>
      </template>
    </Card>
  </div>
</template>

<style scoped>
/* Style tambahan jika diperlukan, tapi utilitas Tailwind di atas sudah cukup */
</style>