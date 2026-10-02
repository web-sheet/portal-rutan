<template>
    <div class="p-4 md:p-6 space-y-6 bg-slate-50 min-h-screen">
        <!-- Header -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="pi pi-objects-column text-primary text-xl md:text-2xl"></i>
                    Dashboard Fasilitas
                </h1>
                <p class="text-slate-500 text-xs md:text-sm mt-0.5">
                    Ringkasan statistik laporan kerusakan & perbaikan fasilitas
                </p>
            </div>

            <!-- Filter Dropdown Tahun -->
            <div class="flex items-center gap-2">
                <label for="year-select" class="text-xs font-semibold text-slate-600 flex items-center gap-1">
                    <i class="pi pi-calendar text-indigo-600"></i>
                    Tahun:
                </label>
                <select id="year-select" v-model="selectedYear" @change="fetchDashboardData"
                    class="bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 font-medium cursor-pointer">
                    <option v-for="year in yearOptions" :key="year" :value="year">
                        Tahun {{ year }}
                    </option>
                </select>
            </div>



            <Button label="Refresh Data" icon="pi pi-refresh" severity="secondary" outlined :loading="loading"
                @click="fetchDashboardData" />
        </div>

        <!-- State Loading Skeleton -->
        <div v-if="loading" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Skeleton height="100px" class="rounded-xl" v-for="i in 3" :key="i" />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Skeleton height="300px" class="rounded-xl lg:col-span-2" />
                <Skeleton height="300px" class="rounded-xl" v-for="i in 2" :key="i" />
            </div>
        </div>

        <!-- State Data Ready -->
        <template v-else-if="stats">
            <!-- 1. Summary Cards (Menggunakan PrimeVue Card & Icons) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="border border-slate-200 shadow-xs">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total
                                    Laporan</span>
                                <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ stats.summary.total }}</h3>
                            </div>
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                                <i class="pi pi-file-edit text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="border border-slate-200 shadow-xs">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Selesai
                                    Ditangani</span>
                                <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ stats.summary.completed }}
                                </h3>
                            </div>
                            <div
                                class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                                <i class="pi pi-check-circle text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="border border-slate-200 shadow-xs">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Menunggu
                                    (Pending)</span>
                                <h3 class="text-3xl font-extrabold text-slate-800 mt-1">{{ stats.summary.pending }}</h3>
                            </div>
                            <div
                                class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                                <i class="pi pi-clock text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- 2. Charts Section (Chart.js Native Canvas Integration) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Grafik Tren Bulanan -->
                <!-- Tren Laporan Bulanan -->
                <Card class="border border-slate-200 shadow-xs lg:col-span-2">
                    <template #title>
                        <div class="flex items-center gap-2 text-base font-bold text-slate-800">
                            <i class="pi pi-chart-line text-indigo-600"></i>
                            Tren Laporan Masuk Per Bulan
                        </div>
                    </template>
                    <template #content>
                        <!-- DIV pembungkus WAJIB ada position relative dan height -->
                        <div class="relative h-64 w-full">
                            <canvas ref="monthlyChartCanvas"></canvas>
                        </div>
                    </template>
                </Card>
                <!-- Grafik per Jenis Fasilitas -->
                <Card class="border border-slate-200 shadow-xs">
                    <template #title>
                        <div class="flex items-center gap-2 text-base font-bold text-slate-800">
                            <i class="pi pi-chart-bar text-cyan-600"></i>
                            Laporan per Jenis Fasilitas
                        </div>
                    </template>
                    <template #content>
                        <div class="h-64 relative">
                            <canvas ref="facilityChartCanvas"></canvas>
                        </div>
                    </template>
                </Card>

                <!-- Grafik per Lokasi -->
                <Card class="border border-slate-200 shadow-xs">
                    <template #title>
                        <div class="flex items-center gap-2 text-base font-bold text-slate-800">
                            <i class="pi pi-chart-pie text-amber-600"></i>
                            Laporan per Lokasi Area
                        </div>
                    </template>
                    <template #content>
                        <div class="h-64 relative flex justify-center">
                            <canvas ref="locationChartCanvas"></canvas>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Galeri Foto Before / After dengan PrimeVue 4 Carousel -->
            <Card class="border border-slate-200 shadow-xs">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-base font-bold text-slate-800">
                            <i class="pi pi-images text-purple-600"></i>
                            Galeri Hasil Perbaikan (Before vs After)
                        </div>
                        <Tag value="10 Terakhir" severity="info" class="text-xs" />
                    </div>
                </template>

                <template #content>
                    <!-- PASTIKAN v-if mengecek stats.gallery agar Carousel baru dipasang saat data SUDAH ADA -->
                    <div v-if="stats && stats.gallery && stats.gallery.length > 0">
                        <Carousel :value="stats.gallery" :numVisible="3" :numScroll="1"
                            :responsiveOptions="responsiveOptions" :circular="true" :autoplayInterval="4000">
                            <template #item="slotProps">
                                <div
                                    class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 hover:bg-white hover:shadow-sm transition-all m-2">
                                    <!-- Info Lokasi & Fasilitas -->
                                    <div class="flex items-center justify-between mb-2 text-xs">
                                        <span class="font-bold text-slate-700 truncate max-w-[140px]">
                                            {{ slotProps.data.facility_type?.name || 'Fasilitas' }}
                                        </span>
                                        <span class="text-slate-500 flex items-center gap-1 text-[11px]">
                                            <i class="pi pi-map-marker text-[10px]"></i>
                                            {{ slotProps.data.location?.name || '-' }}
                                        </span>
                                    </div>

                                    <!-- Gambar Before / After -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="space-y-1">
                                            <Tag value="Sebelum" severity="danger"
                                                class="!text-[10px] !py-0.5 !px-1.5 w-full text-center" />
                                            <img :src="getStorageUrl(slotProps.data.photo_before)" alt="Foto Sebelum"
                                                class="w-full h-28 object-cover rounded-lg border border-slate-200 bg-slate-200"
                                                loading="lazy" />
                                        </div>
                                        <div class="space-y-1">
                                            <Tag value="Sesudah" severity="success"
                                                class="!text-[10px] !py-0.5 !px-1.5 w-full text-center" />
                                            <img :src="getStorageUrl(slotProps.data.photo_after)" alt="Foto Sesudah"
                                                class="w-full h-28 object-cover rounded-lg border border-slate-200 bg-slate-200"
                                                loading="lazy" />
                                        </div>
                                    </div>

                                    <div
                                        class="text-[11px] text-slate-400 text-right mt-2 flex items-center justify-end gap-1">
                                        <i class="pi pi-calendar text-[10px]"></i>
                                        {{ formatDate(slotProps.data.handled_at) }}
                                    </div>
                                </div>
                            </template>
                        </Carousel>
                    </div>

                    <div v-else class="text-center py-12 text-slate-400">
                        <i class="pi pi-inbox text-4xl mb-2 block"></i>
                        <p class="text-sm">Belum ada dokumentasi perbaikan selesai.</p>
                    </div>
                </template>
            </Card>

        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import axios from 'axios';
import { Chart, registerables } from 'chart.js';

// PrimeVue Components (Otomatis / Import sesuai setup kamu)
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Skeleton from 'primevue/skeleton';
import Carousel from 'primevue/carousel';

// 1. PASTIKAN CAROUSEL DI-IMPORT DARI PRIMEVUE


// Setup Breakpoint Responsif Carousel PrimeVue 4
const responsiveOptions = ref([
    {
        breakpoint: '1400px',
        numVisible: 3,
        numScroll: 1
    },
    {
        breakpoint: '1024px',
        numVisible: 2,
        numScroll: 1
    },
    {
        breakpoint: '768px',
        numVisible: 1,
        numScroll: 1
    }
]);

Chart.register(...registerables);

// Helper storage URL khusus production (Sesuai kesepakatan sebelumnya)
const getStorageUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;

      const storageBase = 'https://rtnpondokbambu.my.id/pondokbambu/backend/public/storage';
    // const storageBase = 'http://localhost:8000/storage';

    return `${storageBase}/${path.replace(/^\/?(storage\/)?/, '')}`;
};

const stats = ref(null);
const loading = ref(true);

// Refs HTML Canvas Chart
const monthlyChartCanvas = ref(null);
const facilityChartCanvas = ref(null);
const locationChartCanvas = ref(null);

// Chart Instances
let monthlyChart = null;
let facilityChart = null;
let locationChart = null;

const currentYear = new Date().getFullYear();
const selectedYear = ref(currentYear);

// Opsi Tahun: 3 tahun ke belakang + tahun ini + 5 tahun ke depan
const yearOptions = computed(() => {
  const years = [];
  const startYear = currentYear - 0;
  const endYear = currentYear + 5;
  for (let y = startYear; y <= endYear; y++) {
    years.push(y);
  }
  return years;
});

const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const fetchDashboardData = async () => {
    loading.value = true;
    try {
        const apiUrl = import.meta.env.VITE_API_URL || 'https://rtnpondokbambu.my.id/pondokbambu/backend/public/api';
        // const apiUrl = 'http://localhost:8000/api';
        
        // Mengirimkan parameter ?year=...
        const response = await axios.get(`${apiUrl}/dashboard-stats`, {
            params: { year: selectedYear.value }
        });
        stats.value = response.data;

        await nextTick();
        renderCharts();
    } catch (error) {
        console.error('Gagal memuat data dashboard:', error);
    } finally {
        loading.value = false;
    }
};

const renderCharts = () => {
    if (!stats.value) return;

    // Hancurkan chart lama jika ada
    if (monthlyChart) monthlyChart.destroy();
    if (facilityChart) facilityChart.destroy();
    if (locationChart) locationChart.destroy();

    // Pastikan canvas ada
    if (!monthlyChartCanvas.value) {
        // Jika masih null, coba lakukan nextTick sekali lagi
        nextTick(() => renderCharts());
        return;
    }

    // 1. CHART BULANAN
    const labels = stats.value.chart_monthly.map(i => monthNames[i.month - 1]);
    const totals = stats.value.chart_monthly.map(i => i.total);

    monthlyChart = new Chart(monthlyChartCanvas.value, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: `Laporan Masuk (${selectedYear.value})`,
                data: totals,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.15)',
                fill: true,
                tension: 0.3,
                borderWidth: 3,
                pointRadius: 5,
                pointBackgroundColor: '#4f46e5'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    // 2. CHART FASILITAS (Dibuat Horizontal dengan indexAxis: 'y' agar teks panjang tidak terpotong)
    if (facilityChartCanvas.value && stats.value.chart_by_facility) {
        facilityChart = new Chart(facilityChartCanvas.value, {
            type: 'bar',
            data: {
                labels: stats.value.chart_by_facility.map(i => i.name),
                datasets: [{
                    label: 'Total',
                    data: stats.value.chart_by_facility.map(i => i.total),
                    backgroundColor: '#06b6d4',
                    borderRadius: 6
                }]
            },
            options: { 
                indexAxis: 'y', // Mencegah label terpotong jika variasi fasilitas bertambah
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                }
            }
        });
    }

    // 3. CHART LOKASI
    if (locationChartCanvas.value && stats.value.chart_by_location) {
        locationChart = new Chart(locationChartCanvas.value, {
            type: 'doughnut',
            data: {
                labels: stats.value.chart_by_location.map(i => i.name),
                datasets: [{
                    data: stats.value.chart_by_location.map(i => i.total),
                    backgroundColor: ['#f59e0b', '#10b981', '#ec4899', '#8b5cf6', '#3b82f6']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
};
onMounted(() => {
    fetchDashboardData();
});
</script>