<script setup>
import { ref, computed, onMounted } from "vue";
import { usePegawaiStore } from "@/stores/pegawai"; 
import { useToast } from "primevue/usetoast";
import * as XLSX from "xlsx";

// Komponen PrimeVue
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Toast from "primevue/toast";

const store = usePegawaiStore(); 
const toast = useToast();

const excelInput = ref(null);
const dialog = ref(false);
const editMode = ref(false);
const detailDialog = ref(false);
const selectedPegawai = ref(null);

// State untuk menampung pegawai yang dicentang (Bulk Delete)
const selectedPegawais = ref([]);

const search = ref("");
const statusFilter = ref(null);

const form = ref({
    nama: "",
    nip: "",
    jabatan: "",
    pangkat: "",
    golongan: "",
    status: "",
});

onMounted(() => {
    store.fetchPegawais();
});

const filteredPegawais = computed(() => {
    return store.pegawais.filter((item) => {
        const matchSearch =
            item.nama?.toLowerCase().includes(search.value.toLowerCase()) ||
            item.nip?.toLowerCase().includes(search.value.toLowerCase()) ||
            item.jabatan?.toLowerCase().includes(search.value.toLowerCase());

        const matchStatus =
            !statusFilter.value ||
            item.status === statusFilter.value;

        return matchSearch && matchStatus;
    });
});

const statusColor = (status) => {
    switch (status?.toLowerCase()) {
        case "aktif":
            return "success";
        case "cuti":
            return "warning";
        case "pensiun":
        case "tidak aktif":
            return "danger";
        default:
            return "secondary";
    }
};

const triggerExcelInput = () => {
    excelInput.value.click();
};

// LOGIKA PEMBACAAN EXCEL UNTUK DATA PEGAWAI
const handleExcelUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = async (e) => {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: "array" });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];

            const rawData = XLSX.utils.sheet_to_json(worksheet);

            if (rawData.length === 0) {
                throw new Error("File Excel kosong.");
            }

            const mappedPegawai = rawData.map(row => {
                const cleanedRow = {};
                Object.keys(row).forEach(key => {
                    cleanedRow[key.toLowerCase().trim()] = row[key];
                });

                const nama = cleanedRow['nama'] || cleanedRow['nama pegawai'] || cleanedRow['name'] || '';
                const nip = cleanedRow['nip'] || cleanedRow['nomor induk'] || '';
                const jabatan = cleanedRow['jabatan'] || cleanedRow['position'] || '-';
                const pangkat = cleanedRow['pangkat'] || '-';
                const golongan = cleanedRow['golongan'] || '-';

                let status = cleanedRow['status'] || 'aktif';
                status = status.toLowerCase().trim();

                return {
                    nama: nama.toString().trim(),
                    nip: nip.toString().trim(),
                    jabatan: jabatan.toString().trim(),
                    pangkat: pangkat.toString().trim(),
                    golongan: golongan.toString().trim(),
                    status
                };
            })
                .filter(p => p.nama !== "" && p.nip !== "");

            if (mappedPegawai.length === 0) {
                toast.add({ severity: 'error', summary: 'Gagal', detail: 'Kolom "Nama" dan "NIP" wajib diisi atau tidak ditemukan', life: 4000 });
                return;
            }

            // Eksekusi langsung ke Pinia Action
            await store.importExcelPegawai(mappedPegawai);

            toast.add({ severity: 'success', summary: 'Sukses Import', detail: `${mappedPegawai.length} data pegawai berhasil dimasukkan`, life: 3000 });
            await store.fetchPegawais();
        } catch (err) {
            console.error(err);
            const errorMessage = err.response?.data?.message || 'Format Excel tidak sesuai validasi data pegawai';
            toast.add({ severity: 'error', summary: 'Gagal Import', detail: errorMessage, life: 5000 });
        } finally {
            event.target.value = "";
        }
    };
    reader.readAsArrayBuffer(file);
};

// LOGIKA HAPUS MASSAL (BULK DELETE) VIA PINIA
const confirmBulkDelete = async () => {
    if (confirm(`Yakin ingin menghapus ${selectedPegawais.value.length} pegawai ini secara massal?`)) {
        try {
            const ids = selectedPegawais.value.map(p => p.id);

            // Eksekusi langsung ke Pinia Action
            await store.deleteMultiplePegawais(ids);

            toast.add({ severity: 'success', summary: 'Sukses', detail: `${ids.length} data pegawai berhasil dihapus`, life: 3000 });
            selectedPegawais.value = []; // Kosongkan pilihan centang setelah berhasil
            await store.fetchPegawais();
        } catch (err) {
            console.error(err);
            toast.add({ severity: 'error', summary: 'Gagal', detail: err.response?.data?.message || 'Gagal mengeksekusi hapus massal', life: 3000 });
        }
    }
};

const openDetail = (data) => {
    selectedPegawai.value = data;
    detailDialog.value = true;
};

const deletePegawai = async (id) => {
    if (!confirm("Hapus data pegawai?")) return;
    try {
        await store.deletePegawai(id);
        toast.add({ severity: 'success', summary: 'Sukses', detail: 'Data pegawai berhasil dihapus', life: 3000 });
        selectedPegawais.value = selectedPegawais.value.filter(item => item.id !== id);
        await store.fetchPegawais();
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal menghapus data pegawai', life: 3000 });
    }
};

const openCreate = () => {
    editMode.value = false;
    // Tambahkan ttd: null di inisialisasi awal
    form.value = { nama: "", nip: "", jabatan: "", pangkat: "", golongan: "", status: "", ttd: null };
    dialog.value = true;
};

const openEdit = (data) => {
    editMode.value = true;
    form.value = {
        id: data.id,
        nama: data.nama,
        nip: data.nip,
        jabatan: data.jabatan,
        pangkat: data.pangkat,
        golongan: data.golongan,
        status: data.status,
        ttd: data.ttd, // <-- Ambil data ttd lama dari database untuk ditampilkan di preview jika ada
    };
    dialog.value = true;
};

// --- TAMBAHAN FUNGSI BARU UNTUK PROSES BASE64 ---
const handleTtdUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validasi ukuran file (Opsional, contoh maks 2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert("Ukuran gambar terlalu besar! Maksimal 2MB.");
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        // e.target.result berisi string base64 gambar yang langsung bisa dibaca tag <img> dan disimpan ke database
        form.value.ttd = e.target.result;
    };
    reader.readAsDataURL(file);
};

const save = async () => {
    try {
        if (editMode.value) {
            await store.updatePegawai(form.value.id, form.value);
            toast.add({ severity: 'success', summary: 'Sukses', detail: 'Data pegawai berhasil diperbarui', life: 3000 });
        } else {
            await store.createPegawai(form.value);
            toast.add({ severity: 'success', summary: 'Sukses', detail: 'Data pegawai baru berhasil disimpan', life: 3000 });
        }
        dialog.value = false;
        await store.fetchPegawais();
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan sistem', life: 3000 });
    }
};
</script>

<template>
    <div class="p-3 md:p-4">
        <Toast />

        <input type="file" ref="excelInput" class="hidden" accept=".xlsx, .xls" @change="handleExcelUpload" />

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <h2 class="text-2xl font-bold">Data Pegawai</h2>

            <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                <Button v-if="selectedPegawais.length > 0" severity="danger" class="p-button-sm flex items-center gap-2"
                    @click="confirmBulkDelete">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <span>Hapus ({{ selectedPegawais.length }})</span>
                </Button>

                <Button severity="secondary" outlined class="p-button-sm flex items-center gap-2 bg-white"
                    @click="triggerExcelInput">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-4 h-4 text-emerald-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>Import Excel</span>
                </Button>

                <Button label="Tambah Pegawai" icon="pi pi-plus" @click="openCreate" />
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <span class="relative w-full">
                <InputText v-model="search" placeholder="Cari nama, nip, jabatan..." class="w-full" />
            </span>

            <Select v-model="statusFilter" :options="[
                { label: 'Aktif', value: 'aktif' },
                { label: 'Cuti', value: 'cuti' },
                { label: 'Pensiun', value: 'pensiun' }
            ]" optionLabel="label" optionValue="value" placeholder="Filter Status" showClear class="w-full md:w-56" />
        </div>

        <p class="text-sm text-gray-500 mb-3">
            Total: {{ filteredPegawais.length }} pegawai
            <span v-if="selectedPegawais.length > 0" class="text-emerald-600 font-semibold">
                ({{ selectedPegawais.length }} dipilih)
            </span>
        </p>

        <div class="overflow-x-auto">
            <DataTable :value="filteredPegawais" v-model:selection="selectedPegawais" dataKey="id"
                :loading="store.loading" paginator :rows="10" :rowsPerPageOptions="[5, 10, 20]" stripedRows
                class="text-sm">
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>

                <Column field="nama" header="Nama" />
                <Column field="nip" header="NIP" />

                <Column field="jabatan" header="Jabatan" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <Column field="pangkat" header="Pangkat" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <Column field="golongan" header="Golongan" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <Column header="Status">
                    <template #body="{ data }">
                        <Tag :value="data.status" :severity="statusColor(data.status)" />
                    </template>
                </Column>

                <Column header="Aksi">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button icon="pi pi-eye" severity="info" size="small" @click="openDetail(data)" />
                            <Button icon="pi pi-pencil" severity="warning" size="small" @click="openEdit(data)" />
                            <Button icon="pi pi-trash" severity="danger" size="small" @click="deletePegawai(data.id)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="detailDialog" modal :style="{ width: '95vw', maxWidth: '450px' }">

            <template #header>
                <div class="w-full text-center font-semibold text-lg">
                    Detail Pegawai
                </div>
            </template>
            <div v-if="selectedPegawai" class="space-y-4">
                <div class="space-y-3">
                    <p><b>Nama:</b> {{ selectedPegawai.nama }}</p>
                    <p><b>NIP:</b> {{ selectedPegawai.nip }}</p>
                    <p><b>Jabatan:</b> {{ selectedPegawai.jabatan }}</p>
                    <p><b>Pangkat:</b> {{ selectedPegawai.pangkat }}</p>
                    <p><b>Golongan:</b> {{ selectedPegawai.golongan }}</p>
                    <div class="pt-1">
                        <Tag :value="selectedPegawai.status" :severity="statusColor(selectedPegawai.status)" />
                    </div>
                </div>

                <!-- --- TAMBAHAN DI SINI: TAMPILAN TTD PADA DETAIL --- -->
                <div class="  border-surface-200 pt-2 flex flex-col gap-2">
                    <label class="text-sm font-semibold text-surface-700">Tanda Tangan:</label>

                    <!-- Jika pegawai sudah memiliki TTD -->
                    <div v-if="selectedPegawai.ttd"
                        class="p-3 border border-surface-200 rounded-xl bg-surface-50 flex justify-center max-w-[220px]">
                        <img :src="selectedPegawai.ttd" alt="Tanda Tangan" class="h-20 object-contain" />
                    </div>

                    <!-- Jika pegawai belum memiliki TTD -->
                    <div v-else
                        class="text-xs italic text-surface-400 p-3 border border-dashed border-surface-200 rounded-xl bg-surface-50">
                        Belum mengunggah tanda tangan digital.
                    </div>
                </div>
            </div>
        </Dialog>

        <Dialog v-model:visible="dialog" :header="editMode ? 'Edit Pegawai' : 'Tambah Pegawai'" modal
            :style="{ width: '95vw', maxWidth: '520px' }" :breakpoints="{ '768px': '95vw' }" class="rounded-2xl">
            <div class="flex flex-col gap-5 mt-2">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-surface-700">Nama Pegawai</label>
                    <InputText v-model="form.nama" placeholder="Masukkan nama pegawai" class="w-full" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-surface-700">NIP</label>
                    <InputText v-model="form.nip" placeholder="Masukkan NIP" class="w-full" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-surface-700">Jabatan</label>
                    <InputText v-model="form.jabatan" placeholder="Masukkan jabatan" class="w-full" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-surface-700">Pangkat</label>
                        <InputText v-model="form.pangkat" placeholder="Contoh: Penata" class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-surface-700">Golongan</label>
                        <InputText v-model="form.golongan" placeholder="Contoh: III/a" class="w-full" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-surface-700">Status Pegawai</label>
                    <Select v-model="form.status" :options="[
                        { label: 'Aktif', value: 'aktif' },
                        { label: 'Tidak Aktif', value: 'tidak aktif' },
                    ]" optionLabel="label" optionValue="value" placeholder="Pilih status pegawai" class="w-full" />
                </div>


                <!-- --- TAMBAHAN DI SINI: UPLOAD TANDA TANGAN --- -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-surface-700">Tanda Tangan Digital (Gambar)</label>

                    <!-- Input File Tersembunyi tapi diakali pakai Button agar Estetik -->
                    <div
                        class="flex items-center gap-4 p-3 border border-dashed rounded-xl bg-surface-50 border-surface-300">
                        <input type="file" ref="fileInput" accept="image/*" class="hidden" @change="handleTtdUpload" />
                        <Button label="Pilih Gambar TTD" icon="pi pi-upload" severity="info" text raised size="small"
                            @click="$refs.fileInput.click()" />

                        <!-- Indikator teks jika file sudah terpilih/ada -->
                        <span v-if="form.ttd" class="text-xs text-green-600 font-medium flex items-center gap-1">
                            <i class="pi pi-check-circle"></i> TTD Tersimpan
                        </span>
                        <span v-else class="text-xs text-surface-500">Belum ada gambar ttd</span>
                    </div>

                    <!-- Preview Gambar Tanda Tangan jika ada nilainya -->
                    <div v-if="form.ttd"
                        class="mt-2 p-2 border border-surface-200 rounded-xl bg-white flex justify-center relative group max-w-[200px]">
                        <img :src="form.ttd" alt="Preview TTD" class="h-20 object-contain" />
                        <Button icon="pi pi-trash" severity="danger" rounded text
                            class="absolute -top-2 -right-2 bg-white shadow-md" size="small" @click="form.ttd = null" />
                    </div>
                </div>


            </div>

            <template #footer>
                <div class="flex justify-end gap-2 w-full pt-4">
                    <Button label="Batal" icon="pi pi-times" severity="secondary" text @click="dialog = false" />
                    <Button :label="editMode ? 'Update Data' : 'Simpan Data'" icon="pi pi-check" severity="success"
                        @click="save" />
                </div>
            </template>
        </Dialog>
    </div>
</template>