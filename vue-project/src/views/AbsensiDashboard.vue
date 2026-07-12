<template>
  <div v-if="dashboard" class="p-6 space-y-6">

    <!-- Row Bawah: Chart Halaman -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

      <Card>
        <template #title>
          <div class="flex items-center justify-between gap-4">
            <span class="text-base font-bold">Distribusi Status Absensi</span>

            <!-- Filter Tanggal Minimalis -->
            <DatePicker v-model="selectedDate" dateFormat="dd mm yy" showIcon iconDisplay="input" class="w-40"
              inputClass="text-xs p-2" />
          </div>
        </template>

        <template #content>
          <!-- Kondisi deteksi kosong Anda tetap berfungsi penuh di sini -->
          <div v-if="isPieChartEmpty"
            class="flex flex-col items-center justify-center py-12 text-surface-400 space-y-2">
            <i class="pi pi-inbox text-3xl"></i>
            <!-- Teks otomatis dinamis menyesuaikan keadaan -->
            <span class="text-sm">Belum ada data pegawai pada tanggal ini</span>
          </div>

          <div v-else class="flex justify-center">
            <Chart type="pie" :data="pieChartData" :options="pieChartOptions" @select="onPieSliceClick"
              class="w-full max-w-[320px] cursor-pointer" />
          </div>
        </template>
      </Card>
      <Card>
        <template #title>
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
            <span class="text-base font-bold">Tren Kehadiran Bulanan</span>
            <!-- Menampilkan bulan & tahun yang dinamis sesuai filter -->
            <span class="text-xs font-normal text-surface-400 font-sans tracking-wide">
              Periode: {{ bulanTahunFormatted }}
            </span>
          </div>
        </template>

        <template #content>
          <!-- Jika data tren bulan ini kosong / semuanya 0 -->
          <div v-if="isBarChartEmpty"
            class="flex flex-col items-center justify-center py-12 text-surface-400 space-y-2">
            <i class="pi pi-inbox text-3xl"></i>
            <span class="text-sm">Belum ada data tren kehadiran pada bulan ini</span>
          </div>

          <!-- Jika ada data, tampilkan Bar Chart -->
          <div v-else>
            <Chart type="bar" :data="barChartData" />
          </div>
        </template>
      </Card>


    </div>

    <!-- Row Atas: Ringkasan Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <Card>

        <template #title>
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
            <span class="text-base font-bold">Kehadiran</span>
            <!-- Menampilkan tanggal hari ini dengan warna abu-abu yang lebih soft -->
            <span class="text-xs font-normal text-surface-400 font-sans tracking-wide">
              {{ tanggalHariIniFormatted }}
            </span>
          </div>
        </template>
        <template #content>
          <!-- Jika data kosong (menggunakan checker data kosong yang sama) -->
          <div v-if="isPieChartEmpty"
            class="flex flex-col items-center justify-center py-4 text-surface-400 space-y-1 w-full">
            <i class="pi pi-inbox text-2xl"></i>
            <span class="text-sm">Belum ada data pegawai pada tanggal ini</span>
          </div>

          <!-- Jika ada data, tampilkan statistik normal -->
          <div v-else class="flex items-center justify-between w-full">
            <div>
              <h2 class="text-3xl font-bold">{{ dashboard.cards?.persentase_kehadiran || 0 }}%</h2>
              <p class="text-sm text-surface-400">
                {{ dashboard.cards?.jumlah_hadir || 0 }} dari {{ dashboard.cards?.total_pegawai || 0 }} pegawai
              </p>
            </div>
            <i class="pi pi-check-circle text-4xl text-green-500"></i>
          </div>
        </template>
      </Card>
      <Card @click="openSakitCutiTkModal"
        class="cursor-pointer transition-all duration-200 hover:shadow-md hover:border-surface-300">
        <template #title>
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
            <div class="flex items-center gap-2">
              <span class="text-base font-bold">Tidak Hadir</span>
              <!-- Panah kecil indikator klik jika ada data pegawai tidak hadir -->
              <i v-if="listSakitCutiTkHariIni.length > 0" class=" text-xs text-surface-400"></i>
            </div>
            <!-- Menampilkan tanggal dinamis sesuai filter Anda -->
            <span class="text-xs font-normal text-surface-400 font-sans tracking-wide">
              {{ tanggalHariIniFormatted }}
            </span>
          </div>
        </template>

        <template #content>
          <div class="text-2xl font-bold text-orange-600">
            {{ dashboard?.cards?.total_izin_sakit_cuti || 0 }} <span
              class="text-sm font-normal text-surface-500">Pegawai</span>
          </div>
        </template>
      </Card>
    </div>



    <!-- Chart Memanjang 1 Kolom Penuh -->
    <div class="mt-6 w-full">
      <Card>
        <template #title>
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
            <span class="text-base font-bold">Rekap Izin Pegawai</span>
            <span class="text-xs font-normal text-surface-400 font-sans tracking-wide">
              Periode: {{ bulanTahunFormatted }}
            </span>
          </div>
        </template>

        <template #content>
          <!-- Jika tidak ada record izin sepanjang bulan ini -->
          <div v-if="isBarIzinChartEmpty"
            class="flex flex-col items-center justify-center py-12 text-surface-400 space-y-2">
            <i class="pi pi-inbox text-3xl"></i>
            <span class="text-sm">Tidak ada pegawai yang mengajukan izin pada bulan ini</span>
          </div>

          <!-- Jika ada data izin, render Chart memanjang -->
          <div v-else class="w-full h-[350px]">
            <Chart type="bar" :data="barIzinChartData" :options="barIzinChartOptions" @select="onIzinBarClick"
              class="w-full h-full cursor-pointer" />
          </div>
        </template>
      </Card>
    </div>


    <!-- Dialog Pop-up List Nama Pegawai -->
    <Dialog v-model:visible="listModalVisible" modal :header="'Daftar Pegawai: ' + selectedStatusLabel"
      :style="{ width: '90vw', maxWidth: '400px' }">
      <div class="mt-2">
        <ul v-if="filteredPegawaiList.length > 0"
          class="divide-y divide-surface-200 border border-surface-200 rounded-lg max-h-60 overflow-y-auto">
          <li v-for="(nama, idx) in filteredPegawaiList" :key="idx"
            class="px-4 py-2.5 text-sm text-surface-800 hover:bg-surface-50">
            {{ idx + 1 }}. {{ nama }}
          </li>
        </ul>
        <div v-else class="text-center py-6 text-sm text-surface-400">
          Tidak ada pegawai dengan status ini.
        </div>
      </div>

    </Dialog>



    <!-- MODAL POPUP: Detail Tanggal Izin Pegawai -->
    <Dialog v-model:visible="izinModalVisible" :header="`Detail Tanggal`" modal class="w-full max-w-md mx-4">


      <div class="py-2">
        <p class="text-sm text-surface-500 mb-3">
          Daftar Izin <span class="font-semibold text-surface-900">{{
            selectedPegawaiIzin
          }}</span> pada periode {{ bulanTahunFormatted }}:
        </p>

        <!-- List tanggal dengan bullet icon rapi -->
        <ul class="space-y-2 max-h-[250px] overflow-y-auto pr-1">
          <li v-for="(tanggal, idx) in filteredIzinDates" :key="idx"
            class="flex items-center gap-2 p-2 bg-surface-50 rounded-lg text-sm font-medium border border-surface-100">
            <i class="pi pi-calendar text-orange-500"></i>
            <span>{{ tanggal }}</span>
          </li>
        </ul>
      </div>
    </Dialog>

    <!-- MODAL DETAIL PEGAWAI SAKIT / CUTI / TK PADA TANGGAL FILTER -->
    <Dialog v-model:visible="sakitCutiTkModalVisible" :header="`Daftar Tidak Hadir - ${tanggalHariIniFormatted}`" modal
      class="w-full max-w-md mx-4">
      <div class="py-2">
        <p class="text-sm text-surface-500 mb-4">
          Berikut adalah daftar pegawai yang tidak masuk kerja pada tanggal yang Anda pilih:
        </p>

        <ul class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
          <li v-for="(pegawai, idx) in listSakitCutiTkHariIni" :key="idx"
            class="flex items-center justify-between p-3 bg-surface-50 rounded-lg text-sm border border-surface-100">
            <div class="flex items-center gap-2 font-medium text-surface-800">
              <i class="pi pi-user text-surface-400"></i>
              <span>{{ pegawai.nama }}</span>
            </div>

            <!-- Badge dengan warna dinamis sesuai status absennya -->
            <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="{
              'bg-amber-50 text-amber-700 border border-amber-200': pegawai.status === 'Izin',
              'bg-red-50 text-red-700 border border-red-200': pegawai.status === 'Sakit',
              'bg-blue-50 text-blue-700 border border-blue-200': pegawai.status === 'Cuti',
              'bg-red-500 text-white border border-red-600': pegawai.status === 'Alpha' // 🌟 Warna merah tegas untuk Alpha
            }">
              {{ pegawai.status }}
            </span>
          </li>
        </ul>
      </div>
    </Dialog>


  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useAbsensiStore } from "@/stores/absensi";
import Chart from 'primevue/chart';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog'; // Pastikan di-import jika belum terdaftar global

import DatePicker from 'primevue/datepicker'; // atau 'primevue/calendar' tergantung versi PrimeVue Anda

// --- STATE & DATA UNTUK CHART IZIN BULANAN ---
const izinModalVisible = ref(false);
const selectedPegawaiIzin = ref('');
const filteredIzinDates = ref([]);
// State untuk membuka/menutup modal khusus Card Sakit/Cuti/TK
const sakitCutiTkModalVisible = ref(false);

// Menggabungkan data dari distribusi_status (Pie Chart) untuk Card ini
const listSakitCutiTkHariIni = computed(() => {
  const distribusi = dashboard.value?.distribusi_status;
  if (!distribusi) return [];

  // Cari index masing-masing status dari array labels backend ['Hadir', 'Izin', 'Sakit', 'Cuti']
  const indexIzin = distribusi.labels.findIndex(l => l.toLowerCase() === 'izin');
  const indexSakit = distribusi.labels.findIndex(l => l.toLowerCase() === 'sakit');
  const indexCuti = distribusi.labels.findIndex(l => l.toLowerCase() === 'cuti');
  const indexAlpha = distribusi.labels.findIndex(l => l.toLowerCase() === 'alpha');
  console.log(indexAlpha)


  let gabunganList = [];


  // Masukkan pegawai yang Izin jika ada
  if (indexIzin !== -1 && distribusi.pegawai_list[indexIzin]) {
    distribusi.pegawai_list[indexIzin].forEach(nama => {
      gabunganList.push({ nama, status: 'Izin' });
    });
  }

  // Masukkan pegawai yang Sakit jika ada
  if (indexSakit !== -1 && distribusi.pegawai_list[indexSakit]) {
    distribusi.pegawai_list[indexSakit].forEach(nama => {
      gabunganList.push({ nama, status: 'Sakit' });
    });
  }

  // Masukkan pegawai yang Cuti jika ada
  if (indexCuti !== -1 && distribusi.pegawai_list[indexCuti]) {
    distribusi.pegawai_list[indexCuti].forEach(nama => {
      gabunganList.push({ nama, status: 'Cuti' });
    });
  }

  // Masukkan pegawai yang Alpha jika ada
  if (indexAlpha !== -1 && distribusi.pegawai_list[indexAlpha]) {
    distribusi.pegawai_list[indexAlpha].forEach(nama => {
      gabunganList.push({ nama, status: 'Alpha' });
    });
  }

  return gabunganList;
});

// Fungsi pemicu klik pada Card
const openSakitCutiTkModal = () => {
  if (listSakitCutiTkHariIni.value.length > 0) {
    sakitCutiTkModalVisible.value = true;
  }
};

const store = useAbsensiStore();
const dashboard = ref(null);
const selectedDate = ref(new Date());

// Fungsi bantu untuk mengubah objek Date JS menjadi format Y-m-d untuk API
const formatDateToYmd = (date) => {
  if (!date) return '';
  const yyyy = date.getFullYear();
  const mm = String(date.getMonth() + 1).padStart(2, '0');
  const dd = String(date.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
};

// 2. Buat fungsi fetch data yang menerima parameter tanggal
const loadDashboardData = async () => {
  const tglParam = formatDateToYmd(selectedDate.value);
  // Kirim parameter tanggal ke backend agar data Cards & Pie Chart menyesuaikan tanggal tersebut
  dashboard.value = await store.fetchDashboardStats({ tanggal: tglParam });
};

// --- STATE MODAL DETAIL ---
const listModalVisible = ref(false);
const selectedStatusLabel = ref('');
const filteredPegawaiList = ref([]);

onMounted(async () => {

  loadDashboardData();
});

// Jadikan computed agar otomatis memantau dan memformat variabel selectedDate
const tanggalHariIniFormatted = computed(() => {
  if (!selectedDate.value) return '';

  return new Date(selectedDate.value).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

// Mengambil hanya Bulan dan Tahun dari tanggal yang dipilih di filter
const bulanTahunFormatted = computed(() => {
  if (!selectedDate.value) return '';

  return new Date(selectedDate.value).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long'
  });
});

watch(selectedDate, () => {
  loadDashboardData();
});

// --- CHECKER DATA KOSONG ---
const isPieChartEmpty = computed(() => {
  const statusData = dashboard.value?.distribusi_status?.data || [];
  if (statusData.length === 0) return true;
  // Jika isi datanya ada tapi 0 semua ([0, 0, 0, 0]), anggap kosong
  return statusData.reduce((acc, curr) => acc + curr, 0) === 0;
});

// Checker untuk mendeteksi apakah data Bar Chart kosong
const isBarChartEmpty = computed(() => {
  // Pastikan data tren ada
  const hadirData = dashboard.value?.chart_tren?.hadir || [];
  const tidakHadirData = dashboard.value?.chart_tren?.tidak_hadir || [];

  // Jika tidak ada array data sama sekali
  if (hadirData.length === 0 && tidakHadirData.length === 0) return true;

  // Hitung total dari semua nilai di dalam array hadir dan tidak hadir
  const totalHadir = hadirData.reduce((sum, val) => sum + Number(val), 0);
  const totalTidakHadir = tidakHadirData.reduce((sum, val) => sum + Number(val), 0);

  // Jika jumlah total semuanya adalah 0, berarti data bulan ini kosong
  return (totalHadir + totalTidakHadir) === 0;
});

const barChartData = computed(() => ({
  labels: dashboard.value?.chart_tren?.labels || [],
  datasets: [
    { label: 'Hadir', data: dashboard.value?.chart_tren?.hadir || [], backgroundColor: '#22c55e' },
    { label: 'Tidak Hadir', data: dashboard.value?.chart_tren?.tidak_hadir || [], backgroundColor: '#ef4444' }
  ]
}));

const pieChartData = computed(() => ({
  labels: dashboard.value?.distribusi_status?.labels || [],
  datasets: [{
    data: dashboard.value?.distribusi_status?.data || [],
    backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#8b5cf6']
  }]
}));

// Matikan animasi hover default bawaan Chart.js agar respons klik PrimeVue lebih instan
const pieChartOptions = ref({
  plugins: {
    legend: {
      position: 'bottom'
    }
  }
});

// --- FUNGSI DETEKSI KLIK PADA SLICE PIE ---
const onPieSliceClick = (event) => {
  // Ambil indeks slice yang diklik (0 = Hadir, 1 = Izin, dst tergantung susunan label dari backend)
  const index = event.element?.index;
  if (index === undefined || index === null) return;

  const label = dashboard.value?.distribusi_status?.labels[index];
  selectedStatusLabel.value = label;

  // Mengambil daftar nama pegawai berdasarkan indeks status dari backend
  // Asumsi di JSON backend menyertakan struktur array nama: distribusi_status.pegawai_list: [ ['Budi', 'Andi'], ['Siti'], [] ]
  const allPegawaiData = dashboard.value?.distribusi_status?.pegawai_list || [];
  filteredPegawaiList.value = allPegawaiData[index] || [];

  // Buka dialog popup list nama
  listModalVisible.value = true;
};



// 1. Mapping Data untuk Bar Chart Izin Pegawai
const barIzinChartData = computed(() => {
  const source = dashboard.value?.rekap_izin_pegawai;
  return {
    labels: source?.labels || [],
    datasets: [
      {
        label: 'Jumlah Izin',
        backgroundColor: '#f97316', // Warna orange cerah
        borderColor: '#f97316',
        data: source?.data || [],
        borderWidth: 1,
        borderRadius: 4
      }
    ]
  };
});

// 2. Checker untuk mendeteksi apakah tidak ada data izin sama sekali bulan ini
const isBarIzinChartEmpty = computed(() => {
  const dataIzin = dashboard.value?.rekap_izin_pegawai?.data || [];
  if (dataIzin.length === 0) return true;
  // Jika ada pegawai tapi total izin semuanya 0, anggap kosong
  return dataIzin.reduce((sum, val) => sum + Number(val), 0) === 0;
});

// 3. Konfigurasi Opsi Chart Izin (Kustomisasi Tooltip / Grid jika perlu)
const barIzinChartOptions = ref({
  plugins: {
    legend: { display: false } // Sembunyikan label kotak atas karena hanya ada 1 warna dataset
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: { stepSize: 1 },
      grace: 1 // Karena jumlah kali izin pasti angka bulat (integer)
    }
  }
});

// 4. Handle ketika batang/bar chart pegawai diklik
const onIzinBarClick = (event) => {
  if (!event.element) return;
  const index = event.element.index;
  const source = dashboard.value?.rekap_izin_pegawai;

  if (source) {
    selectedPegawaiIzin.value = source.labels[index];
    filteredIzinDates.value = source.detail_tanggal[index] || [];

    // Buka modal detail tanggal
    izinModalVisible.value = true;
  }
};
</script>