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
          <h2 class="text-2xl font-bold text-slate-900">Tabel Permohonan Barang</h2>
          <p class="text-sm text-slate-500">Pelacakan pengajuan peralatan Rutan Kelas I Pondok Bambu</p>
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
      <!-- <DataTable :value="store.requests" :loading="store.loading" paginator :rows="10" stripedRows
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
      </DataTable> -->

      <DataTable :value="groupedRequests" :loading="store.loading" paginator :rows="10" stripedRows
        responsiveLayout="scroll" class="p-datatable-sm text-sm">

        <Column field="employee_name" header="Nama Pegawai" class="font-medium text-slate-800" />
        <Column field="division" header="Jabatan" />

        <!-- Tampilkan jumlah barang saja dalam satu baris -->
        <Column header="Barang" class="text-center">
          <template #body="{ data }">
            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">
              {{ data.items_count }} Item
            </span>
          </template>
        </Column>

        <Column header="Status" style="min-width: 150px;">
          <template #body="{ data }">
            <Tag :value="getStatusLabel(data.status)" :severity="statusColor(data.status)"
              class="text-[11px] font-semibold" />
          </template>
        </Column>

        <Column field="formatted_created_at" header="Tanggal" />

        <Column header="Detail" class="text-center">
          <template #body="{ data }">
            <Button severity="info" text rounded @click="openTimeline(data)">
              <i class="pi pi-eye"></i>
            </Button>
          </template>
        </Column>
      </DataTable>
    </div>


    <Dialog v-model:visible="dialog"   modal :style="{ width: '500px' }"
      class="p-fluid">

      <template #header>
        <div class="w-full text-center font-semibold text-lg">
           Ajukan Permintaan Barang
        </div>
    </template>
      <div class="flex flex-col gap-4 mt-2">
        <!-- Header: Pegawai & Jabatan tetap di atas -->
        <div class="grid grid-cols-1 gap-4 pb-4 ">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold">Nama Pegawai</label>
            <Select v-model="selectedPegawai" :options="pegawaiList" optionLabel="nama" filter />
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold">Jabatan</label>
            <InputText :value="selectedPegawai?.jabatan" readonly class="bg-slate-50" />
          </div>
        </div>

        <!-- Area Pilih Barang -->
        <div class="space-y-3 bg-slate-50 p-3 rounded-lg border border-slate-200">
          <label class="text-sm font-semibold text-slate-700">Pilih Barang</label>
          <Select v-model="selectedItem" :options="availableItems" optionLabel="name" placeholder="Pilih Logistik"
            filter class="w-full" />

          <!-- Info Stok muncul di bawah Select saat barang dipilih -->
          <div v-if="selectedItem" class="flex justify-between items-center text-xs p-2 bg-white border rounded">
            <span class="text-slate-600">Stok Tersedia: <b>{{ selectedItem.stock }}</b></span>
            <InputNumber v-model="itemQty" placeholder="Qty" class="w-16" inputClass="w-16 text-center p-2" :min="1"
              :max="selectedItem.stock" />
          </div>

          <Button label="Tambah ke Daftar" icon="pi pi-plus" @click="addToCart"
            :disabled="!selectedItem || itemQty > selectedItem.stock" class="w-full" />
        </div>

        <!-- Tabel Daftar Barang yang akan diajukan -->
        <div v-if="cart.length > 0">
          <label class="text-sm font-semibold">Daftar Barang Diajukan:</label>
          <div class="border rounded-lg mt-2 overflow-hidden">
            <div v-for="(item, index) in cart" :key="index"
              class="flex justify-between items-center p-2 border-b last:border-0 text-sm">
              <span>{{ item.name }} ({{ item.qty }})</span>
              <Button icon="pi pi-trash" text severity="danger" @click="cart.splice(index, 1)" />
            </div>
          </div>
        </div>

        <Button severity="success" @click="submit" :disabled="cart.length === 0 || isLoading" :loading="isLoading"
          class="w-full flex items-center justify-center gap-2" label="Kirim">
          <!-- Ikon hanya muncul jika tidak sedang loading -->
          <template #icon v-if="!isLoading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
              stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
            </svg>
          </template>
        </Button>
      </div>
    </Dialog>


    <Dialog v-model:visible="timelineDialog" modal :breakpoints="{ '641px': '95vw' }" :style="{ width: '480px' }">
      <template #header>
        <div class="w-full text-center font-semibold text-lg">
         Detail Permohonan
        </div>
      </template>
      <div v-if="selectedRequest" class="mt-2">
        <!-- List Barang yang Diminta -->
        <div class="mb-6">
          <h4 class="text-sm font-semibold text-slate-700 mb-2">Daftar Barang:</h4>
          <div class="border rounded-lg overflow-hidden bg-white">
            <!-- Header Kolom -->
            <div class="grid grid-cols-12 gap-2 p-2 bg-slate-50 border-b text-xs font-bold text-slate-500 uppercase">
              <span class="col-span-6">Nama Barang</span>
              <span class="col-span-3 text-center">Diajukan</span>
              <span class="col-span-3 text-center">Disetujui</span>
            </div>

            <!-- List Item -->
            <div v-for="item in getItemsInRequest(selectedRequest)" :key="item.id"
              class="grid grid-cols-12 gap-2 items-center p-3 border-b last:border-0 text-sm">

              <span class="col-span-6 text-slate-700 font-medium">{{ item.item_name }}</span>

              <!-- Angka Diajukan (tengah) -->
              <span class="col-span-3 text-center text-slate-500">
                {{ item.stock_requested }}
              </span>

              <!-- Angka Disetujui (tengah) -->
              <span class="col-span-3 text-center font-bold text-green-600">
                {{ item.final_approved_stock ?? item.adjusted_stock_requested ?? '-' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <h4 class="text-sm font-semibold text-slate-700 mb-3">Status Riwayat:</h4>
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
import { useToast } from "primevue/usetoast";

const timelineDialog = ref(false);
const selectedRequest = ref(null);
const store = useRequestStore();
const dialog = ref(false);
const selectedItem = ref(null);
const toast = useToast();
const isLoading = ref(false); // Tambahkan ini di dekat definisi cart atau selectedPegawai

// Penampung data list pegawai rutan global
const pegawaiList = ref([]);
const selectedPegawai = ref(null);

const form = ref({ employee_name: "", division: "", item_id: null, stock_requested: 1 });

// Menghitung barang yang tersedia (dikurangi barang yang sudah ada di cart)
const availableItems = computed(() => {
  // Ambil semua ID yang sudah ada di cart
  const cartIds = cart.value.map(item => item.id);

  // Filter store.items agar hanya menampilkan yang ID-nya belum ada di cart
  return store.items.filter(item => !cartIds.includes(item.id));
});



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



const cart = ref([]);
const itemQty = ref(1);

const addToCart = () => {
  // 1. Validasi Stok
  if (itemQty.value > selectedItem.value.stock) {
    toast.add({ severity: 'error', summary: 'Stok Kurang', detail: 'Jumlah melebihi stok!' });
    return;
  }

  // 2. Push ke cart hanya SEKALI
  if (selectedItem.value) {
    cart.value.push({
      item_id: selectedItem.value.id, // ID untuk backend
      name: selectedItem.value.name,
      qty: itemQty.value,
      max_stock: selectedItem.value.stock, // Simpan stok untuk referensi
      ...selectedItem.value // Menyertakan sisa data objek jika diperlukan
    });

    // 3. Reset pilihan setelah berhasil masuk cart
    selectedItem.value = null;
    itemQty.value = 1;
  }
};
const submit = async () => {
  isLoading.value = true; // Aktifkan loading

  const payload = {
    employee_name: selectedPegawai.value.nama,
    division: selectedPegawai.value.jabatan,
    items: cart.value.map(item => ({
      item_id: item.item_id,
      qty: item.qty
    }))
  };

  try {
    await store.submitRequest(payload);

    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Permintaan barang telah diajukan', life: 3000 });
    dialog.value = false;
    cart.value = [];
    selectedPegawai.value = null;
    await store.fetchRequests();
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: err.response?.data?.message || 'Terjadi kesalahan', life: 5000 });
  } finally {
    isLoading.value = false; // Matikan loading saat selesai (sukses atau gagal)
  }
};
const statusColor = (status) => {
  if (status === "pending") return "warn";
  if (status === "approved_kaur") return "info";

  // Staf konfirmasi atau sudah selesai dianggap berhasil/selesai
  if (status === "confirmed_by_staff" || status === "completed") return "success";

  if (status === "rejected") return "danger";
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
  const data = [{
    status: 'Menunggu Persetujuan Kaur',
    date: req.formatted_requested_at || req.formatted_created_at,
    icon: PaperAirplaneIcon,
    color: 'bg-blue-500'
  }];

  if (req.approved_kaur_at || req.formatted_approved_kaur_at) {
    data.push({
      status: 'Barang sedang disiapkan',
      date: req.formatted_approved_kaur_at,
      by: req.formatted_approved_kaur_by,
      icon: CheckIcon,
      color: 'bg-amber-500'
    });
  }

  // Ganti dari approved_kasi ke confirmed_by_staff
  if (req.formatted_confirmed_by_staff_at) {
    data.push({
      status: 'Barang dikeluarkan',
      date: req.formatted_confirmed_by_staff_at,
      by: req.formatted_confirmed_by_staff_by, // Pastikan field ini ada di model
      icon: CheckIcon,
      color: 'bg-emerald-500'
    });
  }

  if (req.formatted_rejected_at) {
    data.push({
      status: 'Permohonan Ditolak',
      date: req.formatted_rejected_at,
      by: req.formatted_rejected_by,
      icon: XCircleIcon,
      color: 'bg-red-500'
    });
  }
  return data;
};

/* Helper untuk label */
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


const groupedRequests = computed(() => {
  // Jika API Anda masih mengirim data per item, kita kelompokkan berdasarkan waktu & nama
  const requests = store.requests || [];
  const map = new Map();

  requests.forEach(req => {
    const key = req.employee_name + req.created_at;
    if (!map.has(key)) {
      map.set(key, { ...req, items_count: 1 });
    } else {
      const entry = map.get(key);
      entry.items_count += 1;
    }
  });

  return Array.from(map.values());
});

const getItemsInRequest = (req) => {
  // Mencari semua item yang memiliki waktu permintaan (created_at) 
  // atau identifier yang sama dengan request yang dipilih
  return store.requests.filter(item =>
    item.employee_name === req.employee_name &&
    item.created_at === req.created_at
  );
};


</script>