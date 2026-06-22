<template>
  <div v-if="dashboard" class="p-6 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <Card>
        <template #content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500">Kehadiran Hari Ini</p>
              <h2 class="text-3xl font-bold">{{ dashboard.cards.persentase_keh/absensi/dashboard-statsadiran }}%</h2>
              <p class="text-sm text-gray-400">{{ dashboard.cards.jumlah_hadir }} dari {{ dashboard.cards.total_pegawai }} pegawai</p>
            </div>
            <i class="pi pi-check-circle text-4xl text-green-500"></i>
          </div>
        </template>
      </Card>
      <Card>
        <template #content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-500">Absen Izin/Sakit/Cuti</p>
              <h2 class="text-3xl font-bold">{{ dashboard.cards.total_izin_sakit_cuti }}</h2>
              <p class="text-sm text-gray-400">Total pegawai tidak masuk hari ini</p>
            </div>
            <i class="pi pi-calendar-times text-4xl text-orange-500"></i>
          </div>
        </template>
      </Card>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
      <Card>
        <template #title>Tren Kehadiran Bulanan</template>
        <template #content>
          <Chart type="bar" :data="barChartData" />
        </template>
      </Card>

      <Card>
        <template #title>Distribusi Status Absensi</template>
        <template #content>
          <Chart type="pie" :data="pieChartData" />
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAbsensiStore } from "@/stores/absensi";
import Chart from 'primevue/chart'; // WAJIB ADA
import Card from 'primevue/card';   // Jika Anda pakai Card

const store = useAbsensiStore();
const dashboard = ref(null);

onMounted(async () => {
  // Asumsi action baru di Pinia untuk fetch data dashboard
  dashboard.value = await store.fetchDashboardStats();
});

const barChartData = computed(() => ({
  labels: dashboard.value.chart_tren.labels,
  datasets: [
    { label: 'Hadir', data: dashboard.value.chart_tren.hadir, backgroundColor: '#22c55e' },
    { label: 'Tidak Hadir', data: dashboard.value.chart_tren.tidak_hadir, backgroundColor: '#ef4444' }
  ]
}));

const pieChartData = computed(() => ({
  labels: dashboard.value.distribusi_status.labels,
  datasets: [{
    data: dashboard.value.distribusi_status.data,
    backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#8b5cf6']
  }]
}));
</script>