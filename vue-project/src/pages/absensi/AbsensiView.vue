<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useAbsensiStore } from "@/stores/absensi";



const store = useAbsensiStore();

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

const start = ref(1); // mulai dari tanggal 1
const windowSize = 10;

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
    if (!search.value) return store.data;

    return store.data.filter(p =>
        p.nama.toLowerCase().includes(search.value.toLowerCase())
    );
});

const years = [
    2024,
    2025,
    2026,
    2027,
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

    if (!bulkDate.value || !bulkStatus.value) return;

    const target = selectedPegawai.value.length
        ? selectedPegawai.value
        : store.data;

    const promises = [];

    target.forEach(pegawai => {

        const tanggal = `${tahun.value}-${String(bulan.value).padStart(2, '0')}-${String(bulkDate.value).padStart(2, '0')}`;

        // optimistic update
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

    await Promise.all(promises);
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

            <Select v-model="selectedDate" :options="dates" placeholder="Filter Tanggal" class="w-full md:w-44"
                showClear />







        </div>

        <div class="flex gap-2 mb-3">

            <Button v-for="r in ranges" :key="r.start" :label="r.label" size="small"
                :severity="start === r.start ? 'success' : 'secondary'" @click="start = r.start" />

        </div>


        <div class="p-3  rounded mb-4 space-y-3 gap-3">

            <!-- DATE -->
            <Select v-model="bulkDate" :options="dates" placeholder="Pilih Tanggal" class="w-40 " />

            <!-- STATUS -->
            <Select v-model="bulkStatus" :options="statusOptions" optionLabel="label" optionValue="value"
                placeholder="Status" class="w-40 mx-4" />

            <AutoComplete v-model="selectedPegawai" multiple forceSelection optionLabel="nama"
                :suggestions="filteredList" @complete="onSearch" class="w-150"/>


            <!-- APPLY -->
            <Button label="Apply Bulk" icon="pi pi-check" severity="success" @click="applyBulk" class=" mx-4" />

        </div>



        <!-- DEBUG -->

        <div class="p-4 w-full overflow-hidden">



            <div class="overflow-auto  rounded-xl">



                <DataTable :value="enrichedData" stripedRows scrollable scrollHeight="600px" class="text-sm"
                    size="small">



                    <!-- NAMA -->

                    <Column field="nama" header="Nama Pegawai" frozen style="min-width: 220px" />



                    <!-- TANGGAL -->

                    <Column v-for="date in visibleDates" :key="date" :header="date" style="min-width: 50px">



                        <template #body="{ data }">



                            <div class="flex items-center justify-center">

 

                                <button class=" text-xs px-2 py-1 rounded" @click="cycleStatus(data, date)">
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