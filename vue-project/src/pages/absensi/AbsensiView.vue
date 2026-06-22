<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useAbsensiStore } from "@/stores/absensi";
import { useToast } from "primevue/usetoast"; // <-- 1. Import Toast
import Toast from "primevue/toast";
// Tambahkan di script setup Absensi.vue
import { jsPDF } from "jspdf";

import Calendar from 'primevue/calendar';

const store = useAbsensiStore();
const toast = useToast(); // <-- 2. Inisialisasi service Toast
const bulan = ref(new Date().getMonth() + 1);
const tahun = ref(new Date().getFullYear());



onMounted(() => {
    loadData();
});

watch(
    [bulan, tahun],
    () => {
        loadData();
    }
);

const loadData = async () => {
    await store.fetchAbsensi(
        bulan.value,
        tahun.value
    );
};

/////

const daysInMonth = computed(() => {

    return new Date(
        tahun.value,
        bulan.value,
        0
    ).getDate();

});



const dates = computed(() => {

    if (selectedDate.value) {
        return [selectedDate.value];
    }

    return Array.from(
        { length: daysInMonth.value },
        (_, i) => i + 1
    );

});

const visibleDates = computed(() => {

    return dates.value.filter(date =>
        date >= start.value &&
        date < start.value + windowSize
    );

});

const months = [
    { label: 'Januari', value: 1 },
    { label: 'Februari', value: 2 },
    { label: 'Maret', value: 3 },
    { label: 'April', value: 4 },
    { label: 'Mei', value: 5 },
    { label: 'Juni', value: 6 },
    { label: 'Juli', value: 7 },
    { label: 'Agustus', value: 8 },
    { label: 'September', value: 9 },
    { label: 'Oktober', value: 10 },
    { label: 'November', value: 11 },
    { label: 'Desember', value: 12 },
];



const statusOptions = [
    { label: 'Hadir', value: 'hadir' },
    { label: 'Izin', value: 'izin' },
    { label: 'Sakit', value: 'sakit' },
    { label: 'Cuti', value: 'cuti' },
    { label: 'Alpha', value: 'alpha' },
];



const saveStatus = async (pegawaiId, date, value) => {

    const tanggal = `${tahun.value}-${String(bulan.value).padStart(2, '0')}-${String(date).padStart(2, '0')}`;

    // 1. optimistic update (langsung UI berubah)
    const pegawai = store.data.find(p => p.id === pegawaiId);

    if (!pegawai.absensi) pegawai.absensi = {};

    pegawai.absensi[date] = value;

    // 2. API background
    try {
        await store.saveAbsensi({
            pegawai_id: pegawaiId,
            tanggal,
            status: value,
        });
    } catch (e) {
        console.error(e);
    }
};


const search = ref('');
const selectedDate = ref(null);
const selectedStatus = ref(null); // <-- Tambahkan ref baru untuk filter status

const start = ref(1); // mulai dari tanggal 1
const windowSize = 10;

watch(selectedDate, (newDate) => {
    if (!newDate) {
        selectedStatus.value = null;
    }
});

const ranges = computed(() => {

    const result = [];
    const total = daysInMonth.value;

    for (let i = 1; i <= total; i += windowSize) {

        result.push({
            label: `${i}-${Math.min(i + windowSize - 1, total)}`,
            start: i
        });

    }

    return result;

});




const filteredData = computed(() => {
    let result = store.data;

    // 1. Filter berdasarkan nama pencarian
    if (search.value) {
        result = result.filter(p =>
            p.nama.toLowerCase().includes(search.value.toLowerCase())
        );
    }

    // 2. Filter berdasarkan Status Kehadiran pada Tanggal Tertentu
    // Hanya berjalan jika KEDUA filter (Tanggal & Status) terisi
    if (selectedDate.value && selectedStatus.value) {
        result = result.filter(p => {
            const statusPegawai = p.absensi?.[selectedDate.value];

            // Jika memilih status '-', cari data yang absensinya kosong/null
            if (selectedStatus.value === '-') {
                return !statusPegawai;
            }

            return statusPegawai === selectedStatus.value;
        });
    }

    return result;
});

const years = [
    2026,
    2027,
    2028,
    2029,
    2030
];

const statusList = ['-', 'hadir', 'izin', 'sakit', 'cuti', 'alpha'];

const cycleStatus = (pegawai, date) => {

    const current = pegawai.absensi?.[date] ?? '-';

    const nextIndex = (statusList.indexOf(current) + 1) % statusList.length;

    const next = statusList[nextIndex];

    saveStatus(pegawai.id, date, next === '-' ? null : next);
};


const bulkDate = ref(null);
const bulkStatus = ref('hadir');
const selectedPegawai = ref([]);



const filteredList = ref([]);

const onSearch = (event) => {
    filteredList.value = store.data.filter(p =>
        p.nama.toLowerCase().includes(event.query.toLowerCase())
    );
};



const applyBulk = async () => {
    // 1. Validasi awal + Toast Peringatan jika kosong
    if (!bulkDate.value || !bulkStatus.value) {
        toast.add({
            severity: 'warn',
            summary: 'Peringatan',
            detail: 'Silakan pilih tanggal dan status absensi terlebih dahulu.',
            life: 3000
        });
        return;
    }

    const target = selectedPegawai.value.length
        ? selectedPegawai.value
        : store.data;

    const promises = [];

    target.forEach(pegawai => {
        const tanggal = `${tahun.value}-${String(bulan.value).padStart(2, '0')}-${String(bulkDate.value).padStart(2, '0')}`;

        // Optimistic update (UI langsung berubah instant)
        if (!pegawai.absensi) pegawai.absensi = {};
        pegawai.absensi[bulkDate.value] = bulkStatus.value;

        promises.push(
            store.saveAbsensi({
                pegawai_id: pegawai.id,
                tanggal,
                status: bulkStatus.value
            })
        );
    });

    // 2. Jalankan API dengan bungkus try-catch agar Toast tahu statusnya
    try {
        store.loading = true; // Opsional jika store Anda punya state loading
        await Promise.all(promises);

        // Toast Sukses
        toast.add({
            severity: 'success',
            summary: 'Berhasil',
            detail: `Absensi massal berhasil diterapkan untuk ${target.length} pegawai.`,
            life: 3000
        });

        // Bersihkan form aksi bulk setelah sukses agar tidak sengaja ter-klik double

        selectedPegawai.value = [];

    } catch (error) {
        console.error("Gagal apply bulk absensi:", error);

        // Toast Gagal
        toast.add({
            severity: 'error',
            summary: 'Gagal',
            detail: 'Terjadi kesalahan sistem saat menyimpan absensi massal.',
            life: 4000
        });
    } finally {
        store.loading = false;
    }
};

const isLastRange = computed(() => {

    const end = start.value + windowSize - 1;
    const max = daysInMonth.value;

    return end >= 21 || end === max;

});

const formatSummary = (summary) => {

    return `H:${summary.hadir} | I:${summary.izin} | S:${summary.sakit} | C:${summary.cuti} | A:${summary.alpha}`;

};


const calculateSummary = (absensi) => {

    const result = {
        hadir: 0,
        izin: 0,
        sakit: 0,
        cuti: 0,
        alpha: 0
    };

    if (!absensi) return result;

    Object.values(absensi).forEach(status => {

        if (result.hasOwnProperty(status)) {
            result[status]++;
        }

    });

    return result;
};

const enrichedData = computed(() => {

    return filteredData.value.map(p => {

        return {
            ...p,
            summary: calculateSummary(p.absensi ?? {})
        };

    });

});




// Di script setup
const tanggalCetak = ref(new Date()); // Default hari ini


const cetakLaporan = async () => {
    toast.add({
        severity: 'info',
        summary: 'Memproses PDF',
        detail: 'Membuat halaman dokumen secara presisi...',
        life: 2500
    });

    // 1. Tentukan jumlah baris maksimal pegawai per halaman (15 baris)
    const BARIS_PER_HALAMAN = 15;

    // Pecah data pegawai menjadi kelompok-kelompok kecil berisi 15 data
    const kelompokPegawai = [];
    for (let i = 0; i < enrichedData.value.length; i += BARIS_PER_HALAMAN) {
        kelompokPegawai.push(enrichedData.value.slice(i, i + BARIS_PER_HALAMAN));
    }

    // 2. Siapkan dokumen kertas A4 Posisi Tidur (Landscape) sejak awal
    const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'px',
        format: 'a4'
    });

    const lebarKertasA4Pxl = doc.internal.pageSize.getWidth();
    const rasioSkalaAmal = lebarKertasA4Pxl / 1300;
    const namaBulan = months.find(m => m.value === bulan.value)?.label ?? '';
    const tahunVal = tahun.value;
    const bulanTahunFormatted = `${namaBulan} ${tahunVal}`;
    const tanggalDipilih = tanggalCetak.value || new Date();

    // Format tanggal ke Bahasa Indonesia (contoh: 21 Juni 2026)
    const tglFormatted = tanggalDipilih.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });

    // 3. Proses render halaman satu per satu menggunakan perulangan async/await
    for (let indexKelompok = 0; indexKelompok < kelompokPegawai.length; indexKelompok++) {
        const daftarPegawai = kelompokPegawai[indexKelompok];

        // Jika ini bukan halaman pertama, tambahkan lembar halaman baru di jsPDF
        if (indexKelompok > 0) {
            doc.addPage();
        }

        // A. Susun header tanggal (1 - 31)
        let headerTanggalHtml = '';
        for (let d = 1; d <= daysInMonth.value; d++) {
            headerTanggalHtml += `<th style="border: 1px solid #94a3b8; padding: 6px 4px; text-align: center; background-color: #f1f5f9; font-size: 10px; width: 30px;">${d}</th>`;
        }

        // B. Susun baris data pegawai untuk halaman ini
        let bodyPegawaiHtml = '';
        daftarPegawai.forEach((pegawai, indexPegawai) => {
            const nomorUrut = (indexKelompok * BARIS_PER_HALAMAN) + indexPegawai + 1;

            let barisStatusHtml = '';
            for (let d = 1; d <= daysInMonth.value; d++) {
                const status = pegawai.absensi?.[d] ?? '-';
                let inlineStyle = 'color: #64748b;';
                if (status === 'hadir') inlineStyle = 'color: #16a34a; font-weight: bold;';
                if (status === 'alpha') inlineStyle = 'color: #dc2626; font-weight: bold;';
                if (['izin', 'sakit', 'cuti'].includes(status)) inlineStyle = 'color: #d97706; font-weight: bold;';

                const inisialHuruf = status === 'hadir' ? 'H' : status === '-' ? '-' : status.charAt(0).toUpperCase();
                barisStatusHtml += `<td style="border: 1px solid #cbd5e1; padding: 6px 4px; text-align: center; font-size: 10px; ${inlineStyle}">${inisialHuruf}</td>`;
            }

            bodyPegawaiHtml += `
                <tr>
                    <td style="border: 1px solid #cbd5e1; padding: 6px; text-align: center; font-size: 11px; color: #475569;">${nomorUrut}</td>
                    <td style="border: 1px solid #cbd5e1; padding: 6px; font-size: 11px; font-weight: 500; color: #1e293b; width: 160px; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        ${pegawai.nama}
                    </td>
                    ${barisStatusHtml}
                    <td style="border: 1px solid #cbd5e1; padding: 6px; font-size: 10px; font-weight: bold; text-align: center; white-space: nowrap; background-color: #f8fafc; color: #334155;">
                        ${formatSummary(pegawai.summary)}
                    </td>
                </tr>
            `;
        });

        // C. Satukan menjadi tabel halaman ini
        const tabelHtml = `
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-family: sans-serif; table-layout: fixed;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #94a3b8; padding: 6px; background-color: #f1f5f9; font-size: 11px; width: 35px;">No</th>
                        <th style="border: 1px solid #94a3b8; padding: 6px; text-align: left; background-color: #f1f5f9; font-size: 11px; width: 160px;">Nama Pegawai</th>
                        ${headerTanggalHtml}
                        <th style="border: 1px solid #94a3b8; padding: 6px; background-color: #f1f5f9; font-size: 11px; width: 130px;">Summary</th>
                    </tr>
                </thead>
                <tbody>
                    ${bodyPegawaiHtml}
                </tbody>
            </table>
        `;

        // D. Masukkan tabel ke template master dari DB
        let htmlHalaman = store.templateHtml
            .replace('{{TABEL_ABSENSI}}', tabelHtml)
            .replace('{{BULAN_TAHUN}}', bulanTahunFormatted)
            .replace('{{TANGGAL_SEKARANG}}', tglFormatted); // Menggunakan tanggal pilihan use

        // Pastikan htmlHalaman dibungkus ql-editor agar CSS Quill bekerja
        htmlHalaman = `<div class="ql-editor">${htmlHalaman}</div>`;

        // E. Buat kontainer temporer khusus untuk halaman yang sedang diproses saja
        const halamanDiv = document.createElement('div');
        halamanDiv.style.width = '1300px';
        halamanDiv.style.padding = '20px 30px';
        halamanDiv.style.boxSizing = 'border-box';
        halamanDiv.style.backgroundColor = '#ffffff';

        // KUNCI: Tambahkan CSS agar class Quill dibaca dan pewarisan style berjalan
        halamanDiv.innerHTML = `
    <style>
        .ql-align-center { text-align: center !important; }
        .ql-align-right { text-align: right !important; }
        .ql-align-justify { text-align: justify !important; }
        .ql-editor { 
            font-family: Arial, sans-serif !important; 
        }
        .ql-size-small { font-size: 10px; }
        .ql-size-large { font-size: 18px; }
        .ql-size-huge { font-size: 24px; }
        /* Memastikan tabel absensi juga mewarisi font dan gaya dari pembungkusnya */
        table { font-family: inherit !important; }
    </style>
    <div class="ql-editor">
        ${htmlHalaman}
    </div>
`;
        document.body.appendChild(halamanDiv);
        // F. RENDER LANGSUNG KE HALAMAN AKTIF jsPDF (Gunakan await agar runtut)
        await doc.html(halamanDiv, {
            x: 0,
            y: 0,
            html2canvas: {
                scale: rasioSkalaAmal,
                logging: false,
                useCORS: true
            },
            autoPaging: false // Matikan auto paging bawaan karena kita mengontrol page secara manual
        });

        // Hapus elemen temporer dari DOM setelah berhasil di-foto oleh html2canvas
        document.body.removeChild(halamanDiv);
    }

    // 4. Setelah semua halaman selesai dirender secara berurutan, unduh file PDF
    doc.save(`Rekap_Absensi_${bulanTahunFormatted}.pdf`);
    toast.add({ severity: 'success', summary: 'Selesai', detail: 'PDF rapi per halaman berhasil diunduh.', life: 3000 });
};


</script>



<template>



    <div class="p-4">

        <Toast />

        <!-- HEADER -->

        <!-- <div class="flex items-center justify-between mb-4">



            <h1 class="text-2xl font-bold">

                Management Absensi

            </h1>



        </div>


        <Button label="Cetak Laporan PDF" icon="pi pi-file-pdf" severity="danger" @click="cetakLaporan" /> -->



        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">

            <h1 class="text-2xl font-bold">
                Management Absensi
            </h1>


        </div>

        <div class="flex flex-col md:flex-row gap-3 mb-4 mt-5">



            <!-- SEARCH -->

            <InputText v-model="search" placeholder="Cari nama pegawai..." class="w-full md:w-72" />



            <!-- FILTER BULAN -->

            <Select v-model="bulan" :options="months" optionLabel="label" optionValue="value" placeholder="Bulan" />



            <Select v-model="tahun" :options="years" placeholder="Tahun" />



            <!-- FILTER TANGGAL -->

            <div class="flex flex-wrap items-center gap-3">
                <Select v-model="selectedDate" :options="Array.from({ length: daysInMonth }, (_, i) => i + 1)"
                    placeholder="Pilih Tanggal" showClear class="w-40" />

                <Select v-model="selectedStatus" :options="['-', ...statusList.filter(s => s !== '-')]"
                    placeholder="Pilih Status" :disabled="!selectedDate" showClear class="w-40">
                    <template #option="slotProps">
                        <span class="capitalize">{{ slotProps.option === '-' ? 'Belum Absen' : slotProps.option
                        }}</span>
                    </template>
                </Select>
            </div>







        </div>



        <div class="flex gap-2 mb-3">

            <Button v-for="r in ranges" :key="r.start" :label="r.label" size="small"
                :severity="start === r.start ? 'success' : 'secondary'" @click="start = r.start" />

        </div>

        <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 mb-6 mt-6 shadow-sm">

            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate-200/60 text-slate-700">
                <h4 class="text-sm font-semibold tracking-wide uppercase">
                    Aksi Massal
                </h4>
                <span class="text-xs text-slate-400 font-normal normal-case ml-1">
                    Isi absensi untuk banyak pegawai sekaligus
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-4">

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-500 tracking-wide uppercase px-0.5">Tanggal</label>
                    <Select v-model="bulkDate" :options="dates" placeholder="Pilih Tanggal" class="w-40 shadow-sm" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-500 tracking-wide uppercase px-0.5">Status</label>
                    <Select v-model="bulkStatus" :options="statusOptions" optionLabel="label" optionValue="value"
                        placeholder="Pilih Status" class="w-40 shadow-sm" />
                </div>

                <div class="flex-1 min-w-[250px] flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-500 tracking-wide uppercase px-0.5">Target
                        Pegawai</label>
                    <AutoComplete v-model="selectedPegawai" multiple forceSelection optionLabel="nama"
                        :suggestions="filteredList" @complete="onSearch"
                        placeholder="Ketik nama pegawai (kosongkan untuk semua)..." class="w-full shadow-sm" />
                </div>

                <Button label="Terapkan Absensi" icon="pi pi-check" severity="success" @click="applyBulk"
                    class="shadow-sm active:scale-95 transition-transform duration-150 px-4 self-end h-[38px]" />

            </div>
        </div>



        <!-- DEBUG -->

        <div class="p-4 w-full overflow-hidden">

        <p class="text-sm text-gray-500 mb-3">
            Total: {{ enrichedData.length }} pegawai
         
        </p>

            <div class="overflow-auto  rounded-xl">



                <DataTable :value="enrichedData" stripedRows scrollable scrollHeight="600px" class="text-sm"
                    size="small">





                    <Column field="nama" header="Nama Pegawai" frozen style="min-width: 220px" />



                    <Column v-for="date in visibleDates" :key="date" style="min-width: 50px" bodyClass="text-center">
                        <template #header>
                            <div class="w-full flex justify-center text-center font-semibold">
                                {{ date }}
                            </div>
                        </template>

                        <template #body="{ data }">
                            <div class="flex items-center justify-center">
                                <button class="text-xs px-2 py-1 rounded" @click="cycleStatus(data, date)">
                                    {{ data.absensi?.[date] ?? '-' }}
                                </button>
                            </div>
                        </template>
                    </Column>

                    <Column v-if="isLastRange" header="Summary" frozen alignFrozen="right">
                        <template #body="{ data }">

                            <div class="text-xs font-medium whitespace-nowrap">

                                {{ formatSummary(data.summary) }}

                            </div>

                        </template>
                    </Column>



                </DataTable>



            </div>

        </div>


        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 m-6">

         <div>

         </div>

            <div class="flex items-center gap-3">
                <Calendar v-model="tanggalCetak" dateFormat="dd/mm/yy" showIcon placeholder="Pilih Tanggal Cetak"
                    class="w-40" />

                <Button label="Cetak PDF" icon="pi pi-file-pdf" @click="cetakLaporan" severity="info" />
            </div>
        </div>



    </div>



</template>