<template>
  <div class="p-6 max-w-4xl mx-auto space-y-6">
    <Toast />

    <div class="border-b border-slate-200 pb-4">
      <h1 class="text-2xl font-bold text-slate-900">Pengaturan Profil</h1>
      <p class="text-sm text-slate-500">Kelola informasi akun dan keamanan berkala petugas rutan.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <div class="md:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
        <div>
          <h2 class="text-lg font-semibold text-slate-800 mb-4">Informasi Akun</h2>
          
          <form @submit.prevent="updateProfile" class="space-y-4 p-fluid">
            <div class="flex flex-col gap-1">
              <label class="text-sm font-semibold text-slate-700">Nama Lengkap</label>
              <InputText v-model="profileForm.name" type="text" required placeholder="Masukkan nama lengkap" />
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-sm font-semibold text-slate-700">Alamat Email</label>
              <InputText v-model="profileForm.email" type="email" required placeholder="petugas@mail.com" />
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-sm font-semibold text-slate-700">Username</label>
              <InputText v-model="profileForm.username" type="text" required placeholder="username_petugas" />
            </div>

            <div class="flex justify-end pt-2">
              <Button 
                type="submit" 
                label="Simpan Perubahan" 
                icon="pi pi-check" 
                :loading="loadingProfile" 
                severity="success"
                class="w-auto px-4"
              />
            </div>
          </form>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Ubah Password</h2>

        <form @submit.prevent="updatePassword" class="space-y-4 p-fluid">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold text-slate-700">Password Saat Ini</label>
            <Password 
              v-model="passwordForm.current_password" 
              :toggleMask="true" 
              :feedback="false" 
              required 
              placeholder="••••••••"
            />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold text-slate-700">Password Baru</label>
            <Password 
              v-model="passwordForm.password" 
              :toggleMask="true" 
              :feedback="false" 
              required 
              placeholder="••••••••"
            />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold text-slate-700">Konfirmasi Password</label>
            <Password 
              v-model="passwordForm.password_confirmation" 
              :toggleMask="true" 
              :feedback="false" 
              required 
              placeholder="••••••••"
            />
          </div>

          <div class="pt-2">
            <Button 
              type="submit" 
              label="Perbarui Password" 
              icon="pi pi-lock" 
              :loading="loadingPassword" 
              severity="danger" 
              class="w-full"
            />
          </div>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from "@/api/axios";
import { useToast } from 'primevue/usetoast';

// Import Komponen Resmi PrimeVue
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Toast from 'primevue/toast';

const toast = useToast();

const profileForm = ref({ name: '', email: '', username: '' });
const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' });

const loadingProfile = ref(false);
const loadingPassword = ref(false);

// Mengambil data pengguna aktif
const fetchUser = async () => {
  try {
    const response = await api.get('/me'); // Sesuaikan endpoint Anda (/me atau /user)
    profileForm.value.name = response.data.name;
    profileForm.value.email = response.data.email;
    profileForm.value.username = response.data.username;
  } catch (error) {
    toast.add({ 
      severity: 'error', 
      summary: 'Sesi Habis', 
      detail: 'Gagal mengambil data pengguna. Sesi mungkin kedaluwarsa.', 
      life: 4000 
    });
  }
};

// Mengubah informasi profil
const updateProfile = async () => {
  loadingProfile.value = true;
  try {
    const response = await api.put('/profile', profileForm.value); // Sesuaikan rute update Anda
    toast.add({ 
      severity: 'success', 
      summary: 'Sukses', 
      detail: response.data.message || 'Profil berhasil diperbarui!', 
      life: 3000 
    });
  } catch (error) {
    toast.add({ 
      severity: 'error', 
      summary: 'Gagal', 
      detail: error.response?.data?.message || 'Terjadi kesalahan saat memperbarui profil.', 
      life: 4000 
    });
  } finally {
    loadingProfile.value = false;
  }
};

// Mengubah kata sandi
const updatePassword = async () => {
  loadingPassword.value = true;
  try {
    const response = await api.put('/profile/password', passwordForm.value); // Sesuaikan rute update password Anda
    toast.add({ 
      severity: 'success', 
      summary: 'Sukses', 
      detail: response.data.message || 'Password berhasil diganti!', 
      life: 3000 
    });
    // Bersihkan field password setelah sukses
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
  } catch (error) {
    toast.add({ 
      severity: 'error', 
      summary: 'Gagal', 
      detail: error.response?.data?.message || 'Gagal memperbarui password. Periksa kembali inputan Anda.', 
      life: 4000 
    });
  } finally {
    loadingPassword.value = false;
  }
};

onMounted(() => {
  fetchUser();
});
</script>