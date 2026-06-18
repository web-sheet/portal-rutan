<template>
  <!-- Gunakan v-if untuk memastikan data dashboard sudah dimuat agar tidak error saat akses properti -->
  <div v-if="dashboard" class="p-4 space-y-6">

    <!-- HEADER -->
    <div>
      <h1 class="text-3xl font-bold">Dashboard</h1>
      <p class="text-gray-500">Monitoring stok & permohonan barang</p>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
      <Card>
        <template #content>
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-500 text-sm">Total Barang</p>
              <h2 class="text-3xl font-bold mt-2">{{ dashboard.cards.total_items }}</h2>
            </div>
            <i class="pi pi-box text-4xl text-blue-500"></i>
          </div>
        </template>
      </Card>

      <Card>
        <template #content>
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-500 text-sm">Total Request</p>
              <h2 class="text-3xl font-bold mt-2">{{ dashboard.cards.total_requests }}</h2>
            </div>
            <i class="pi pi-send text-4xl text-green-500"></i>
          </div>
        </template>
      </Card>

      <Card>
        <template #content>
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-500 text-sm">Pending Approval</p>
              <h2 class="text-3xl font-bold mt-2">{{ dashboard.cards.pending_requests }}</h2>
            </div>
            <i class="pi pi-clock text-4xl text-orange-500"></i>
          </div>
        </template>
      </Card>

      <Card>
        <template #content>
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-500 text-sm">Stok Menipis</p>
              <h2 class="text-3xl font-bold mt-2">{{ dashboard.cards.low_stock }}</h2>
            </div>
            <i class="pi pi-exclamation-triangle text-4xl text-red-500"></i>
          </div>
        </template>
      </Card>
    </div>

    <!-- CHART + APPROVAL -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
      <Card>
        <template #title>Permohonan Bulanan</template>
        <template #content>
          <div class="h-72 w-full">
            <!-- Pastikan data ada sebelum render Chart -->
            <Chart v-if="chartData.datasets[0].data.length > 0" type="bar" :data="chartData" :options="chartOptions" />
            <div v-else class="flex items-center justify-center h-full text-gray-400">
              Memuat grafik...
            </div>
          </div>
        </template>
      </Card>

      <Card>
        <template #title>Approval Queue</template>
        <template #content>
          <DataTable :value="dashboard.approval_queue" responsiveLayout="scroll" stripedRows>
            <Column field="employee_name" header="Pegawai" />
            <Column field="item_name" header="Barang" />
            <Column field="stock_requested" header="Qty" />
            <Column header="Status">
              <template #body="{ data }">
                <Tag :value="data.status" severity="warn" />
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>

    <!-- BOTTOM SECTION -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
      <Card>
        <template #title>Stok Menipis</template>
        <template #content>
          <DataTable :value="dashboard.low_stock_items" responsiveLayout="scroll" stripedRows>
            <Column field="name" header="Barang" />
            <Column field="category" header="Kategori" />
            <Column header="Stock">
              <template #body="{ data }">
                <Tag :value="data.stock" severity="danger" />
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>

      <Card>
        <template #title>Top Requested Items</template>
        <template #content>
          <div v-for="item in dashboard.top_requested_items" :key="item.item_name" class="mb-4">
            <div class="flex justify-between mb-1">
              <span class="font-medium">{{ item.item_name }}</span>
              <span class="text-sm text-gray-500">{{ item.total }}</span>
            </div>
            <ProgressBar :value="item.total" :showValue="false" />
          </div>
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
// IMPORT KOMPONEN (Penting jika tidak pakai auto-import)
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';

import api from "@/api/axios";

/* STATE */
const dashboard = ref({
  cards: { total_items: 0, total_requests: 0, pending_requests: 0, low_stock: 0 },
  monthly_requests: [],
  approval_queue: [],
  low_stock_items: [],
  top_requested_items: [],
});

/* FETCH DASHBOARD */
const fetchDashboard = async () => {
  try {
    const res = await api.get("/dashboard");
    dashboard.value = res.data;
  } catch (error) {
    console.error("Gagal mengambil data:", error);
  }
};

onMounted(() => {
  fetchDashboard();
});

/* CHART DATA */
const chartData = computed(() => {
  const monthly = dashboard.value?.monthly_requests || [];
  return {
    labels: monthly.map(i => `Bulan ${i.month}`),
    datasets: [
      {
        label: "Permohonan",
        data: monthly.map(i => i.total),
        backgroundColor: "#3b82f6",
        borderRadius: 6
      },
    ],
  };
});

/* OPTIONS */
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  layout: {
    padding: 0 // Menghilangkan margin/padding internal bawaan dari Chart.js
  },
  plugins: {
    legend: {
      labels: { color: '#4b5563' }
    }
  },
  scales: {
    y: { beginAtZero: true }
  }
});
</script>