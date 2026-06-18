<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useAbsensiStore } from "@/stores/absensi";
import { useToast } from "primevue/usetoast"; // <-- 1. Import Toast
import Toast from "primevue/toast";

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



// const filteredData = computed(() => {
//     if (!search.value) return store.data;

//     return store.data.filter(p =>
//         p.nama.toLowerCase().includes(search.value.toLowerCase())
//     );
// });

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



// const applyBulk = async () => {

//     if (!bulkDate.value || !bulkStatus.value) return;

//     const target = selectedPegawai.value.length
//         ? selectedPegawai.value
//         : store.data;

//     const promises = [];

//     target.forEach(pegawai => {

//         const tanggal = `${tahun.value}-${String(bulan.value).padStart(2, '0')}-${String(bulkDate.value).padStart(2, '0')}`;

//         // optimistic update
//         if (!pegawai.absensi) pegawai.absensi = {};
//         pegawai.absensi[bulkDate.value] = bulkStatus.value;

//         promises.push(
//             store.saveAbsensi({
//                 pegawai_id: pegawai.id,
//                 tanggal,
//                 status: bulkStatus.value
//             })
//         );

//     });

//     await Promise.all(promises);
// };

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


</script>



<template>



    <div class="p-4">

        <Toast />

        <!-- HEADER -->

        <div class="flex items-center justify-between mb-4">



            <h1 class="text-2xl font-bold">

                Management Absensi

            </h1>



        </div>





        <div class="flex flex-col md:flex-row gap-3 mb-4">



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



    </div>



</template>