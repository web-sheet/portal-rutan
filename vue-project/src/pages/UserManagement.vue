<template>
  <div class="p-6 max-w-6xl mx-auto space-y-6">
    <Toast />

    <div class="flex justify-between items-center border-b border-slate-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Manajemen Petugas Rutan</h1>
        <p class="text-sm text-slate-500">Kelola hak akses akun mulai dari Kaur, Kasi, Karutan, hingga Admin Aplikasi.
        </p>
      </div>
      <Button label="Tambah Petugas" icon="pi pi-user-plus" severity="success" class="p-button-sm"
        @click="openModal(null)" />
    </div>

    <div class="card bg-white shadow-sm border border-slate-200 rounded-xl overflow-hidden">
      <DataTable :value="users" stripe responsiveLayout="scroll" class="p-datatable-sm text-sm">
        <Column field="name" header="Nama Lengkap" sortable class="font-medium text-slate-800"></Column>

        <Column header="Kredensial Login">
          <template #body="slotProps">
            <div class="flex flex-col">
              <span class="font-semibold text-slate-700">@{{ slotProps.data.username }}</span>
              <span class="text-xs text-slate-400">{{ slotProps.data.email }}</span>
            </div>
          </template>
        </Column>

        <Column field="role" header="Role Hak Akses" sortable>
          <template #body="slotProps">
            <Tag :value="slotProps.data.role" :severity="getRoleSeverity(slotProps.data.role)"
              class="uppercase font-bold" />
          </template>
        </Column>

        <Column header="Aksi" class="text-right" headerClass="text-right">
          <template #body="slotProps">
            <Button icon="pi pi-pencil" severity="secondary" text rounded v-tooltip.top="'Edit Data'"
              @click="openModal(slotProps.data)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <Dialog v-model:visible="isModalOpen" :header="isEditMode ? '⚙️ Edit Data Petugas' : '👤 Tambah Petugas Baru'"
      :modal="true" :style="{ width: '450px' }" class="p-fluid">
      <form @submit.prevent="handleSubmit" class="space-y-4 mt-2">
        <div class="flex flex-col gap-1">
          <label for="name" class="text-sm font-semibold text-slate-700">Nama Lengkap</label>
          <InputText id="name" v-model="form.name" required autofocus placeholder="Masukkan nama petugas" />
        </div>

        <div class="flex flex-col gap-1">
          <label for="username" class="text-sm font-semibold text-slate-700">Username</label>
          <InputText id="username" v-model="form.username" required placeholder="Contoh: kaur_perlengkapan" />
        </div>

        <div class="flex flex-col gap-1">
          <label for="email" class="text-sm font-semibold text-slate-700">Alamat Email</label>
          <InputText id="email" v-model="form.email" type="email" required placeholder="petugas@mail.com" />
        </div>

        <div class="flex flex-col gap-1">
          <label for="role" class="text-sm font-semibold text-slate-700">Role Kedinasan</label>
          <Select id="role" v-model="form.role" :options="roleOptions" optionLabel="label" optionValue="value"
            placeholder="Pilih Tingkat Jabatan" required />
        </div>

        <div class="flex flex-col gap-1">
          <label for="password" class="text-sm font-semibold text-slate-700">
            {{ isEditMode ? 'Password Baru (Kosongkan jika tidak diganti)' : 'Kata Sandi Akun' }}
          </label>
          <Password id="password" v-model="form.password" :toggleMask="true" :feedback="false" :required="!isEditMode"
            placeholder="••••••••" />
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
          <Button label="Batal" icon="pi pi-times" text severity="secondary" @click="isModalOpen = false" />
          <Button type="submit" :label="isEditMode ? 'Simpan' : 'Daftarkan'" icon="pi pi-check" :loading="loadingSubmit"
            severity="success" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api/axios';
import { useToast } from 'primevue/usetoast';

// Import komponen PrimeVue yang dibutuhkan
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Password from 'primevue/password';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';

const toast = useToast();
const users = ref([]);
const isModalOpen = ref(false);
const isEditMode = ref(false);
const currentUserId = ref(null);
const loadingSubmit = ref(false);

const form = ref({ name: '', username: '', email: '', role: 'kaur', password: '' });

// Opsi pilihan Dropdown Jabatan Rutan
const roleOptions = [
  { label: 'Kaur Perlengkapan', value: 'perlengkapan' },
  { label: 'Kaur Kepegawaian', value: 'kepegawaian' },
  { label: 'Kasi Pengelolaan', value: 'kasi' },
  { label: 'Staf Perlengkapan', value: 'staf_perlengkapan' },
  { label: 'Admin Aplikasi', value: 'admin' },
  { label: 'Internal', value: 'karutan' }
];

const fetchUsers = async () => {
  try {
    const res = await api.get('/users');
    users.value = res.data;
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengambil data user rutan', life: 3000 });
  }
};

const openModal = (user = null) => {
  if (user) {
    isEditMode.value = true;
    currentUserId.value = user.id;
    form.value = { name: user.name, username: user.username, email: user.email, role: user.role, password: '' };
  } else {
    isEditMode.value = false;
    currentUserId.value = null;
    form.value = { name: '', username: '', email: '', role: 'kaur', password: '' };
  }
  isModalOpen.value = true;
};

const handleSubmit = async () => {
  loadingSubmit.value = true;
  try {
    if (isEditMode.value) {
      await api.put(`/users/${currentUserId.value}`, form.value);
      toast.add({ severity: 'success', summary: 'Sukses', detail: 'Data petugas berhasil diperbarui', life: 3000 });
    } else {
      await api.post('/users', form.value);
      toast.add({ severity: 'success', summary: 'Sukses', detail: 'Petugas baru berhasil ditambahkan', life: 3000 });
    }
    isModalOpen.value = false;
    fetchUsers();
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Error', detail: err.response?.data?.message || 'Terjadi kesalahan sistem', life: 3000 });
  } finally {
    loadingSubmit.value = false;
  }
};

// Menentukan warna tag PrimeVue berdasarkan tingkatan role dinas rutan
const getRoleSeverity = (role) => {
  switch (role) {
    case 'admin': return 'danger';    // Merah
    case 'kepegawaian': return 'warn';    // Oranye/Kuning
    case 'perlengkapan': return 'warn';    // Oranye/Kuning
    case 'staf_perlengkapan': return 'warn';    // Oranye/Kuning
    case 'kasi': return 'info';       // Biru
    default: return 'success';        // Hijau (kaur)
  }
};

onMounted(() => {
  fetchUsers();
});
</script>