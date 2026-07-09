<template>
    <div class="p-3 md:p-4">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <h2 class="text-2xl font-bold">
                Permohonan Barang
            </h2>
        </div>

        <!-- FILTER -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <!-- SEARCH -->
            <span class="relative w-full">
                <!-- <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /> -->

                <InputText v-model="search" placeholder="search" class=" w-full pl-0" />
            </span>

            <Select v-model="statusFilter" :options="[
                { label: 'Menunggu Persetujuan Kaur', value: 'pending' },
                { label: 'Menunggu Konfirmasi Staf', value: 'approved_kaur' }, // Ubah label
                { label: 'Selesai', value: 'completed' },
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
                <Column header="Status">
                    <template #body="{ data }">
                        <Tag :value="getStatusLabel(data.status)" :severity="statusColor(data.status)" />
                    </template>
                </Column>

                <!-- Tombol Aksi -->
                <Column header="Aksi">
                    <template #body="{ data }">
                        <!-- Approval Kaur (Perlengkapan) -->
                        <Button v-if="data.status === 'pending' && (auth.isPerlengkapan || auth.isAdmin)"
                            icon="pi pi-check-circle" severity="warning" size="small"
                            @click="openApproveGroup(data, 'perlengkapan')" v-tooltip.top="'Approve Kaur'" />

                        <!-- Approval Staf -->
                        <Button v-if="data.status === 'approved_kaur' && (auth.isStafPerlengkapan || auth.isAdmin)"
                            icon="pi pi-check-circle" severity="success" size="small"
                            @click="openApproveGroup(data, 'staf_perlengkapan')" v-tooltip.top="'Approve Staf'" />

                        <!-- Tombol Tolak (Hanya muncul untuk Perlengkapan DAN status pending) -->
                        <Button
                            v-if="data.status === 'pending' && approvalType !== 'staf_perlengkapan' && (!auth.isStafPerlengkapan)"
                            icon="pi pi-times" severity="danger" size="small" @click="rejectGroup(data)"
                            v-tooltip="'Tolak Permohonan'" />



                        <!-- Tombol Menu Ellipsis -->
                        <Menu :ref="el => menus[data.employee_name + data.created_at] = el" :model="getMenuItems(data)"
                            popup />
                        <Button icon="pi pi-ellipsis-v" size="small" severity="secondary" text
                            @click="toggleMenu($event, data.employee_name + data.created_at)" />


                    </template>
                </Column>
            </DataTable>



        </div>




        <Dialog v-model:visible="approvalDialog" modal header="Approval Permohonan" :style="{ width: '500px' }">
            <div v-if="approvalData" class="flex flex-col gap-4">
                <p class="font-bold">Pegawai: {{ approvalData.employee_name }}</p>

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


                            <!-- Input Approval dengan desain lebih ramping -->
                            <InputNumber v-model="approvalData.items[index].approved_qty" :min="0" :useGrouping="false"
                                class="w-16" inputClass="w-16 text-center p-2"
                                :readonly="approvalType === 'staf_perlengkapan'"
                                :class="{ 'opacity-50': approvalType === 'staf_perlengkapan' }" />
                        </div>
                    </div>
                </div>

                <Button label="Setujui Semua" severity="success" class="w-full" @click="submitApproval" />
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
                <div class="mb-2">
                    <p class="text-sm text-slate-500">Pegawai: <span class="text-slate-800">{{
                        selectedRequest.employee_name }}</span></p>
                    <p class="text-sm text-slate-500">Jabatan: <span class="text-slate-800">{{
                        selectedRequest.division }}</span></p>
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
import InputNumber from 'primevue/inputnumber';

import { useConfirm } from "primevue/useconfirm";
import { useAuthStore } from "@/stores/auth";
const auth = useAuthStore();
import { useRoute, useRouter } from 'vue-router';

import { jsPDF } from "jspdf";

const confirm = useConfirm();
const route = useRoute();
const router = useRouter();
const detailDialog = ref(false);
const selectedRequest = ref(null);


// Di script
const onInput = (event) => {
    // PrimeVue mengembalikan event dengan properti 'value'
    approvalData.items[index].approved_qty = event.value;
};
const openDetail = (data) => {
    selectedRequest.value = data;
    detailDialog.value = true;
};

const timelineDialog = ref(false);


const store = useRequestStore();


onMounted(async () => {
    await store.fetchRequests();

});


const approvalDialog = ref(false);
const approvalData = ref(null);

const approved_qty = ref(0);
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

    // Grouping
    const groups = {};
    filtered.forEach(item => {
        const key = `${item.employee_name}_${item.created_at}`;
        if (!groups[key]) {
            groups[key] = {
                ...item,
                items: [], // Tempat menampung list item
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

import Menu from 'primevue/menu'
const menus = ref({});

const toggleMenu = (event, id) => {
    menus.value[id].toggle(event);
};

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
            command: () => generateDistributionPdf(data) // Memanggil fungsi cetak PDF
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




const openApproveGroup = (groupData, type) => {
    approvalType.value = type;

    approvalData.value = {
        ...groupData,
        items: groupData.items.map(item => ({
            ...item,
            // Prioritas: gunakan adjusted jika sudah ada, kalau belum pakai requested
            approved_qty: item.adjusted_stock_requested ?? item.stock_requested
        }))
    };

    approvalDialog.value = true;
};

/* STATUS COLOR */
const statusColor = (status) => {
    if (status === "pending") return "warn";
    if (status === "approved_kaur") return "info";
    if (status === "confirmed_by_staff") return "success"; // Jadi hijau
    if (status === "completed") return "success";
    if (status === "rejected") return "danger";
    return "secondary";
};

/* MAPPING LABEL STATUS */
const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Menunggu Persetujuan Kaur',
        'approved_kaur': 'Barang sedang disiapkan',
        'confirmed_by_staff': 'Barang dikeluarkan (Selesai)',
        'completed': 'Selesai',
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
            // Logika hapus: Anda bisa membuat endpoint deleteBulk di store
            // Atau loop hapus per item jika belum ada endpoint bulk
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





// const generateDistributionPdf = (data) => {
//     const doc = new jsPDF({
//         orientation: 'l',
//         unit: 'mm',
//         format: 'a4'
//     });


//     doc.setFont("helvetica", "bold");


//     doc.setFont("helvetica", "bold");

//     doc.setFontSize(7.5);
//     doc.text("KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN RI", 52.5, 12, { align: "center" });
//     doc.text("KANTOR WILAYAH DKI JAKARTA", 52.5, 16, { align: "center" }); // Jarak dari atas dikurangi dari 20 ke 16

//     doc.setFontSize(8);
//     doc.text("RUMAH TAHANAN NEGARA KELAS I PONDOK BAMBU", 52.5, 21, { align: "center" }); // Jarak dari atas dikurangi dari 26 ke 21

//     doc.setLineWidth(0.2);
//     doc.line(14.5, 22.2, 90.5, 22.2); // Posisi garis disesuaikan tepat di bawah teks nama Rutan (Y = 22.2)

//     doc.setFont("helvetica", "italic");
//     doc.setFontSize(6.5);
//     doc.text("Jl. Pahlawan Revolusi, Pondok Bambu - Jakarta Timur", 52.5, 25.5, { align: "center" }); // Jarak alamat dikurangi dari 31 ke 25.5



//     // --- 2. JUDUL & NO SURAT ---
//     doc.setFont("helvetica", "bold");
//     doc.setFontSize(14);
//     doc.text("BON PENDISTRIBUSIAN BARANG PERSEDIAAN", 105, 48, { align: "center" });

//     doc.setLineWidth(0.2);
//     doc.line(45, 49.5, 165, 49.5);

//     doc.setFont("helvetica", "normal");
//     doc.setFontSize(10);

//     doc.text(`No : ...........................................`, 105, 54, { align: "center" });

//     // --- 4. TABEL BARANG ---
//     let currentY = 65;

//     // Header Tabel
//     doc.setFillColor(241, 245, 249); // Warna abu-abu slate-50
//     doc.rect(15, currentY, 180, 8, "F");
//     doc.rect(15, currentY, 180, 8, "S");

//     doc.setFont("helvetica", "bold");
//     doc.setFontSize(10);
//     doc.text("NO", 20, currentY + 5.5, { align: "center" });
//     doc.text("NAMA BARANG", 32, currentY + 5.5);
//     doc.text("BANYAKNYA", 130, currentY + 5.5, { align: "center" });
//     doc.text("KETERANGAN", 165, currentY + 5.5, { align: "center" });

//     // Garis vertikal pembatas header
//     doc.line(26, currentY, 26, currentY + 8);
//     doc.line(110, currentY, 110, currentY + 8);
//     doc.line(150, currentY, 150, currentY + 8);

//     currentY += 8;

//     // Isi Baris Tabel
//     doc.setFont("helvetica", "normal");
//     data.items.forEach((item, index) => {
//         // Gambar kotak baris
//         doc.rect(15, currentY, 180, 8, "S");

//         // Isi teks kolom
//         doc.text((index + 1).toString(), 20, currentY + 5.5, { align: "center" });
//         doc.text(item.item_name || '-', 32, currentY + 5.5);

//         const qtyText = `${item.final_approved_stock ?? item.stock_requested ?? 0} `;
//         doc.text(qtyText, 130, currentY + 5.5, { align: "center" });
//         doc.text(item.notes || '', 165, currentY + 5.5, { align: "center" });

//         // Garis vertikal pembatas kolom isi
//         doc.line(26, currentY, 26, currentY + 8);
//         doc.line(110, currentY, 110, currentY + 8);
//         doc.line(150, currentY, 150, currentY + 8);

//         currentY += 8;
//     });

//     // --- 5. TANGGAL & AREA TANDA TANGAN (3 KOLOM) ---
//     const today = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
//     currentY += 15;

//     doc.text(`Jakarta, ...............................`, 195, currentY, { align: "right" });
//     currentY += 10;


//     // Kolom 3: Kasubsi (Kiri - Berdasarkan koordinat col3X = 40 Anda)
//     let col3X = 40;
//     doc.text("Mengetahui/ Menyetujui,", col3X, currentY - 8, { align: "center" }); // Dinaikkan ke -8 agar tidak menabrak
//     doc.setFont("helvetica", "bold").text("Kepala Sub Seksi", col3X, currentY - 4, { align: "center" }); // Baris pertama jabatan
//     doc.setFont("helvetica", "bold").text("Keuangan dan Perlengkapan,", col3X, currentY, { align: "center" }); // Baris kedua jabatan

//     doc.setFont("helvetica", "bold").text("", col3X, currentY + 25, { align: "center" });
//     doc.line(col3X - 20, currentY + 26, col3X + 20, currentY + 26);
//     doc.setFont("helvetica", "normal").text("NIP. ", col3X - 20, currentY + 31);

//     // Kolom 2: Pengelola BMN (Tengah)
//     let col2X = 105;
//     doc.text("Pengelola BMN,", col2X, currentY, { align: "center" });
//     doc.setFont("helvetica", "bold").text("", col2X, currentY + 25, { align: "center" });
//     doc.line(col2X - 22, currentY + 26, col2X + 22, currentY + 26);
//     doc.setFont("helvetica", "normal").text("NIP.  ", col2X - 20, currentY + 31);

//     // Kolom 1: Pemohon (Kanan - Berdasarkan koordinat col1X = 170 Anda)
//     let col1X = 170;
//     doc.text("Pemohon,", col1X, currentY, { align: "center" });
//     doc.setFont("helvetica", "bold").text(data.employee_name, col1X, currentY + 25, { align: "center" });
//     doc.line(col1X - 20, currentY + 26, col1X + 20, currentY + 26);
//     doc.setFont("helvetica", "normal").text("NIP.", col1X - 20, currentY + 31);

//     // Cetak Dokumen
//     doc.save(`Bon_Distribusi_${data.employee_name}.pdf`);
// };


const generateDistributionPdf = (data) => {
    const doc = new jsPDF({
        orientation: 'l',
        unit: 'mm',
        format: 'a5' // Ukuran Nota/Bon (210 x 148 mm)
    });

    // --- HELPER UNTUK KOP & JUDUL NOTA ---
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
        doc.text(`No : ...........................................`, 105, 35, { align: "center" });

        // Penanda Halaman Nota
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
        doc.text(`Jakarta, ...............................`, 195, currentY, { align: "right" });
        currentY += 6;

        // Kolom 3: Kasubsi (Kiri)
        let col3X = 40;
        doc.text("Mengetahui/ Menyetujui,", col3X, currentY - 4, { align: "center" });
        doc.setFont("helvetica", "bold").text("Kepala Sub Seksi", col3X, currentY, { align: "center" });
        doc.setFont("helvetica", "bold").text("Keuangan dan Perlengkapan,", col3X, currentY + 3.5, { align: "center" });
        doc.line(col3X - 20, currentY + 20, col3X + 20, currentY + 20);
        doc.setFont("helvetica", "normal").text("NIP. ", col3X - 20, currentY + 24);

        // Kolom 2: Pengelola BMN (Tengah)
        let col2X = 105;
        doc.text("Pengelola BMN,", col2X, currentY + 3.5, { align: "center" });
        doc.line(col2X - 22, currentY + 20, col2X + 22, currentY + 20);
        doc.setFont("helvetica", "normal").text("NIP.  ", col2X - 20, currentY + 24);

        // Kolom 1: Pemohon (Kanan)
        let col1X = 170;
        doc.text("Pemohon,", col1X, currentY + 3.5, { align: "center" });
        doc.setFont("helvetica", "bold").text(data.employee_name, col1X, currentY + 19.5, { align: "center" });
        doc.line(col1X - 20, currentY + 20, col1X + 20, currentY + 20);
        doc.setFont("helvetica", "normal").text("NIP.", col1X - 20, currentY + 24);
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

    // --- CETAK TANDA TANGAN UNTUK NOTA TERAKHIR ---
    // Dipanggil di luar loop untuk menangani sisa barang (misal total barang ada 13, nota 1 isi 10 sudah ada ttd, nota 2 isi 3 dicetak ttd-nya di sini)
    printSignatures(currentY);

    // Download berkas PDF
    doc.save(`Bon_Distribusi_${data.employee_name}.pdf`);
};


</script>