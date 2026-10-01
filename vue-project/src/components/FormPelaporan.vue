<template>
  <div class="max-w-2xl mx-auto p-6 bg-white rounded-xl shadow-md border border-slate-100">
    <!-- Tambahkan komponen Toast di sini -->
    <Toast />

    <h2 class="text-2xl font-bold text-slate-800 mb-6 text-center">Lapor Kerusakan Fasilitas</h2>

    <form @submit.prevent="handleSubmit" class="space-y-5">
      <!-- 1. Nama Petugas (Read-only / Auto dari Token) -->
      <div class="flex flex-col gap-1.5">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Petugas <span class="text-red-500">*</span></label>
        <Select v-model="form.reporter_id" :options="pegawaiList" optionLabel="nama" optionValue="id" filter
          placeholder="Pilih Nama Petugas" class="w-full rounded-xl border-slate-300" />
     
      </div>

      <!-- 2. Lokasi Fasilitas (PrimeVue Select) -->
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
          Lokasi Fasilitas <span class="text-red-500">*</span>
        </label>
        <Select v-model="form.location_id" :options="facilityStore.locations" optionLabel="name" optionValue="id"
          placeholder="Pilih Lokasi Fasilitas" class="w-full" :class="{ 'p-invalid': errors.location_id }" />
        <small v-if="errors.location_id" class="text-red-500 text-xs mt-1">
          {{ errors.location_id[0] }}
        </small>
      </div>

      <!-- 3. Jenis Fasilitas (PrimeVue Select) -->
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
          Jenis Fasilitas <span class="text-red-500">*</span>
        </label>
        <Select v-model="form.facility_type_id" :options="facilityStore.facilityTypes" optionLabel="name"
          optionValue="id" placeholder="Pilih Jenis Fasilitas" class="w-full"
          :class="{ 'p-invalid': errors.facility_type_id }" />
        <small v-if="errors.facility_type_id" class="text-red-500 text-xs mt-1">
          {{ errors.facility_type_id[0] }}
        </small>
      </div>

      <!-- 4. Kendala / Kerusakan (Textarea) -->
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
          Kendala / Kerusakan <span class="text-red-500">*</span>
        </label>
        <Textarea v-model="form.description" rows="4"
          placeholder="Jelaskan detail kerusakan (misal: Pipa bocor, kran tidak bisa diputar)"
          class="w-full border rounded-lg p-2" :class="{ 'p-invalid': errors.description }" />
        <small v-if="errors.description" class="text-red-500 text-xs mt-1">
          {{ errors.description[0] }}
        </small>
      </div>

      <!-- 5. Foto Before (File Upload + Preview) -->
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">
          Foto Kondisi Kerusakan (Before) <span class="text-red-500">*</span>
        </label>

        <div
          class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 rounded-lg p-4 bg-slate-50 hover:bg-slate-100 transition cursor-pointer relative">
          <input type="file" accept="image/*" @change="handleFileChange"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
          <div v-if="!previewImage" class="text-center text-slate-500">
            <i class="pi pi-camera text-3xl mb-2"></i>
            <p class="text-sm font-medium">Klik atau drag foto kerusakan ke sini</p>
            <p class="text-xs text-slate-400">Format: JPG, PNG (Maks 5MB)</p>
          </div>
          <div v-else class="relative w-full text-center">
            <img :src="previewImage" alt="Preview" class="max-h-48 mx-auto rounded-lg shadow-sm" />
            <span class="text-xs text-slate-500 mt-2 block">Klik foto untuk mengganti</span>
          </div>
        </div>

        <small v-if="errors.photo_before" class="text-red-500 text-xs mt-1">
          {{ errors.photo_before[0] }}
        </small>
      </div>

      <!-- Submit Button -->
      <div class="pt-3">
        <Button type="submit" label="Kirim Laporan" icon="pi pi-send" :loading="facilityStore.loading"
          class="w-full p-button-primary" />
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useFacilityStore } from '@/stores/facilityStore';

import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Toast from 'primevue/toast'; // Import komponen Toast
import { useToast } from 'primevue/usetoast';
import api from "@/api/axios";

const facilityStore = useFacilityStore();
const toast = useToast();

const form = ref({
  location_id: null,
  facility_type_id: null,
  description: '',
  photo_before: null,
});

const previewImage = ref(null);
const errors = ref({});
const pegawaiList = ref([]);

// Fetch data list pegawai untuk dropdown
const fetchAllPegawai = async () => {
  try {
    const response = await api.get('/pegawai');

    if (Array.isArray(response.data)) {
      pegawaiList.value = response.data;
    } else if (response.data && typeof response.data === 'object') {
      pegawaiList.value = Object.values(response.data);
    } else {
      pegawaiList.value = [];
    }
  } catch (error) {
    console.error("Gagal memuat list data pegawai", error);
    pegawaiList.value = [];
  }
};

onMounted(async () => {
  await facilityStore.fetchOptions();
  await fetchAllPegawai();
});

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.value.photo_before = file;
    previewImage.value = URL.createObjectURL(file);
  }
};

const handleSubmit = async () => {
  errors.value = {};

  // Validasi manual di frontend sebelum dikirim
  if (!form.value.reporter_id) {
    errors.value.reporter_id = ['Nama petugas pelapor wajib dipilih'];
  }

  if (!form.value.photo_before) {
    errors.value.photo_before = ['Foto kondisi kerusakan wajib diunggah'];
  }

  // Jika ada error validasi di frontend, stop proses submit
  if (Object.keys(errors.value).length > 0) {
    toast.add({
      severity: 'warn',
      summary: 'Peringatan',
      detail: 'Mohon lengkapi semua field yang wajib diisi',
      life: 3000
    });
    return;
  }

  try {
    // Kirim form.value (termasuk reporter_id) ke store
    await facilityStore.submitReport(form.value);

    // Reset Form ke kondisi awal
    form.value = {
      reporter_id: null, // <-- Tambahkan reset untuk reporter_id
      location_id: null,
      facility_type_id: null,
      description: '',
      photo_before: null,
    };
    previewImage.value = null;

    toast.add({
      severity: 'success',
      summary: 'Berhasil',
      detail: 'Laporan kerusakan berhasil dikirim!',
      life: 3000
    });
  } catch (err) {
    if (err.errors) {
      // Menangkap error validasi dari backend Laravel (422 Unprocessable Entity)
      errors.value = err.errors;
    } else {
      toast.add({
        severity: 'error',
        summary: 'Gagal',
        detail: err.message || 'Gagal mengirim laporan',
        life: 3000
      });
    }
  }
};
</script>