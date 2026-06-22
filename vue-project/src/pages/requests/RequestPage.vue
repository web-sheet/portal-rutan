<template>
  <div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">
    <Toast />

    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-4">
      <div class="flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
          class="w-8 h-8 text-slate-700 shrink-0">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
        </svg>
        <div>
          <h2 class="text-2xl font-bold text-slate-900">Request Barang</h2>
          <p class="text-sm text-slate-500">Pelacakan alur persetujuan dan pengajuan inventaris gudang rutan</p>
        </div>
      </div>

      <Button label="Ajukan Barang" severity="success"
        class="w-auto p-button-sm flex items-center justify-center gap-2 self-end sm:self-auto" @click="openDialog">
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
        </template>
      </Button>
    </div>

    <div class="card bg-white shadow-sm border border-slate-200 rounded-xl overflow-hidden">
      <DataTable :value="store.requests" :loading="store.loading" paginator :rows="10" stripedRows
        responsiveLayout="scroll" class="p-datatable-sm text-sm">
        <Column field="employee_name" header="Nama Pegawai" sortable class="font-medium text-slate-800" />
        <Column field="division" header="Jabatan" sortable />
        <Column field="item_name" header="Barang" sortable />
        <Column field="stock_requested" header="Jumlah" class="text-center" headerClass="text-center" />
        <Column field="final_approved_stock" header="Disetujui" class="text-center" headerClass="text-center">
          <template #body="{ data }">
            <span class="font-semibold text-emerald-600">{{ data.final_approved_stock ?? '-' }}</span>
          </template>
        </Column>
        <!-- <Column header="Status">
          <template #body="{ data }">
            <Tag :value="data.status" :severity="statusColor(data.status)" class="uppercase font-bold text-[10px]" />
          </template>
        </Column> -->
        <Column header="Status" style="min-width: 150px;">
          <template #body="{ data }">
            <Tag :value="getStatusLabel(data.status)" :severity="statusColor(data.status)"
              class="text-[11px] font-semibold" />
          </template>
        </Column>
        <Column field="formatted_created_at" header="Tanggal" sortable />
        <Column header="Detail" class="text-center" headerClass="text-center">
          <template #body="{ data }">
            <Button severity="info" text rounded v-tooltip.top="'Lihat Timeline'" @click="openTimeline(data)">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </Button>
          </template>
        </Column>
      </DataTable>
    </div>

    <Dialog v-model:visible="dialog" header="📝 Ajukan Permintaan Barang" modal
      :breakpoints="{ '960px': '75vw', '641px': '92vw' }" :style="{ width: '450px' }" class="p-fluid">
      <div class="flex flex-col gap-4 mt-2">

        <div class="flex flex-col gap-1">
          <label class="text-sm font-semibold text-slate-700">Nama Pegawai</label>
          <Select v-model="selectedPegawai" :options="pegawaiList" optionLabel="nama" placeholder="Pilih Pegawai Rutan"
            filter />
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-semibold text-slate-700">Jabatan</label>
          <InputText v-model="form.division" placeholder="Akan terisi otomatis" readonly
            class="bg-slate-50 cursor-not-allowed" />
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-semibold text-slate-700">Pilih Barang</label>
          <Select v-model="selectedItem" :options="store.items" optionLabel="name" placeholder="Pilih Logistik"
            filter />
        </div>

        <div v-if="selectedItem" class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs space-y-1">
          <p class="text-slate-600"><b class="text-slate-800">Kategori:</b> {{ selectedItem.category }}</p>
          <p class="text-slate-600"><b class="text-slate-800">Sisa Stok Gudang:</b> {{ selectedItem.stock }} unit</p>
        </div>

        <div v-if="stockNotEnough"
          class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            class="w-4 h-4 text-red-600 shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
          <span>Stok di gudang tidak mencukupi untuk jumlah yang Anda minta.</span>
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-semibold text-slate-700">Jumlah Permintaan</label>
          <InputNumber v-model="form.stock_requested" placeholder="Kuantitas barang" showButtons :min="1" />
        </div>

        <div class="pt-2">
          <Button severity="success" :disabled="stockNotEnough" @click="submit"
            class="w-full flex items-center justify-center gap-2" label="Kirim Request">
            <template #icon>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
              </svg>
            </template>
          </Button>
        </div>
      </div>
    </Dialog>

    <Dialog v-model:visible="timelineDialog" modal header="⏳ Alur Riwayat Permohonan" :breakpoints="{ '641px': '95vw' }"
      :style="{ width: '480px' }">
      <div v-if="selectedRequest" class="mt-4 px-1">
        <Timeline :value="getTimelineData(selectedRequest)" class="customized-timeline">
          <template #marker="slotProps">
            <span class="flex w-8 h-8 items-center justify-center text-white rounded-full shadow-md"
              :class="slotProps.item.color">
              <component :is="slotProps.item.icon" class="w-4 h-4" />
            </span>
          </template>
          <template #content="slotProps">
            <div class="bg-slate-50 border border-slate-200 p-3 rounded-lg mb-4 ml-2">
              <p class="font-bold text-slate-800 text-sm">{{ slotProps.item.status }}</p>
              <p class="text-xs text-slate-500 mt-0.5">{{ slotProps.item.date }}</p>
              <p v-if="slotProps.item.by" class="text-xs text-slate-400 mt-1 italic">Oleh: {{ slotProps.item.by }}</p>
            </div>
          </template>
        </Timeline>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed, h } from "vue";
import { useRequestStore } from "@/stores/request";
import api from "@/api/axios";

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select'; // Menggunakan Select untuk Barang & Pegawai
import Timeline from 'primevue/timeline';
import Toast from 'primevue/toast';

const timelineDialog = ref(false);
const selectedRequest = ref(null);
const store = useRequestStore();
const dialog = ref(false);
const selectedItem = ref(null);

// Penampung data list pegawai rutan global
const pegawaiList = ref([]);
const selectedPegawai = ref(null);

const form = ref({ employee_name: "", division: "", item_id: null, stock_requested: 1 });



onMounted(() => {
  // Menjalankan semua request secara paralel (bersamaan) tanpa saling mengantre
  Promise.all([
    store.fetchItems(),
    store.fetchRequests(),
    fetchAllPegawai()
  ]).catch(error => {
    console.error("Ada request yang gagal dimuat di awal", error);
  });
});


const fetchAllPegawai = async () => {
  try {
    const response = await api.get('/pegawai');

    if (Array.isArray(response.data)) {
      pegawaiList.value = response.data;
    } else if (response.data && typeof response.data === 'object') {
      // Jika Laravel tidak sengaja mengembalikan format Object, konversi ke Array
      pegawaiList.value = Object.values(response.data);
    } else {
      pegawaiList.value = [];
    }
  } catch (error) {
    console.error("Gagal memuat list data pegawai", error);
    pegawaiList.value = []; // Set ke array kosong jika API eror agar dropdown tidak crash
  }
};
// MENGINTIP PILIHAN PEGAWAI: Set Nama dan Jabatan otomatis ke form
watch(selectedPegawai, (newValue) => {
  if (newValue) {
    form.value.employee_name = newValue.nama;
    form.value.division = newValue.jabatan;
  } else {
    form.value.employee_name = "";
    form.value.division = "";
  }
});

watch(selectedItem, (value) => { form.value.item_id = value?.id ?? null; });
const openDialog = () => { dialog.value = true; };
const stockNotEnough = computed(() => selectedItem.value ? form.value.stock_requested > selectedItem.value.stock : false);

const submit = async () => {
  await store.submitRequest(form.value);
  dialog.value = false;
  form.value = { employee_name: "", division: "", item_id: null, stock_requested: 1 };
  selectedItem.value = null;
  selectedPegawai.value = null; // reset dropdown pegawai
  await store.fetchRequests();
};

const statusColor = (status) => {
  // Kuning untuk fase antrean (Pending)
  if (status === "pending") return "warn";

  // Biru untuk fase verifikasi internal (Kaur)
  if (status === "approved_kaur") return "info";

  // Hijau untuk fase final (Kasi/Selesai)
  if (status === "approved_kasi" || status === "completed") return "success";

  // Merah untuk penolakan
  if (status === "rejected") return "danger";

  // Default jika status tidak dikenali
  return "secondary";
};

const openTimeline = (data) => {
  selectedRequest.value = data;
  timelineDialog.value = true;
};

// Heroicons render untuk komponen Timeline
const PaperAirplaneIcon = h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '2', stroke: 'currentColor' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5' })]);
const CheckIcon = h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '2', stroke: 'currentColor' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'm4.5 12.75 6 6 9-13.5' })]);
const XCircleIcon = h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '2', stroke: 'currentColor' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' })]);

const getTimelineData = (req) => {
  const data = [{ status: 'Menuggu Persetujuan Kaur', date: req.formatted_requested_at || req.formatted_created_at, icon: PaperAirplaneIcon, color: 'bg-blue-500' }];
  if (req.approved_kaur_at || req.formatted_approved_kaur_at) { data.push({ status: 'Menunggu Persetujuan Kasi', date: req.formatted_approved_kaur_at, by: req.formatted_approved_kaur_by, icon: CheckIcon, color: 'bg-amber-500' }); }
  if (req.formatted_approved_kasi_at) { data.push({ status: 'Selesai', date: req.formatted_approved_kasi_at, by: req.formatted_approved_kasi_by, icon: CheckIcon, color: 'bg-emerald-500' }); }
  if (req.formatted_rejected_at) { data.push({ status: 'Permohonan Ditolak', date: req.formatted_rejected_at, by: req.formatted_rejected_by, icon: XCircleIcon, color: 'bg-red-500' }); }
  return data;
};

/* Helper untuk label */
const getStatusLabel = (status) => {
  const labels = {
    'pending': 'Menunggu Persetujuan Kaur',
    'approved_kaur': 'Menunggu Persetujuan Kasi',
    'approved_kasi': 'Disetujui Kasi',
    'completed': 'Selesai',
    'rejected': 'Ditolak'
  };
  return labels[status] || status;
};
</script>