<template>
    <div class="p-3 md:p-4">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div class="flex items-center gap-4">
                <!-- Logo SIPANDA -->
                <img :src="sipanda" alt="Logo SIPANDA"
                    class="w-12 h-12 object-contain filter drop-shadow-sm select-none" />

                <!-- Teks Judul & Deskripsi -->
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">
                        Permohonan Barang
                    </h2>
                    <p class="text-sm font-medium text-slate-500 mt-1.5">
                        Persetujuan dan pengelolaan berkas pengajuan barang persediaan
                    </p>
                </div>
            </div>
        </div>

        <!-- FILTER -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <!-- SEARCH -->
            <span class="relative w-full">


                <InputText v-model="search" placeholder="search" class=" w-full pl-0" />
            </span>

            <Select v-model="statusFilter" :options="[
                { label: 'Menunggu Persetujuan Kaur', value: 'pending' },
                { label: 'Barang Sedang Disiapkan', value: 'approved_kaur' }, // Ubah label
                { label: 'Barang Ready', value: 'ready' },
                { label: 'Barang Diambil', value: 'completed' },
                { label: 'Ditolak', value: 'rejected' }
            ]" optionLabel="label" optionValue="value" placeholder="Filter Status" showClear class="w-full md:w-56" />

        </div>
        <p class="text-sm text-gray-500 mb-3">
            Total: {{ filteredRequests.length }} permohonan
        </p>

        <!-- TABLE -->
        <div class="overflow-x-auto">


            <DataTable :value="filteredRequests" :loading="store.loading" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20]" stripedRows class="text-sm">
                <Column field="employee_name" header="Pegawai" />

                <Column field="division" header="Jabatan" />

                <!-- Tampilkan Ringkasan Barang -->
                <Column header="Barang">
                    <template #body="{ data }">
                        <div class="text-xs">
                            {{ data.total_items }} jenis barang
                            <Button label="Lihat" text size="small" @click="openDetail(data)" />
                        </div>
                    </template>
                </Column>


                <!-- Status -->
                <!-- Status -->
                <Column header="Status">
                    <template #body="{ data }">
                        <!-- Mengubah flex-col menjadi flex-row dan menyelaraskan posisi ke tengah -->
                        <div class="flex flex-row items-center gap-2">
                            <Tag :value="getStatusLabel(data.status)" :severity="statusColor(data.status)" />

                            <!-- Tombol Lihat hanya muncul otomatis jika status sudah completed -->
                            <div v-if="data.status === 'completed'" class="text-xs">
                                <Button label="Info" text size="small" class="p-0 text-primary-600 font-semibold"
                                    @click.stop="openBonInfo(data)" />
                            </div>
                        </div>
                    </template>
                </Column>



                <Column header="Aksi">
                    <template #body="{ data }">

                        <div class="flex items-center gap-1">
                            <!-- 1. Approval Kaur (Perlengkapan) -->
                            <Button v-if="data.status === 'pending' && (auth.isPerlengkapan || auth.isAdmin)"
                                icon="pi pi-check-circle" severity="warning" size="small"
                                @click="openApproveGroup(data, 'perlengkapan')" v-tooltip.top="'Approve Kaur'" />

                            <!-- 2. Staf Tahap 1: Konfirmasi Barang Siap / Ready (Kurangi Stok) -->
                            <Button v-if="data.status === 'approved_kaur' && (auth.isStafPerlengkapan || auth.isAdmin)"
                                icon="pi pi-box" severity="success" size="small"
                                @click="openApproveGroup(data, 'staf_ready')" v-tooltip.top="'Barang Siap / Ready'" />

                            <!-- 3. Staf Tahap 2: Konfirmasi Barang Sudah Diambil (Selesai) -->
                            <Button v-if="data.status === 'ready' && (auth.isStafPerlengkapan || auth.isAdmin)"
                                icon="pi pi-thumbs-up" severity="info" size="small"
                                @click="openApproveGroup(data, 'staf_complete')"
                                v-tooltip.top="'Barang Sudah Diambil'" />

                            <!-- Tombol Tolak (Hanya untuk berkas yang masih pending di Kaur) -->
                            <Button v-if="data.status === 'pending' && (auth.isPerlengkapan || auth.isAdmin)"
                                icon="pi pi-times" severity="danger" size="small" @click="rejectGroup(data)"
                                v-tooltip="'Tolak Permohonan'" />

                            <!-- Tombol Menu Ellipsis -->
                            <Menu :ref="el => menus[data.employee_name + data.created_at] = el"
                                :model="getMenuItems(data)" popup />
                            <Button icon="pi pi-ellipsis-v" size="small" severity="secondary" text
                                @click="toggleMenu($event, data.employee_name + data.created_at)" />
                        </div>

                    </template>
                </Column>

            </DataTable>



        </div>




        <Dialog v-model:visible="approvalDialog" modal header="Approval Permohonan" :style="{ width: '500px' }">
            <div v-if="approvalData" class="flex flex-col gap-4">
                <p class="font-bold">Pegawai: {{ approvalData.employee_name }}</p>

                <!-- 1. JIKA TIPE NYA STAF COMPLETE: Hanya Tampilkan Teks Konfirmasi Simple -->
                <div v-if="approvalType === 'staf_complete'"
                    class="text-center py-4 border rounded-lg bg-emerald-50 text-emerald-800 border-emerald-200">
                    <i class="pi pi-info-circle text-2xl mb-2 text-emerald-600 block"></i>
                    <p class="font-medium text-base">Konfirmasi bahwa semua barang di permohonan ini sudah diambil oleh
                        pegawai
                        yang bersangkutan?</p>
                </div>

                <!-- 2. JIKA TIPE LAIN (perlengkapan / staf_ready): Tampilkan Tabel Input Angka Seperti Biasa -->
                <div v-else class="flex flex-col gap-4">
                    <!-- Header Info -->
                    <div class="text-xs text-slate-500 flex justify-between px-2">
                        <span>Barang</span>
                        <div class="flex gap-4">
                            <span>Stok</span>
                            <span>Minta</span>
                            <span>Setujui</span>
                        </div>
                    </div>

                    <!-- List Item dalam Request -->
                    <div class="border rounded-lg p-2 bg-slate-50">
                        <div v-for="(item, index) in approvalData.items" :key="item.id"
                            class="flex justify-between items-center py-3 border-b last:border-0">

                            <!-- Nama Barang -->
                            <span class="text-sm font-medium">{{ item.item_name }}</span>

                            <div class="flex items-center gap-3">
                                <!-- Stok Saat Ini -->
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] text-gray-400">Stok</span>
                                    <span class="text-sm font-bold"
                                        :class="item.item?.stock < item.stock_requested ? 'text-red-500' : 'text-slate-700'">
                                        {{ item.item?.stock ?? 0 }}
                                    </span>
                                </div>

                                <!-- Jumlah Minta -->
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] text-gray-400">Minta</span>
                                    <span class="text-sm font-semibold text-slate-700">{{ item.stock_requested }}</span>
                                </div>

                                <!-- Input Approval -->
                                <InputNumber v-model="approvalData.items[index].approved_qty" :min="0"
                                    :useGrouping="false" class="w-16" inputClass="w-16 text-center p-2"
                                    :readonly="approvalType === 'staf_ready'"
                                    :class="{ 'opacity-50': approvalType === 'staf_ready' }" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit Dinamis Mengikuti Tipe -->
                <Button :label="approvalType === 'staf_complete' ? 'Konfirmasi Sudah Diambil' : 'Setujui Semua'"
                    :severity="approvalType === 'staf_complete' ? 'info' : 'success'" class="w-full"
                    @click="submitApproval" />
            </div>
        </Dialog>




        <Dialog v-model:visible="timelineDialog" modal header="Timeline Permohonan"
            :style="{ width: '95vw', maxWidth: '500px' }">

            <div v-if="selectedRequest" class="space-y-4">

                <div class="flex gap-3 items-start">
                    <i class="pi pi-send text-blue-500 mt-1"></i>
                    <div>
                        <p class="font-semibold">Permohonan Dibuat</p>
                        <p>{{ selectedRequest.formatted_requested_at }}</p>
                    </div>
                </div>

                <div v-if="selectedRequest.approved_kaur_at" class="flex gap-3 items-start">
                    <i class="pi pi-check-circle text-yellow-500 mt-1"></i>
                    <div>
                        <p class="font-semibold">Disetujui Kaur</p>
                        <p>{{ selectedRequest.formatted_approved_kaur_at }}</p>
                        <small>Oleh: {{ selectedRequest.approved_kaur_by }}</small>
                    </div>
                </div>

                <div v-if="selectedRequest.confirmed_by_staff_at" class="flex gap-3 items-start">
                    <i class="pi pi-check-circle text-green-500 mt-1"></i>
                    <div>
                        <p class="font-semibold">Barang sedang disiapkan</p>
                        <p>{{ selectedRequest.formatted_confirmed_by_staff_at }}</p>
                        <small>Oleh: {{ selectedRequest.confirmed_by_staff_by }}</small>
                    </div>
                </div>

                <div v-if="selectedRequest.formatted_completed_at" class="flex gap-3 items-start">
                    <i class="pi pi-box text-green-600 mt-1"></i>
                    <div>
                        <p class="font-semibold">Barang Dikeluarkan</p>
                        <p>{{ selectedRequest.formatted_completed_at }}</p>
                    </div>
                </div>

                <div v-if="selectedRequest.rejected_at" class="flex gap-3 items-start">
                    <i class="pi pi-times-circle text-red-500 mt-1"></i>
                    <div>
                        <p class="font-semibold">Permohonan Ditolak</p>
                        <p>{{ selectedRequest.formatted_rejected_at }}</p>
                        <small>Oleh: {{ selectedRequest.rejected_by }}</small>
                    </div>
                </div>

            </div>
        </Dialog>

        <Dialog v-model:visible="detailDialog" modal :style="{ width: '500px' }">

            <template #header>
                <div class="w-full text-center font-semibold text-lg">
                    Detail Barang
                </div>
            </template>
            <div v-if="selectedRequest" class="flex flex-col gap-3">
                <!-- Informasi Header -->

                <div class="flex justify-between items-start gap-4 mb-4 border-b pb-3">
                    <!-- Kolom Kiri: Informasi Teks -->
                    <div>
                        <p class="text-sm text-slate-500">Pegawai: <span class="text-slate-800 font-medium">{{
                            selectedRequest.employee_name }}</span></p>
                        <p class="text-sm text-slate-500 mt-1">Jabatan: <span class="text-slate-800 font-medium">{{
                            selectedRequest.division }}</span></p>
                    </div>

                    <!-- Kolom Kanan: Tanda Tangan Pemohon -->
                    <div v-if="selectedRequest.signature" class="flex flex-col items-center text-center">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-1">Ttd Pemohon
                        </p>
                        <div class="border border-slate-200 rounded p-1 bg-white shadow-sm">
                            <img :src="selectedRequest.signature" alt="Tanda Tangan Pemohon"
                                class="h-16 w-32 object-contain block" />
                        </div>
                    </div>
                </div>

                <!-- Daftar Barang -->
                <div class="border rounded-lg overflow-hidden border-slate-200">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-2 text-left">Barang</th>
                                <th class="p-2 text-center">Diajukan</th>
                                <th class="p-2 text-center">Disetujui</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in selectedRequest.items" :key="item.id" class="border-b last:border-0">
                                <td class="p-2">{{ item.item_name }}</td>
                                <!-- Jumlah Awal -->
                                <td class="p-2 text-center">{{ item.stock_requested }}</td>
                                <!-- Jumlah Akhir (mengambil dari adjusted atau final) -->
                                <td class="p-2 text-center font-bold text-green-600">
                                    {{ item.final_approved_stock ?? item.adjusted_stock_requested ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Dialog>


        <!-- Dialog Pilihan Petugas BMN Sebelum Cetak -->
        <Dialog v-model:visible="bmnModalVisible" modal :style="{ width: '90vw', maxWidth: '400px' }">

            <template #header>
                <div class="w-full text-center font-semibold text-lg">
                    Pilih Pengelola BMN
                </div>
            </template>
            <div class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-surface-700">Nama Pengelola BMN</label>
                    <Select v-model="selectedBmnId" :options="listPengelolaBmn" optionLabel="nama" optionValue="id"
                        placeholder="Pilih petugas BMN..." class="w-full" />
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2 w-full pt-2">
                    <Button label="Batal" severity="secondary" text @click="bmnModalVisible = false" />
                    <Button label="Lanjutkan Cetak" icon="pi pi-file-pdf" severity="success" :disabled="!selectedBmnId"
                        @click="handleConfirmPrint" />
                </div>
            </template>
        </Dialog>


        <!-- Dialog Info Nomor & Tanggal Bon -->
        <Dialog v-model:visible="bonDialogVisible" modal :style="{ width: '90vw', maxWidth: '380px' }">

            <template #header>
                <div class="w-full text-center font-semibold text-lg">
                    Informasi Bon Distribusi
                </div>
            </template>
            <div v-if="selectedBonData"
                class="flex flex-col gap-4 mt-2 bg-surface-50 p-4 rounded-xl border border-surface-200">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-surface-500 uppercase tracking-wider">Nomor Bon</span>
                    <span
                        class="text-base  text-surface-800 font-mono bg-white px-3 py-2 border rounded-lg border-surface-300">
                        {{ selectedBonData.bon_number_formatted || 'Belum ada nomor' }}
                    </span>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-surface-500 uppercase tracking-wider">Tanggal Pengeluaran
                        Bon</span>
                    <div
                        class="flex items-center gap-2 text-surface-700 bg-white px-3 py-2 border rounded-lg border-surface-300">
                        <i class="pi pi-calendar text-surface-500"></i>
                        <span class="text-sm font-medium">{{ formatIndoDate(selectedBonData.completed_at) }}</span>
                    </div>
                </div>
            </div>

        </Dialog>



        <ConfirmDialog :pt="{
            header: {
                class: 'justify-center text-center'
            },
            title: {
                class: 'w-full text-center'
            },
            message: {
                class: 'text-center'
            },
            footer: {
                class: 'justify-center'
            }
        }" />

    </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from "vue";
import { useRequestStore } from "@/stores/request";
import { usePegawaiStore } from '@/stores/pegawai'; // Sesuaikan nama store Anda
import InputNumber from 'primevue/inputnumber';
import { useConfirm } from "primevue/useconfirm";
import { useAuthStore } from "@/stores/auth";
import { useRoute, useRouter } from 'vue-router';
import { jsPDF } from "jspdf";
import Menu from 'primevue/menu'
import sipanda from '@/assets/sipanda.png'

const auth = useAuthStore();
const pegawaiStore = usePegawaiStore();

const confirm = useConfirm();
const route = useRoute();
const router = useRouter();
const detailDialog = ref(false);
const selectedRequest = ref(null);
const currentActiveData = ref(null);
const bmnModalVisible = ref(false);
const selectedBmnId = ref(null);




const openDetail = (data) => {
    selectedRequest.value = data;
    detailDialog.value = true;
};

const timelineDialog = ref(false);


const store = useRequestStore();


onMounted(async () => {
    await store.fetchRequests();
    await pegawaiStore.fetchPegawais();

});


const approvalDialog = ref(false);
const approvalData = ref(null);


const approvalType = ref(""); // kaur / kasi
const search = ref("");

const statusFilter = computed({
    get: () => route.query.status || null,
    set: (val) => {
        router.push({
            query: { ...route.query, status: val || undefined }
        });
    }
});


const filteredRequests = computed(() => {
    if (!store.requests || store.requests.length === 0) return [];

    // Filter berdasarkan search dan status terlebih dahulu
    const filtered = store.requests.filter((item) => {
        const keyword = search.value.toLowerCase();
        const matchesSearch = item.employee_name?.toLowerCase().includes(keyword) ||
            item.item_name?.toLowerCase().includes(keyword);
        const matchesStatus = !statusFilter.value || item.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });

    // 2. Kelompokkan berdasarkan request_code
    const groups = {};
    filtered.forEach(item => {
        let key = "";

        if (item.request_code) {
            // Utama: Gunakan request_code buatan backend (Pasti Akurat & Permanen)
            key = item.request_code;
        } else {
            // Cadangan: Untuk data-data lama yang request_code-nya masih NULL di DB
            const minuteOnly = item.created_at ? item.created_at.substring(0, 16) : 'no-date';
            key = `${item.employee_name}_${minuteOnly}`;
        }

        if (!groups[key]) {
            groups[key] = {
                ...item,
                items: [],
                total_items: 0
            };
        }
        groups[key].items.push(item);
        groups[key].total_items += 1;
    });

    return Object.values(groups);
});



const submitApproval = async () => {
    const payload = {
        items: approvalData.value.items.map(item => ({
            id: item.id,
            qty: item.approved_qty
        })),
        type: approvalType.value
    };

    console.log("Mengirim Payload:", payload); // CEK DI CONSOLE BROWSER

    if (!payload.type) {
        alert("Error: Tipe approval tidak ditemukan!");
        return;
    }

    try {
        await store.approveBulk({
            items: approvalData.value.items.map(item => ({
                id: item.id,
                qty: item.approved_qty // Kirim nilai dari input
            })),
            type: approvalType.value
        });
        approvalDialog.value = false;
        await store.fetchRequests();
    } catch (error) {
        console.error("Error Backend:", error.response?.data);
        alert("Gagal: " + (error.response?.data?.message || "Terjadi kesalahan"));
    }
};





const menus = ref({});

const toggleMenu = (event, id) => {
    menus.value[id].toggle(event);
};



const listPengelolaBmn = computed(() => {
    return pegawaiStore.pegawais?.filter(emp =>
        emp.jabatan?.toLowerCase().includes('pengelola barang milik negara') && emp.status === 'aktif'
    ) || [];
});
const getMenuItems = (data) => {
    return [
        {
            label: 'Timeline',
            icon: 'pi pi-clock',
            command: () => openTimeline(data) // Mengirim data grup ke timeline
        },
        {
            label: 'Cetak Bon Distribusi',
            icon: 'pi pi-file-pdf',
            command: () => {
                currentActiveData.value = data; // Simpan data item grup ke state
                selectedBmnId.value = null;     // Reset pilihan dropdown sebelumnya
                bmnModalVisible.value = true;   // Buka modal dropdown
            }
        },
        {
            separator: true
        },
        {
            label: 'Hapus Semua Item', // Label disesuaikan karena ini adalah group
            icon: 'pi pi-trash',
            command: () => deleteGroup(data)
        }
    ];
};

// --- 2. FUNGSI KONFIRMASI SAAT KLIK LANJUTKAN ---
const handleConfirmPrint = () => {
    bmnModalVisible.value = false; // Tutup modal dulu

    // Panggil fungsi pembuat PDF asli Anda dengan membawa data permohonan
    generateDistributionPdf(currentActiveData.value);
};


const openApproveGroup = (groupData, type) => {
    approvalType.value = type;

    approvalData.value = {
        ...groupData,
        items: groupData.items.map(item => ({
            ...item,

            approved_qty: item.final_approved_stock ?? item.adjusted_stock_requested ?? item.stock_requested
        }))
    };


    if (type === 'staf_complete') {

        approvalDialog.value = true;
    } else {
        approvalDialog.value = true;
    }
};

/* STATUS COLOR (PrimeVue Badge Severities) */
const statusColor = (status) => {
    if (status === "pending") return "warn";             // Kuning
    if (status === "approved_kaur") return "secondary";    // Abu-abu (Diproses)
    if (status === "ready") return "info";                // Biru (Siap Diambil)
    if (status === "completed") return "success";         // Hijau (Selesai/Sudah Diambil)
    if (status === "rejected") return "danger";           // Merah
    return "secondary";
};

/* MAPPING LABEL STATUS */
const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Menunggu Persetujuan Kaur',
        'approved_kaur': 'Barang Sedang Disiapkan Staf',
        'ready': 'Barang Ready',
        'completed': 'Barang Diambil',
        'rejected': 'Ditolak'
    };
    return labels[status] || status;
};

const rejectGroup = async (groupData) => {
    // groupData sekarang adalah objek grup (karena kita kirim 'data' dari template)
    console.log("Objek yang diterima:", groupData);

    confirm.require({
        message: 'Yakin ingin menolak seluruh permohonan ini?',
        header: 'Konfirmasi',
        icon: 'pi pi-exclamation-triangle',
        accept: async () => {
            try {
                // Pastikan 'items' ada di dalam groupData (hasil grouping Anda)
                if (groupData.items && Array.isArray(groupData.items)) {
                    const ids = groupData.items.map(item => item.id);

                    await store.rejectBulk(ids); // Menggunakan endpoint bulk
                    await store.fetchRequests();
                } else {
                    console.error("Data items tidak ditemukan:", groupData);
                }
            } catch (error) {
                console.error("Error saat reject:", error);
            }
        }
    });
};
const deleteGroup = (groupData) => {
    confirm.require({
        message: 'Hapus seluruh request untuk ' + groupData.employee_name + '?',
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-trash',
        accept: async () => {

            for (const item of groupData.items) {
                await store.deleteRequest(item.id);
            }
            await store.fetchRequests();
        }
    });
};

const openTimeline = (data) => {
    selectedRequest.value = data;
    timelineDialog.value = true;
};


// --- STATE DIALOG INFO BON ---
const bonDialogVisible = ref(false);
const selectedBonData = ref(null);

// --- FUNGSI MEMBUKA MODAL INFO BON ---
const openBonInfo = (data) => {
    selectedBonData.value = data;
    bonDialogVisible.value = true;
};

// --- HELPER FORMAT TANGGAL DI MODAL POPUP ---
const formatIndoDate = (rawDate) => {
    if (!rawDate) return "...............................";
    const dateObj = new Date(rawDate);
    const day = dateObj.getDate();
    const months = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];
    return `${day} ${months[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
};



const generateDistributionPdf = (data) => {

    // TAMBAHKAN LINE INI UNTUK DEBUGGING
    console.log("=== ISI DATA YANG AKAN DICETAK ===");
    console.log(data);

    // 1. Cari data Kasubsi berdasarkan kata kunci jabatan dari Pinia Store
    const kasubsi = pegawaiStore.pegawais?.find(emp =>
        emp.jabatan?.toLowerCase().includes('kepala subseksi keuangan dan perlengkapan') ||
        emp.jabatan?.toLowerCase().includes('kepala subseksi keuangan')
    ) || { nama: "", nip: "", ttd: null };

    // 2. KHUSUS PENGELOLA BMN: Ambil langsung dari hasil pilihan dropdown user tadi!
    const pengelolaBmn = pegawaiStore.pegawais?.find(emp => emp.id === selectedBmnId.value)
        || { nama: "..................................", nip: "..................................", ttd: null };

    // 3. KHUSUS PEMOHON: Ambil Nama dan TTD Langsung dari Data Request Item
    const pemohonNama = data.employee_name;
    const pemohonTtd = data.signature; // Memakai data ttd dari signature pad di database item_requests

    // Untuk NIP Pemohon, kita bantu cari ke Pinia Store berdasarkan kecocokan nama
    const pemohonPegawai = pegawaiStore.pegawais?.find(emp =>
        emp.nama?.toLowerCase() === pemohonNama?.toLowerCase()
    );
    const pemohonNip = pemohonPegawai ? pemohonPegawai.nip : "";


    const rawDate = data.completed_at;
    let tanggalFormatted = "...............................";

    if (rawDate) {
        const dateObj = new Date(rawDate);

        // Ambil tanggal (dd)
        const day = dateObj.getDate();

        // Array Nama Bulan Indonesia (mmmm)
        const months = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];
        const monthName = months[dateObj.getMonth()];

        // Ambil Tahun (yyyy)
        const year = dateObj.getFullYear();

        // Gabungkan menjadi format: dd mmmm yyyy
        tanggalFormatted = `${day} ${monthName} ${year}`;
    }



    const doc = new jsPDF({
        orientation: 'l',
        unit: 'mm',
        format: 'a5' // Ukuran Nota/Bon (210 x 148 mm)
    });


    const printHeader = (pageNumber) => {
        doc.setFont("helvetica", "bold");
        doc.setFontSize(7);
        doc.text("KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN RI", 52.5, 10, { align: "center" });
        doc.text("KANTOR WILAYAH DKI JAKARTA", 52.5, 13.5, { align: "center" });

        doc.setFontSize(7.5);
        doc.text("RUMAH TAHANAN NEGARA KELAS I PONDOK BAMBU", 52.5, 18, { align: "center" });
        doc.setLineWidth(0.2);
        doc.line(14.5, 19.2, 90.5, 19.2);

        doc.setFont("helvetica", "italic");
        doc.setFontSize(6);
        doc.text("Jl. Pahlawan Revolusi, Pondok Bambu - Jakarta Timur", 52.5, 22, { align: "center" });

        // Judul Nota Tengah
        doc.setFont("helvetica", "bold");
        doc.setFontSize(11);
        doc.text("BON PENDISTRIBUSIAN BARANG PERSEDIAAN", 105, 30, { align: "center" });

        doc.setLineWidth(0.2);
        doc.line(45, 31.2, 165, 31.2);

        doc.setFont("helvetica", "normal");
        doc.setFontSize(8);

        // --- PERBAIKAN DI SINI ---
        // Gunakan parameter 'data' dari scope generateDistributionPdf, bukan selectedRequest
        const nomorBon = data?.bon_number_formatted || '...........................................';
        doc.text(`No : ${nomorBon}`, 105, 35, { align: "center" });

        doc.setFontSize(8);
        doc.text(`(Nota Halaman ${pageNumber})`, 195, 35, { align: "right" });
    };
    // --- HELPER HEADER TABEL ---
    const printTableHeader = (y) => {
        doc.setFillColor(241, 245, 249);
        doc.rect(15, y, 180, 6, "F");
        doc.rect(15, y, 180, 6, "S");

        doc.setFont("helvetica", "bold");
        doc.setFontSize(8);
        doc.text("NO", 20, y + 4, { align: "center" });
        doc.text("NAMA BARANG", 32, y + 4);
        doc.text("BANYAKNYA", 130, y + 4, { align: "center" });
        doc.text("KETERANGAN", 165, y + 4, { align: "center" });

        doc.line(26, y, 26, y + 6);
        doc.line(110, y, 110, y + 6);
        doc.line(150, y, 150, y + 6);
    };

    // --- HELPER AREA TANDA TANGAN ---
    const printSignatures = (y) => {
        let currentY = y + 4; // Beri spasi sedikit dari tabel

        // Teks Tanggal & Tanda Tangan
        doc.setFontSize(8.5);
        doc.setFont("helvetica", "normal");

        // Jakarta, dd mmmm yyyy otomatis sesuai tanggal pengeluaran barang
        doc.text(`Jakarta, ${tanggalFormatted}`, 184, currentY, { align: "right" });
        currentY += 6;

        // --- AREA TANDA TANGAN ---

        // Kolom 3: Kasubsi (Kiri)
        let col3X = 40;
        doc.setFont("helvetica", "normal").text("Mengetahui/ Menyetujui,", col3X, currentY - 4, { align: "center" });
        doc.setFont("helvetica", "bold").text("Kepala Sub Seksi", col3X, currentY, { align: "center" });
        doc.setFont("helvetica", "bold").text("Keuangan dan Perlengkapan,", col3X, currentY + 3.5, { align: "center" });

        // Cetak Gambar TTD Kasubsi jika ada (posisi X disesuaikan agar pas di tengah kolom)
        if (kasubsi.ttd) {
            doc.addImage(kasubsi.ttd, "PNG", col3X - 15, currentY + 5, 30, 13, undefined, 'FAST');
        }

        doc.setFont("helvetica", "bold").text(kasubsi.nama, col3X, currentY + 19.5, { align: "center" });
        doc.line(col3X - 22, currentY + 20, col3X + 22, currentY + 20);
        doc.setFont("helvetica", "normal").text("NIP. " + kasubsi.nip, col3X - 22, currentY + 24);


        // Kolom 2: Pengelola BMN (Tengah)
        let col2X = 105;
        doc.setFont("helvetica", "normal").text("Pengelola BMN,", col2X, currentY + 3.5, { align: "center" });

        // Cetak Gambar TTD Pengelola BMN jika ada
        if (pengelolaBmn.ttd) {
            doc.addImage(pengelolaBmn.ttd, "PNG", col2X - 15, currentY + 5, 30, 13, undefined, 'FAST');
        }

        doc.setFont("helvetica", "bold").text(pengelolaBmn.nama, col2X, currentY + 19.5, { align: "center" });
        doc.line(col2X - 22, currentY + 20, col2X + 22, currentY + 20);
        doc.setFont("helvetica", "normal").text("NIP. " + pengelolaBmn.nip, col2X - 22, currentY + 24);

        // Kolom 1: Pemohon (Kanan)
        let col1X = 170;
        doc.setFont("helvetica", "normal").text("Pemohon,", col1X, currentY + 3.5, { align: "center" });

        // Cetak Gambar TTD Pemohon dari kolom 'signature' (tabel item_requests) jika ada
        if (pemohonTtd) {
            doc.addImage(pemohonTtd, "PNG", col1X - 15, currentY + 5, 30, 13, undefined, 'FAST');
        }

        doc.setFont("helvetica", "bold").text(pemohonNama, col1X, currentY + 19.5, { align: "center" });
        doc.line(col1X - 22, currentY + 20, col1X + 22, currentY + 20);
        doc.setFont("helvetica", "normal").text("NIP. " + pemohonNip, col1X - 22, currentY + 24);
    };

    // --- MULAI CETAK HALAMAN 1 ---
    let page = 1;
    let itemsInCurrentPage = 0;
    printHeader(page);

    let currentY = 41;
    printTableHeader(currentY);
    currentY += 6;

    // --- LOOP DATA BARANG ---
    data.items.forEach((item, index) => {
        // JIKA SUDAH MAKSIMAL 10 BARANG DI NOTA AKTIF
        if (itemsInCurrentPage >= 10) {
            // 1. Cetak tanda tangan dulu di nota yang sekarang sebelum pindah halaman
            printSignatures(currentY);

            // 2. Buat halaman nota baru
            doc.addPage();
            page++;
            itemsInCurrentPage = 0; // Reset hitungan barang ke 0

            // 3. Setup ulang header di halaman baru
            printHeader(page);
            currentY = 41;
            printTableHeader(currentY);
            currentY += 6;
        }

        // Gambar baris tabel barang
        doc.setFont("helvetica", "normal");
        doc.setFontSize(8);
        doc.rect(15, currentY, 180, 6, "S");

        // Nomor urut di-reset mulai dari 1 lagi di setiap nota baru
        const displayIndex = (itemsInCurrentPage + 1).toString();
        doc.text(displayIndex, 20, currentY + 4, { align: "center" });

        doc.text(item.item_name || '-', 32, currentY + 4);

        const qtyText = `${item.final_approved_stock ?? item.stock_requested ?? 0}`;
        doc.text(qtyText, 130, currentY + 4, { align: "center" });
        doc.text(item.notes || '', 165, currentY + 4, { align: "center" });

        // Garis kolom vertikal
        doc.line(26, currentY, 26, currentY + 6);
        doc.line(110, currentY, 110, currentY + 6);
        doc.line(150, currentY, 150, currentY + 6);

        currentY += 6;
        itemsInCurrentPage++;
    });
    if (itemsInCurrentPage < 10) {
        const sisaBaris = 10 - itemsInCurrentPage;

        currentY += (sisaBaris * 3);


    }

    printSignatures(currentY);

    // Download berkas PDF
    doc.save(`Bon_Distribusi_${data.employee_name}.pdf`);
};


</script>