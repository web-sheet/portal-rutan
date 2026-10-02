<template>
    <div class="p-6 bg-white rounded-xl shadow-md border border-slate-100">
        <Toast />

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Daftar Perbaikan Fasilitas</h2>

            <!-- Filter Status -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-semibold text-slate-600">Filter Status:</label>
                <Select v-model="selectedStatus" :options="statusOptions" optionLabel="label" optionValue="value"
                    placeholder="Semua Status" class="w-48" @change="loadReports" />
            </div>
        </div>

        <div class="space-y-4">
            <!-- Toolbar / Search Bar -->
            <div class="flex justify-between items-center mb-3">
                <IconField iconPosition="left" class="w-full max-w-xs">

                    <InputText v-model="searchQuery" placeholder="Cari..." class="w-full" />
                </IconField>
            </div>

            <DataTable :value="filteredReports" :loading="loading" paginator :rows="10" responsiveLayout="scroll"
                class="p-datatable-sm">
                <Column field="id" header="No" style="width: 50px">
                    <template #body="slotProps">
                        {{ slotProps.index + 1 }}
                    </template>
                </Column>

                <!-- Tanggal Laporan -->
                <Column header="Tanggal Laporan" style="min-width: 140px">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.created_at) }}
                    </template>
                </Column>

                <!-- Pelapor -->
                <Column header="Pelapor">
                    <template #body="slotProps">
                        {{ slotProps.data.reporter?.nama || '-' }}
                    </template>
                </Column>

                <!-- Lokasi -->
                <Column header="Lokasi">
                    <template #body="slotProps">
                        {{ slotProps.data.location?.name || '-' }}
                    </template>
                </Column>

                <!-- Jenis Fasilitas -->
                <Column header="Jenis Fasilitas">
                    <template #body="slotProps">
                        {{ slotProps.data.facility_type?.name || '-' }}
                    </template>
                </Column>

                <Column field="description" header="Kendala / Kerusakan" style="max-width: 200px">
                    <template #body="slotProps">
                        <span class="line-clamp-2" :title="slotProps.data.description">
                            {{ slotProps.data.description }}
                        </span>
                    </template>
                </Column>

                <Column header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status === 'completed' ? 'Sudah Ditindaklanjuti' : 'Belum Ditindaklanjuti'"
                            :severity="slotProps.data.status === 'completed' ? 'success' : 'warn'" />
                    </template>
                </Column>

                <Column header="Foto Before">
                    <template #body="slotProps">
                        <img :src="storageUrl(slotProps.data.photo_before)" alt="Before"
                            class="w-16 h-12 object-cover rounded shadow cursor-pointer border hover:opacity-80 transition"
                            @click="openImageModal(slotProps.data.photo_before, 'Foto Kondisi Kerusakan (Before)')" />
                    </template>
                </Column>

                <!-- Column Aksi (Tambah Edit & Hapus) -->
                <Column header="Aksi" style="min-width: 180px">
                    <template #body="slotProps">
                        <div class="flex items-center gap-1">
                            <!-- Jika Pending: Tombol Proses Perbaikan -->
                            <Button v-if="slotProps.data.status === 'pending'" icon="pi pi-wrench"
                                class="p-button-sm p-button-warning" v-tooltip.top="'Proses Perbaikan'"
                                @click="openRepairModal(slotProps.data)" />

                            <!-- Jika Completed: Tombol Edit Perbaikan -->
                            <Button v-else icon="pi pi-pencil" class="p-button-sm p-button-info"
                                v-tooltip.top="'Edit Data Perbaikan'" @click="openRepairModal(slotProps.data)" />

                            <!-- Tombol Detail -->
                            <Button icon="pi pi-eye" class="p-button-sm p-button-secondary"
                                v-tooltip.top="'Lihat Detail'" @click="openDetailModal(slotProps.data)" />

                            <!-- Tombol Hapus -->
                            <Button icon="pi pi-trash" class="p-button-sm p-button-danger"
                                v-tooltip.top="'Hapus Laporan'" @click="confirmDelete(slotProps.data)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modal Konfirmasi Hapus (PrimeVue ConfirmDialog) -->
        <ConfirmDialog></ConfirmDialog>

        <!-- 1. DIALOG MODAL: PROSES PERBAIKAN (KAUR PERLENGKAPAN) -->
        <Dialog v-model:visible="repairDialog" header="Input Perbaikan & Anggaran" :modal="true"
            class="p-fluid max-w-xl w-full">
            <div v-if="selectedReport" class="space-y-4">
                <!-- Informasi Laporan Masuk (Read-Only) -->
                <div class="bg-slate-50 p-3 rounded-lg border text-sm space-y-1">
                    <p><strong>Nama Pelapor:</strong> {{ selectedReport.reporter?.nama }}</p>
                    <p><strong>Lokasi:</strong> {{ selectedReport.location?.name }}</p>
                    <p><strong>Jenis Fasilitas:</strong> {{ selectedReport.facility_type?.name }}</p>
                    <p><strong>Kendala:</strong> {{ selectedReport.description }}</p>
                </div>

                <form @submit.prevent="handleRepairSubmit" class="space-y-4 pt-2">
                    <!-- Sumber Anggaran -->

                    <!-- Tambahkan di Form Modal Perbaikan -->
                    <div class="field mb-3">
                        <label for="repaired_at" class="font-medium block mb-1">Tanggal & Waktu Perbaikan</label>
                        <!-- SESUDAH (DIPERBAIKI) -->
                        <DatePicker id="repaired_at" v-model="repairForm.repaired_at" dateFormat="yy-mm-dd"
                            placeholder="Pilih Tanggal Perbaikan" class="w-full" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            Sumber Anggaran <span class="text-red-500">*</span>
                        </label>
                        <Select v-model="repairForm.budget_source" :options="budgetOptions"
                            placeholder="Pilih Sumber Anggaran" class="w-full" />
                    </div>

                    <!-- Nominal Biaya -->
                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            Nominal Biaya (Rp)  
                        </label>
                        <InputNumber v-model="repairForm.cost" mode="currency" currency="IDR" locale="id-ID"
                            placeholder="0" class="w-full" />
                    </div>

                    <!-- Foto After -->
                    <div>
                        <label class="block text-sm font-semibold mb-1">
                            Foto Perbaikan (After) <span class="text-red-500">*</span>
                        </label>
                        <input type="file" accept="image/*" @change="handleAfterFileChange"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        <div v-if="previewAfter" class="mt-2 text-center">
                            <img :src="previewAfter" alt="Preview After"
                                class="max-h-40 mx-auto rounded shadow border" />
                        </div>
                    </div>

                    <!-- Catatan Perbaikan -->
                    <div>
                        <label class="block text-sm font-semibold mb-1">Catatan Perbaikan</label>
                        <Textarea v-model="repairForm.repair_notes" rows="3"
                            placeholder="Rincian tindakan teknis yang dilakukan..." class="w-full" />
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="repairDialog = false" />
                        <Button type="submit" label="Simpan & Selesaikan" icon="pi pi-check"
                            :loading="facilityStore.loading" />
                    </div>
                </form>
            </div>
        </Dialog>

        <!-- 2. DIALOG MODAL: DETAIL / BEFORE-AFTER VIEW -->
        <Dialog v-model:visible="detailDialog" header="Detail Perbaikan Facilities" :modal="true"
            class="max-w-2xl w-full">
            <div v-if="selectedReport" class="space-y-4">
                <!-- Grid Before & After -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="border rounded-lg p-2 text-center">
                        <p class="font-semibold text-sm mb-2 text-red-600">Foto BEFORE (Kerusakan)</p>
                        <img :src="storageUrl(selectedReport.photo_before)"
                            class="max-h-48 mx-auto rounded object-cover" />
                    </div>
                    <div class="border rounded-lg p-2 text-center">
                        <p class="font-semibold text-sm mb-2 text-green-600">Foto AFTER (Perbaikan)</p>
                        <img :src="storageUrl(selectedReport.photo_after)"
                            class="max-h-48 mx-auto rounded object-cover" />
                    </div>
                </div>

                <!-- Tambahkan di dalam Dialog (di bawah elemen foto / gambar) -->
                <div class="mt-4 grid grid-cols-2 gap-4 border-t pt-3 text-sm">
                    <div>
                        <span class="text-gray-500 font-medium block">Tanggal Laporan:</span>
                        <span class="text-gray-800 font-semibold">
                            {{ formatDate(selectedReport?.created_at) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-medium block">Tanggal Perbaikan:</span>
                        <span class="text-gray-800 font-semibold">
                            {{ formatDate(selectedReport?.repaired_at) }}
                        </span>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-lg border text-sm space-y-2">
                    <p><strong>Lokasi:</strong> {{ selectedReport.location?.name }}</p>
                    <p><strong>Fasilitas:</strong> {{ selectedReport.facility_type?.name }}</p>
                    <p><strong>Pelapor:</strong> {{ selectedReport.reporter?.nama }}</p>
                    <p><strong>Sumber Anggaran:</strong> {{ selectedReport.budget_source }}</p>
                    <p><strong>Nominal Biaya:</strong> Rp {{ Number(selectedReport.cost).toLocaleString('id-ID') }}</p>
                    <p><strong>Catatan Perbaikan:</strong> {{ selectedReport.repair_notes || '-' }}</p>
                </div>
            </div>
        </Dialog>


        <!-- Modal Preview Foto -->
        <Dialog v-model:visible="imageDialog.visible" :header="imageDialog.title" :modal="true"
            class="max-w-2xl w-full">
            <div class="text-center p-2">
                <img v-if="imageDialog.url" :src="imageDialog.url" :alt="imageDialog.title"
                    class="max-h-[70vh] mx-auto rounded shadow border object-contain" />
            </div>
        </Dialog>

    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useFacilityStore } from '@/stores/facilityStore';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';


const facilityStore = useFacilityStore();

// Ambil reports dari store secara reaktif
const reports = computed(() => facilityStore.reports);
const loading = computed(() => facilityStore.loading);


const toast = useToast();
const confirm = useConfirm();

// State Search
const searchQuery = ref('');

const selectedStatus = ref('');
const repairDialog = ref(false);
const detailDialog = ref(false);
const selectedReport = ref(null);
const previewAfter = ref(null);

const imageDialog = ref({
    visible: false,
    title: '',
    url: ''
});

// Fungsi untuk Membuka Modal Foto
const openImageModal = (imagePath, title = 'Preview Foto') => {
    if (!imagePath) return;
    imageDialog.value = {
        visible: true,
        title: title,
        url: storageUrl(imagePath) // Memakai fungsi storageUrl yang sudah kamu miliki
    };
};

const statusOptions = [
    { label: 'Semua Status', value: '' },
    { label: 'Belum Ditindaklanjuti', value: 'pending' },
    { label: 'Sudah Ditindaklanjuti', value: 'completed' },
];


// Computed Property untuk Filter Search Otomatis
const filteredReports = computed(() => {
    if (!searchQuery.value) return reports.value;

    const query = searchQuery.value.toLowerCase();
    return reports.value.filter(item => {
        return (
            item.reporter?.name?.toLowerCase().includes(query) ||
            item.location?.name?.toLowerCase().includes(query) ||
            item.facility_type?.name?.toLowerCase().includes(query) ||
            item.description?.toLowerCase().includes(query)
        );
    });
});

// Fungsi Hapus Data
const confirmDelete = (report) => {
    confirm.require({
        message: `Apakah Anda yakin ingin menghapus laporan dari ${report.reporter?.name || 'pelapor ini'}?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-danger',
        rejectLabel: 'Batal',
        acceptLabel: 'Hapus',
        accept: async () => {
            try {
                await facilityStore.deleteReport(report.id);
                toast.add({
                    severity: 'success',
                    summary: 'Berhasil',
                    detail: 'Data laporan berhasil dihapus',
                    life: 3000
                });
                loadReports(); // Refresh data tabel
            } catch (err) {
                toast.add({
                    severity: 'error',
                    summary: 'Gagal',
                    detail: err.message || 'Gagal menghapus laporan',
                    life: 3000
                });
            }
        }
    });
};

const budgetOptions = ['DIPA Kantor', 'Swadaya', 'Pihak ke 3'];
const storageUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;

    const storageBase = import.meta.env.VITE_STORAGE_URL || 'http://127.0.0.1:8000/storage';
    return `${storageBase}/${path.replace(/^\/?(storage\/)?/, '')}`;
};

const loadReports = async (page = 1) => {
    // 1. Panggil store
    await facilityStore.fetchReports(page, selectedStatus.value);

    // 2. Log isi store
    console.log('--- CEK STORE REPORTS ---', facilityStore.reports);
    console.log('--- CEK COMPUTED REPORTS ---', reports.value);
};

onMounted(() => {
    loadReports();
});




const repairForm = ref({
    budget_source: 'DIPA Kantor',
    repaired_at: new Date(), // default tanggal & waktu saat ini
    cost: 0,
    photo_after: null,
    repair_notes: '',
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const openRepairModal = (report) => {
    selectedReport.value = report;

    // Jika report.repaired_at sudah ada dari DB, convert ke Date Object agar DatePicker membaca dengan benar.
    // Jika belum ada, default-kan ke new Date()
    const initialDate = report.repaired_at ? new Date(report.repaired_at) : new Date();

    repairForm.value = {
        budget_source: report.budget_source || 'DIPA Kantor',
        repaired_at: initialDate,
        cost: report.cost || 0,
        photo_after: null,
        repair_notes: report.repair_notes || '',
    };

    previewAfter.value = report.photo_after ? storageUrl(report.photo_after) : null;
    repairDialog.value = true;
};

const handleRepairSubmit = async () => {
    // Foto HANYA wajib diunggah jika belum ada di database DAN user belum memilih foto baru
    const hasExistingPhoto = Boolean(selectedReport.value?.photo_after);
    const hasNewPhoto = Boolean(repairForm.value.photo_after);

    if (!hasExistingPhoto && !hasNewPhoto) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Foto perbaikan (After) wajib diunggah',
            life: 3000
        });
        return;
    }

    try {
        let formattedDate = null;
        if (repairForm.value.repaired_at) {
            const d = new Date(repairForm.value.repaired_at);
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');

            // Kirim YYYY-MM-DD saja
            formattedDate = `${year}-${month}-${day}`;
        }

        const payload = {
            ...repairForm.value,
            repaired_at: formattedDate
        };

        // ==========================================
        // 🔍 DEBUG 1: Cek Objek JSON Plain
        // ==========================================
        console.log('--- DATA REPAIR FORM (PLAIN OBJECT) ---', payload);

        // Jika facilityStore.submitRepair mengonversi payload ke FormData (karena ada upload file photo_after):
        const formData = new FormData();
        Object.keys(payload).forEach(key => {
            formData.append(key, payload[key]);
        });

        // ==========================================
        // 🔍 DEBUG 2: Cek Isi FormData
        // ==========================================
        console.log('--- DATA REPAIR FORM (FORMDATA) ---');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }

        await facilityStore.submitRepair(selectedReport.value.id, payload);
        repairDialog.value = false;
        loadReports();

        toast.add({
            severity: 'success',
            summary: 'Berhasil',
            detail: 'Data perbaikan & anggaran berhasil disimpan!',
            life: 3000,
        });
    } catch (err) {
        // ==========================================
        // 🔍 DEBUG 3: Cek Respon Error Backend
        // ==========================================
        console.error('--- ERROR RESPONSE BACKEND ---', err.response?.data || err);

        toast.add({
            severity: 'error',
            summary: 'Gagal',
            detail: err.message || 'Gagal menyimpan perbaikan',
            life: 3000,
        });
    }
};



const openDetailModal = (report) => {
    selectedReport.value = report;
    detailDialog.value = true;
};

const handleAfterFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        repairForm.value.photo_after = file;
        previewAfter.value = URL.createObjectURL(file);
    }
};
</script>