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
    { label: 'Pengganti Libur', value: 'penganti_libur' },
    { label: 'Lepas Piket', value: 'lepas_piket' },
    { label: 'Tanpa Keterangan', value: 'alpha' },
    { label: 'Reset (Hapus)', value: null },
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

const statusList = ['-', 'hadir', 'izin', 'sakit', 'cuti', 'alpha', 'lepas_piket', 'pengganti_libur'];
// Mapping label untuk mempermudah pembacaan di UI (Tooltip / Keterangan Tombol)
// Objek pemetaan status untuk tampilan tombol di tabel
const statusMapping = {
    'hadir': { inisial: 'H', class: 'bg-green-100 text-green-700 font-bold' },
    'alpha': { inisial: 'TK', class: 'bg-red-100 text-red-700 font-bold' },
    'tanpa keterangan': { inisial: 'TK', class: 'bg-red-100 text-red-700 font-bold' },
    'izin': { inisial: 'TA', class: 'bg-amber-100 text-amber-700 font-bold' },
    'sakit': { inisial: 'S', class: 'bg-amber-100 text-amber-700 font-bold' },
    'cuti': { inisial: 'C', class: 'bg-amber-100 text-amber-700 font-bold' },
    'lepas_piket': { inisial: 'LP', class: 'bg-indigo-100 text-indigo-700 font-bold' },
    'pengganti_libur': { inisial: 'PL', class: 'bg-cyan-100 text-cyan-700 font-bold' },
    '-': { inisial: '-', class: 'bg-gray-100 text-gray-500' }
};

// Helper fungsi untuk mengambil inisial huruf di template
const getInisialStatus = (statusRaw) => {
    return statusMapping[statusRaw]?.inisial || '-';
};

// Helper fungsi untuk mengambil warna class di template
const getClassStatus = (statusRaw) => {
    return statusMapping[statusRaw]?.class || 'bg-gray-100 text-gray-500';
};
const cycleStatus = (pegawai, date) => {
    if (!pegawai.absensi) {
        pegawai.absensi = {};
    }

    const current = pegawai.absensi[date] ?? '-';
    const nextIndex = (statusList.indexOf(current) + 1) % statusList.length;
    const next = statusList[nextIndex];

    // 1. Update status absensi
    if (next === '-') {
        delete pegawai.absensi[date];
    } else {
        pegawai.absensi[date] = next;
    }

    // 2. 🌟 UPDATE SUMMARY SECARA INSTAN 🌟
    // Menghitung ulang summary khusus untuk pegawai yang sedang diklik saat ini juga
    pegawai.summary = calculateSummary(pegawai.absensi);

    // 3. Simpan perubahan ke database
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
    const isStatusValid = bulkStatus.value !== undefined && bulkStatus.value !== '';

    if (!bulkDate.value || !isStatusValid) {
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

    // 1. Kumpulkan semua ID pegawai dan susun tanggalnya
    const pegawaiIds = target.map(p => p.id);
    const tanggal = `${tahun.value}-${String(bulan.value).padStart(2, '0')}-${String(bulkDate.value).padStart(2, '0')}`;

    // 2. Lakukan Optimistic Update pada UI secara instan
    target.forEach(pegawai => {
        if (!pegawai.absensi) pegawai.absensi = {};
        if (bulkStatus.value === null) {
            delete pegawai.absensi[bulkDate.value];
        } else {
            pegawai.absensi[bulkDate.value] = bulkStatus.value;
        }
    });

    try {
        store.loading = true;

        // 3. Hanya menembak API SATU KALI dengan membawa seluruh data array ID
        await store.saveBulkAbsensi({
            pegawai_ids: pegawaiIds,
            tanggal: tanggal,
            status: bulkStatus.value
        });

        const actionText = bulkStatus.value === null ? 'direset' : 'diterapkan';

        toast.add({
            severity: 'success',
            summary: 'Berhasil',
            detail: `Absensi massal berhasil ${actionText} untuk ${target.length} pegawai.`,
            life: 3000
        });

        bulkStatus.value = '';
        selectedPegawai.value = [];

    } catch (error) {
        console.error("Gagal apply bulk absensi:", error);
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
    if (!summary) return 'H:0 | TA:0 | S:0 | C:0 | TK:0 | LP:0 | PL:0';

    // Alpha di-display sebagai TK (Tanpa Keterangan) agar konsisten dengan tabel
    return `H:${summary.hadir} | TA:${summary.izin} | S:${summary.sakit} | C:${summary.cuti} | TK:${summary.alpha} | LP:${summary.lepas_piket} | PL:${summary.pengganti_libur}`;
};

const calculateSummary = (absensi) => {
    const result = {
        hadir: 0,
        izin: 0,
        sakit: 0,
        cuti: 0,
        alpha: 0,
        lepas_piket: 0,     // Tambahan status baru
        pengganti_libur: 0  // Tambahan status baru
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


// const cetakLaporan = async () => {
//     toast.add({
//         severity: 'info',
//         summary: 'Memproses PDF',
//         detail: 'Membuat halaman dokumen secara presisi...',
//         life: 2500
//     });

//     // 1. Tentukan jumlah baris maksimal pegawai per halaman (15 baris)
//     const BARIS_PER_HALAMAN = 15;

//     // Pecah data pegawai menjadi kelompok-kelompok kecil berisi 15 data
//     const kelompokPegawai = [];
//     for (let i = 0; i < enrichedData.value.length; i += BARIS_PER_HALAMAN) {
//         kelompokPegawai.push(enrichedData.value.slice(i, i + BARIS_PER_HALAMAN));
//     }

//     // 2. Siapkan dokumen kertas A4 Posisi Tidur (Landscape) sejak awal
//     const doc = new jsPDF({
//         orientation: 'landscape',
//         unit: 'px',
//         format: 'a4'
//     });

//     const lebarKertasA4Pxl = doc.internal.pageSize.getWidth();
//     const rasioSkalaAmal = lebarKertasA4Pxl / 1300;
//     const namaBulan = months.find(m => m.value === bulan.value)?.label ?? '';
//     const tahunVal = tahun.value;
//     const bulanTahunFormatted = `${namaBulan} ${tahunVal}`;
//     const tanggalDipilih = tanggalCetak.value || new Date();

//     // Format tanggal ke Bahasa Indonesia (contoh: 21 Juni 2026)
//     const tglFormatted = tanggalDipilih.toLocaleDateString('id-ID', {
//         day: 'numeric',
//         month: 'long',
//         year: 'numeric'
//     });

//     // 3. Proses render halaman satu per satu menggunakan perulangan async/await
//     for (let indexKelompok = 0; indexKelompok < kelompokPegawai.length; indexKelompok++) {
//         const daftarPegawai = kelompokPegawai[indexKelompok];

//         // Jika ini bukan halaman pertama, tambahkan lembar halaman baru di jsPDF
//         if (indexKelompok > 0) {
//             doc.addPage();
//         }

//         // A. Susun header tanggal (1 - 31)
//         let headerTanggalHtml = '';
//         for (let d = 1; d <= daysInMonth.value; d++) {
//             headerTanggalHtml += `<th style="border: 1px solid #94a3b8; padding: 6px 4px; text-align: center; background-color: #f1f5f9; font-size: 10px; width: 30px;">${d}</th>`;
//         }

//         // B. Susun baris data pegawai untuk halaman ini
//         let bodyPegawaiHtml = '';
//         daftarPegawai.forEach((pegawai, indexPegawai) => {
//             const nomorUrut = (indexKelompok * BARIS_PER_HALAMAN) + indexPegawai + 1;

//             // 1. Definisikan Mapping Status untuk Label, Inisial, dan Warna Style
//             const statusMapping = {
//                 'hadir': { inisial: 'H', style: 'color: #16a34a; font-weight: bold;' },
//                 'alpha': { inisial: 'TK', style: 'color: #dc2626; font-weight: bold;' }, // alpha -> TK (Tanpa Keterangan)
//                 'tanpa keterangan': { inisial: 'TK', style: 'color: #dc2626; font-weight: bold;' },
//                 'tidak hadir': { inisial: 'TH', style: 'color: #ef4444; font-weight: bold;' },
//                 'izin': { inisial: 'I', style: 'color: #d97706; font-weight: bold;' },
//                 'sakit': { inisial: 'S', style: 'color: #d97706; font-weight: bold;' },
//                 'cuti': { inisial: 'C', style: 'color: #d97706; font-weight: bold;' },
//                 'cuti alasan penting': { inisial: 'CAP', style: 'color: #b45309; font-weight: bold;' },
//                 'cuti melahirkan': { inisial: 'CM', style: 'color: #b45309; font-weight: bold;' },
//                 'dinas_luar': { inisial: 'DL', style: 'color: #2563eb; font-weight: bold;' },
//                 'dinas_luar_full': { inisial: 'DLF', style: 'color: #2563eb; font-weight: bold;' },
//                 'dinas_luar_half': { inisial: 'DLH', style: 'color: #3b82f6; font-weight: bold;' },
//                 'lepas_piket': { inisial: 'LP', style: 'color: #6366f1; font-weight: bold;' },     // Warna Indigo
//                 'pengganti_libur': { inisial: 'PL', style: 'color: #06b6d4; font-weight: bold;' },  // Warna Cyan
//                 '-': { inisial: '-', style: 'color: #64748b;' }
//             };

//             let barisStatusHtml = '';
//             for (let d = 1; d <= daysInMonth.value; d++) {
//                 const rawStatus = pegawai.absensi?.[d] ?? '-';

//                 // Ambil konfigurasi mapping berdasarkan status, jika tidak terdaftar pakai default '-'
//                 const konfigurasi = statusMapping[rawStatus] || statusMapping['-'];

//                 const inisialHuruf = konfigurasi.inisial;
//                 const inlineStyle = konfigurasi.style;

//                 barisStatusHtml += `<td style="border: 1px solid #cbd5e1; padding: 6px 4px; text-align: center; font-size: 10px; ${inlineStyle}">${inisialHuruf}</td>`;
//             }

//             bodyPegawaiHtml += `
//         <tr>
//             <td style="border: 1px solid #cbd5e1; padding: 6px; text-align: center; font-size: 11px; color: #475569;">${nomorUrut}</td>
//             <td style="border: 1px solid #cbd5e1; padding: 6px; font-size: 11px; font-weight: 500; color: #1e293b; width: 160px; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
//                 ${pegawai.nama}
//             </td>
//             ${barisStatusHtml}
//             <td style="border: 1px solid #cbd5e1; padding: 6px; font-size: 10px; font-weight: bold; text-align: center; white-space: nowrap; background-color: #f8fafc; color: #334155;">
//                 ${formatSummary(pegawai.summary)}
//             </td>
//         </tr>
//     `;
//         });

//         // C. Satukan menjadi tabel halaman ini
//         const tabelHtml = `
//             <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-family: sans-serif; table-layout: fixed;">
//                 <thead>
//                     <tr>
//                         <th style="border: 1px solid #94a3b8; padding: 6px; background-color: #f1f5f9; font-size: 11px; width: 35px;">No</th>
//                         <th style="border: 1px solid #94a3b8; padding: 6px; text-align: left; background-color: #f1f5f9; font-size: 11px; width: 160px;">Nama Pegawai</th>
//                         ${headerTanggalHtml}
//                         <th style="border: 1px solid #94a3b8; padding: 6px; background-color: #f1f5f9; font-size: 11px; width: 130px;">Summary</th>
//                     </tr>
//                 </thead>
//                 <tbody>
//                     ${bodyPegawaiHtml}
//                 </tbody>
//             </table>
//         `;

//         // D. Masukkan tabel ke template master dari DB
//         let htmlHalaman = store.templateHtml
//             .replace('{{TABEL_ABSENSI}}', tabelHtml)
//             .replace('{{BULAN_TAHUN}}', bulanTahunFormatted)
//             .replace('{{TANGGAL_SEKARANG}}', tglFormatted); // Menggunakan tanggal pilihan use

//         // Pastikan htmlHalaman dibungkus ql-editor agar CSS Quill bekerja
//         htmlHalaman = `<div class="ql-editor">${htmlHalaman}</div>`;

//         // E. Buat kontainer temporer khusus untuk halaman yang sedang diproses saja
//         const halamanDiv = document.createElement('div');
//         halamanDiv.style.width = '1300px';
//         halamanDiv.style.padding = '20px 30px';
//         halamanDiv.style.boxSizing = 'border-box';
//         halamanDiv.style.backgroundColor = '#ffffff';

//         // KUNCI: Tambahkan CSS agar class Quill dibaca dan pewarisan style berjalan
//         halamanDiv.innerHTML = `
//     <style>
//         .ql-align-center { text-align: center !important; }
//         .ql-align-right { text-align: right !important; }
//         .ql-align-justify { text-align: justify !important; }
//         .ql-editor { 
//             font-family: Arial, sans-serif !important; 
//         }
//         .ql-size-small { font-size: 10px; }
//         .ql-size-large { font-size: 18px; }
//         .ql-size-huge { font-size: 24px; }
//         /* Memastikan tabel absensi juga mewarisi font dan gaya dari pembungkusnya */
//         table { font-family: inherit !important; }
//     </style>
//     <div class="ql-editor">
//         ${htmlHalaman}
//     </div>
// `;
//         document.body.appendChild(halamanDiv);
//         // F. RENDER LANGSUNG KE HALAMAN AKTIF jsPDF (Gunakan await agar runtut)
//         await doc.html(halamanDiv, {
//             x: 0,
//             y: 0,
//             html2canvas: {
//                 scale: rasioSkalaAmal,
//                 logging: false,
//                 useCORS: true
//             },
//             autoPaging: false // Matikan auto paging bawaan karena kita mengontrol page secara manual
//         });

//         // Hapus elemen temporer dari DOM setelah berhasil di-foto oleh html2canvas
//         document.body.removeChild(halamanDiv);
//     }

//     // 4. Setelah semua halaman selesai dirender secara berurutan, unduh file PDF
//     doc.save(`Rekap_Absensi_${bulanTahunFormatted}.pdf`);
//     toast.add({ severity: 'success', summary: 'Selesai', detail: 'PDF rapi per halaman berhasil diunduh.', life: 3000 });
// };


const cetakLaporan = async () => {
    toast.add({
        severity: 'info',
        summary: 'Memproses PDF',
        detail: 'Membuat halaman dokumen secara presisi...',
        life: 2500
    });

    // Sesuaikan jumlah baris per halaman jika ukuran font yang lebih besar membuat baris meninggi
    const BARIS_PER_HALAMAN = 9;

    const kelompokPegawai = [];
    for (let i = 0; i < enrichedData.value.length; i += BARIS_PER_HALAMAN) {
        kelompokPegawai.push(enrichedData.value.slice(i, i + BARIS_PER_HALAMAN));
    }

    const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'px',
        format: 'a4'
    });

    const lebarKertasA4Pxl = doc.internal.pageSize.getWidth();
    // Mengubah pembagi dari 1380 menjadi 1360 agar tabel melebar pas memenuhi sisi kanan kertas
    const rasioSkalaAmal = lebarKertasA4Pxl / 1340;
    const namaBulan = months.find(m => m.value === bulan.value)?.label ?? '';
    const tahunVal = tahun.value;
    const bulanTahunFormatted = `${namaBulan} ${tahunVal}`;
    const tanggalDipilih = tanggalCetak.value || new Date();

    const tglFormatted = tanggalDipilih.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });

    const statusMapping = {
        'hadir': { inisial: 'H', style: 'color: #16a34a; font-weight: bold;' },
        'alpha': { inisial: 'TK', style: 'color: #dc2626; font-weight: bold;' },
        'tanpa keterangan': { inisial: 'TK', style: 'color: #dc2626; font-weight: bold;' },
        'tidak hadir': { inisial: 'TH', style: 'color: #ef4444; font-weight: bold;' },
        'izin': { inisial: 'TA', style: 'color: #d97706; font-weight: bold;' },
        'sakit': { inisial: 'S', style: 'color: #d97706; font-weight: bold;' },
        'cuti': { inisial: 'C', style: 'color: #d97706; font-weight: bold;' },
        'cuti alasan penting': { inisial: 'CAP', style: 'color: #b45309; font-weight: bold;' },
        'cuti melahirkan': { inisial: 'CM', style: 'color: #b45309; font-weight: bold;' },
        'dinas_luar': { inisial: 'DL', style: 'color: #2563eb; font-weight: bold;' },
        'lepas_piket': { inisial: 'LP', style: 'color: #6366f1; font-weight: bold;' },
        'pengganti_libur': { inisial: 'PL', style: 'color: #06b6d4; font-weight: bold;' },
        '-': { inisial: '-', style: 'color: #64748b;' }
    };

    for (let indexKelompok = 0; indexKelompok < kelompokPegawai.length; indexKelompok++) {
        const daftarPegawai = kelompokPegawai[indexKelompok];

        if (indexKelompok > 0) {
            doc.addPage();
        }

        const totalHari = daysInMonth.value;
        const batasBarisSatu = Math.ceil(totalHari / 2);
        const batasBarisDua = totalHari - batasBarisSatu;

        // Header Atas (1 - 16)
        let headerTanggalRow1 = '';
        for (let d = 1; d <= batasBarisSatu; d++) {
            headerTanggalRow1 += `<th style="border: 1px solid #94a3b8; padding: 6px 2px; text-align: center; background-color: #cbd5e1; font-size: 14px; width: 34px; vertical-align: top !important; font-weight: normal; color: #000;">${d}</th>`;
        }

        // Header Bawah (17 - 31)
        let headerTanggalRow2 = '';
        for (let d = batasBarisSatu + 1; d <= totalHari; d++) {
            headerTanggalRow2 += `<th style="border: 1px solid #94a3b8; padding: 6px 2px; text-align: center; background-color: #cbd5e1; font-size: 14px; width: 34px; vertical-align: top !important; font-weight: normal; color: #000;">${d}</th>`;
        }

        if (batasBarisDua < batasBarisSatu) {
            const selisihKekurangan = batasBarisSatu - batasBarisDua;
            headerTanggalRow2 += `<th colspan="${selisihKekurangan}" style="border: 1px solid #94a3b8; background-color: #cbd5e1; vertical-align: top !important;"></th>`;
        }

        let bodyPegawaiHtml = '';
        daftarPegawai.forEach((pegawai, indexPegawai) => {
            const nomorUrut = (indexKelompok * BARIS_PER_HALAMAN) + indexPegawai + 1;

            // Hitung ulang jumlah hadir secara real-time berdasarkan tanggal yang ada
            let jumlahHadir = 0;
            if (pegawai.absensi) {
                Object.keys(pegawai.absensi).forEach(hari => {
                    const status = pegawai.absensi[hari]?.toLowerCase();
                    // Status 'hadir', 'izin', 'tanpa keterangan', atau 'alpha' terhitung masuk Hadir
                    if (status === 'hadir' || status === 'izin') {
                        jumlahHadir++;
                    }
                });
            }

            // Generate kolom absensi bertingkat (Atas/Bawah disatukan di dalam 1 TD)
            let statusColumnsHtml = '';
            for (let d = 1; d <= batasBarisSatu; d++) {
                // Baris atas (Hari 1-16)
                const rawStatus1 = pegawai.absensi?.[d] ?? '-';
                const config1 = statusMapping[rawStatus1] || statusMapping['-'];

                // Baris bawah (Hari 17-31)
                const hariDua = d + batasBarisSatu;
                let barisDuaContent = '';

                if (hariDua <= totalHari) {
                    const rawStatus2 = pegawai.absensi?.[hariDua] ?? '-';
                    const config2 = statusMapping[rawStatus2] || statusMapping['-'];
                    barisDuaContent = `<div style="padding: 6px 2px; border-top: 1px solid #cbd5e1; min-height: 20px; text-align: center; font-size: 14px; ${config2.style}">${config2.inisial}</div>`;
                } else {
                    // Sel kosong jika bulan hanya sampai tanggal 28, 29, atau 30
                    barisDuaContent = `<div style="padding: 6px 2px; border-top: 1px solid #cbd5e1; min-height: 20px; background-color: #f1f5f9;"></div>`;
                }

                statusColumnsHtml += `
                    <td style="border: 1px solid #cbd5e1; padding: 0; vertical-align: top !important; width: 34px;">
                        <div style="padding: 6px 2px; min-height: 20px; text-align: center; font-size: 14px; ${config1.style}">${config1.inisial}</div>
                        ${barisDuaContent}
                    </td>
                `;
            }



            bodyPegawaiHtml += `
                <tr style="vertical-align: top !important;">
                    <!-- No -->
                    <td style="border: 1px solid #cbd5e1; padding: 8px 4px; text-align: center; font-size: 14px; color: #000; font-weight: normal; vertical-align: top !important;">
                        ${nomorUrut}
                    </td>
                    
                    <!-- Nama & NIP (Satu Sel, Nama diizinkan wrap/turun baris agar tidak terpotong setengah) -->
                    <td style="border: 1px solid #cbd5e1; padding: 8px; font-size: 14px; color: #000; width: 190px; max-width: 190px; vertical-align: top !important; word-wrap: break-word; white-space: normal;">
                        <div style="font-weight: normal; color: #000; margin-bottom: 6px; line-height: 1.3; vertical-align: top !important;">${pegawai.nama}</div>
                        <div style="font-size: 12px; color: #000; font-weight: normal; vertical-align: top !important;">NIP. ${pegawai.nip ?? '-'}</div>
                    </td>
                    
                    <!-- Block Kolom Absensi Tanggal -->
                    ${statusColumnsHtml}
                    
                    <!-- Summary Absensi -->
                    <td style="border: 1px solid #cbd5e1; padding: 8px 6px; font-size: 13px; font-weight: normal; text-align: left; vertical-align: top !important; width: 145px; color: #000;">
                        <div style="line-height: 1.45; vertical-align: top !important;">${formatSummary(pegawai.summary)}</div>
                    </td>
                    
                    <!-- Jumlah Kehadiran Khusus -->
                    <td style="border: 1px solid #cbd5e1; padding: 8px 4px; font-size: 16px; font-weight: normal; text-align: center; vertical-align: top !important; width: 60px; color: #000;">
                        <div style="margin-top: 2px; text-align: center !important; vertical-align: top !important;">${jumlahHadir}</div>
                    </td>
                </tr>
            `;
        });

        const tabelHtml = `
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-family: sans-serif; table-layout: fixed; vertical-align: top !important;">
                <thead>
                    <tr style="vertical-align: top !important;">
                        <th rowspan="2" style="border: 1px solid #94a3b8; padding: 8px 6px; background-color: #cbd5e1; font-size: 14px; color: #000; width: 35px; vertical-align: top !important;">No</th>
                        <th style="border: 1px solid #94a3b8; padding: 8px; text-align: left; background-color: #cbd5e1; font-size: 14px; color: #000; width: 180px; vertical-align: top !important;">Nama Pegawai</th>
                        ${headerTanggalRow1}
                        <th rowspan="2" style="border: 1px solid #94a3b8; padding: 8px 6px; background-color: #cbd5e1; font-size: 14px; color: #000; width: 140px; vertical-align: top !important;">Summary Absensi</th>
                        <th rowspan="2" style="border: 1px solid #94a3b8; padding: 8px 4px; background-color: #cbd5e1; font-size: 13px; color: #000; width: 65px; vertical-align: top !important; text-align: center;">Hadir<br>(H)</th>
                    </tr>
                    <tr style="vertical-align: top !important;">
                        <th style="border: 1px solid #94a3b8; padding: 6px 8px; text-align: left; background-color: #cbd5e1; font-size: 14px; color: #000; width: 180px; vertical-align: top !important;">NIP</th>
                        ${headerTanggalRow2}
                    </tr>
                </thead>
                <tbody>
                    ${bodyPegawaiHtml}
                </tbody>
            </table>
        `;

        const pakahHalamanTerakhir = (indexKelompok === kelompokPegawai.length - 1);
        let htmlTtdTerpilih = '';

        if (pakahHalamanTerakhir) {
            htmlTtdTerpilih = store.templateTtdHtml
                .replace('{{TANGGAL_SEKARANG}}', tglFormatted);
        }

        let htmlHalaman = store.templateHtml
            .replace('{{TABEL_ABSENSI}}', tabelHtml)
            .replace('{{BULAN_TAHUN}}', bulanTahunFormatted)
            .replace('{{BLOK_TANDA_TANGAN}}', htmlTtdTerpilih);

        htmlHalaman = `<div class="ql-editor">${htmlHalaman}</div>`;

        const halamanDiv = document.createElement('div');
        halamanDiv.style.width = '1300px';
        halamanDiv.style.padding = '20px 30px';
        halamanDiv.style.boxSizing = 'border-box';
        halamanDiv.style.backgroundColor = '#ffffff';

        halamanDiv.innerHTML = `
            <style>
                .ql-align-center { text-align: center !important; }
                .ql-align-right { text-align: right !important; }
                .ql-align-justify { text-align: justify !important; }
                .ql-editor { font-family: Arial, sans-serif !important; }
                table, tr, td, th, div, span { vertical-align: top !important; text-align: left; }
                th { text-align: center !important; }
            </style>
            <div class="ql-editor">
                ${htmlHalaman}
            </div>
        `;
        document.body.appendChild(halamanDiv);

        await doc.html(halamanDiv, {
            x: 0,
            y: 0,
            html2canvas: {
                scale: rasioSkalaAmal,
                logging: false,
                useCORS: true
            },
            autoPaging: false
        });

        document.body.removeChild(halamanDiv);
    }

    doc.save(`Rekap_Absensi_${bulanTahunFormatted}.pdf`);
    toast.add({ severity: 'success', summary: 'Selesai', detail: 'PDF Berhasil diunduh.', life: 3000 });
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

                                <button
                                    class="text-xs px-2 py-1 rounded transition-colors duration-150 min-w-[28px] text-center"
                                    :class="getClassStatus(data.absensi?.[date] ?? '-')"
                                    @click="cycleStatus(data, date)">
                                    {{ getInisialStatus(data.absensi?.[date] ?? '-') }}
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


        <!-- KETERANGAN MAPPING VALUE ABSENSI -->
        <div class="mt-4 p-4 bg-surface-50   border-surface-200 rounded-lg">
            <div class="text-xs font-semibold text-surface-600 mb-2.5 flex items-center gap-1.5">
                <i class="pi pi-info-circle text-primary"></i>
                <span>Keterangan Status Absensi:</span>
            </div>

            <div class="flex flex-wrap gap-3 items-center">
                <!-- Hadir -->
                <div class="flex items-center gap-1.5">
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700 font-bold min-w-[24px] text-center">H</span>
                    <span class="text-xs text-surface-600 font-medium">Hadir</span>
                </div>


                <!-- Sakit -->
                <div class="flex items-center gap-1.5">
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-700 font-bold min-w-[24px] text-center">S</span>
                    <span class="text-xs text-surface-600 font-medium">Sakit</span>
                </div>

                <!-- Cuti -->
                <div class="flex items-center gap-1.5">
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-700 font-bold min-w-[24px] text-center">C</span>
                    <span class="text-xs text-surface-600 font-medium">Cuti</span>
                </div>

                <!-- Izin -->
                <div class="flex items-center gap-1.5">
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-700 font-bold min-w-[24px] text-center">TA</span>
                    <span class="text-xs text-surface-600 font-medium">Tidak Apel</span>
                </div>


                <!-- Tanpa Keterangan (Alpha) -->
                <div class="flex items-center gap-1.5">
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-700 font-bold min-w-[24px] text-center">TK</span>
                    <span class="text-xs text-surface-600 font-medium">Tanpa Keterangan</span>
                </div>

                <!-- Lepas Piket -->
                <div class="flex items-center gap-1.5">
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 font-bold min-w-[24px] text-center">LP</span>
                    <span class="text-xs text-surface-600 font-medium">Lepas Piket</span>
                </div>

                <!-- Pengganti Libur -->
                <div class="flex items-center gap-1.5">
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-cyan-100 text-cyan-700 font-bold min-w-[24px] text-center">PL</span>
                    <span class="text-xs text-surface-600 font-medium">Pengganti Libur</span>
                </div>

                <!-- Belum Absen -->
                <div class="flex items-center gap-1.5">
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500 min-w-[24px] text-center">-</span>
                    <span class="text-xs text-surface-500 italic">Belum Absen (Klik untuk mengisi)</span>
                </div>
            </div>
        </div>



    </div>



</template>