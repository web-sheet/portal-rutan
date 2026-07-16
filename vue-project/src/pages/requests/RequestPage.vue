<template>
  <!-- 1. NAVBAR PREMIUM SIPANDA -->
  <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-sm px-4 py-3 select-none">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
      
      <!-- Logo dan Nama Sistem -->
      <div class="flex items-center gap-3">
        <!-- Menggunakan variabel sipanda yang sudah Anda definisikan -->
        <img :src="sipanda" alt="Logo SIPANDA" class="w-12 h-12 object-contain filter drop-shadow-sm transition-transform duration-300 hover:scale-105" />
        
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-lg font-black text-slate-800 tracking-tight leading-none">SIPANDA</h1>
          
          </div>
          <p class="text-xs font-medium text-slate-400   sm:block mt-0.5">
            Sistem Informasi Permintaan dan Pendistribusian Barang
          </p>
        </div>
      </div>

    

    </div>
  </nav>

  <!-- 2. MAIN CONTENT AREA -->
  <div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">
    <Toast />

    <!-- HEADER SEKSI TABEL & AJUKAN BARANG (Lebih Bersih & Compact) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-5">
      <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
          Tabel Permohonan Barang
        </h2>
        <p class="text-sm font-medium text-slate-500 mt-1">
          Berikut adalah data permohonan barang persediaan beserta statusnya
        </p>
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

    <!-- TABEL DATA -->
    <div class="card bg-white shadow-md border border-slate-200/60 rounded-2xl overflow-hidden transition-all duration-300">
      <DataTable :value="groupedRequests" :loading="store.loading" paginator :rows="10" stripedRows
        responsiveLayout="scroll" class="p-datatable-sm text-sm">

        <Column field="employee_name" header="Nama Pegawai" class="font-semibold text-slate-800 py-3" />
        <Column field="division" header="Jabatan" class="text-slate-600" />

        <!-- Tampilkan jumlah barang saja dalam satu baris -->
        <Column header="Barang" class="text-center">
          <template #body="{ data }">
            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200/50 px-2.5 py-1 rounded-full text-xs font-semibold">
              {{ data.items_count }} Item
            </span>
          </template>
        </Column>

        <Column header="Status" style="min-width: 150px;">
          <template #body="{ data }">
            <Tag :value="getStatusLabel(data.status)" :severity="statusColor(data.status)"
              class="text-[11px] font-bold px-2 py-1 rounded-md" />
          </template>
        </Column>

        <Column field="formatted_created_at" header="Tanggal" class="text-slate-500" />

        <Column header="Detail" class="text-center">
          <template #body="{ data }">
            <Button severity="info" text rounded @click="openTimeline(data)" class="hover:bg-slate-50">
              <i class="pi pi-eye text-lg text-slate-600 hover:text-emerald-600"></i>
            </Button>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- DIALOG INPUT PENGAJUAN -->
    <Dialog v-model:visible="dialog" modal :style="{ width: '500px' }" class="p-fluid rounded-2xl shadow-2xl">
      <template #header>
        <div class="w-full text-center font-bold text-lg text-slate-800">
          Ajukan Permintaan Barang
        </div>
      </template>
      <div class="flex flex-col gap-4 mt-2">
        <!-- Header: Pegawai & Jabatan tetap di atas -->
        <div class="grid grid-cols-1 gap-4 pb-4 border-b border-slate-100">
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Pegawai</label>
            <Select v-model="selectedPegawai" :options="pegawaiList" optionLabel="nama" filter class="rounded-xl border-slate-300" />
          </div>
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Jabatan</label>
            <InputText :value="selectedPegawai?.jabatan" readonly class="bg-slate-50 rounded-xl border-slate-200" />
          </div>
        </div>

        <!-- AREA TANDA TANGAN -->
        <div class="field mb-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">Tanda Tangan Pemohon</label>
          <!-- Wadah Canvas -->
          <div class="border border-slate-300 rounded-xl overflow-hidden bg-slate-50 relative flex justify-center items-center">
            <canvas ref="signatureCanvas" class="w-full h-[200px] block bg-white cursor-crosshair"></canvas>
          </div>
          <!-- Tombol Aksi Tanda Tangan -->
          <div class="flex justify-end mt-2">
            <Button type="button" label="Hapus Tanda Tangan" icon="pi pi-trash" severity="danger" text size="small"
              @click="clearSignature" class="text-xs font-bold" />
          </div>
        </div>

        <!-- Area Pilih Barang -->
        <div class="space-y-3 bg-slate-50/50 p-4 rounded-2xl border border-slate-200/80">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Pilih Barang</label>
          <Select v-model="selectedItem" :options="availableItems" optionLabel="name" placeholder="Pilih Logistik"
            filter class="w-full rounded-xl" />

          <!-- Info Stok muncul di bawah Select saat barang dipilih -->
          <div v-if="selectedItem" class="flex justify-between items-center text-xs p-3 bg-white border rounded-xl shadow-sm">
            <span class="text-slate-600 font-medium">Stok Tersedia: <b class="text-emerald-600">{{ selectedItem.stock }}</b></span>
            <InputNumber v-model="itemQty" placeholder="Qty" class="w-16" inputClass="w-16 text-center p-2 rounded-lg" :min="1"
              :max="selectedItem.stock" />
          </div>

          <Button label="Tambah ke Daftar" icon="pi pi-plus" @click="addToCart"
            :disabled="!selectedItem || itemQty > selectedItem.stock" class="w-full rounded-xl bg-emerald-600 border-none" />
        </div>

        <!-- Tabel Daftar Barang yang akan diajukan -->
        <div v-if="cart.length > 0" class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Barang Diajukan:</label>
          <div class="border rounded-2xl bg-white overflow-hidden shadow-sm">
            <div v-for="(item, index) in cart" :key="index"
              class="flex justify-between items-center p-3 border-b last:border-0 text-sm">
              <span class="font-semibold text-slate-700">{{ item.name }} <span class="text-emerald-600 font-bold">({{ item.qty }})</span></span>
              <Button icon="pi pi-trash" text severity="danger" @click="cart.splice(index, 1)" class="p-button-rounded" />
            </div>
          </div>
        </div>

        <Button severity="success" @click="submit" :disabled="cart.length === 0 || isLoading" :loading="isLoading"
          class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-emerald-600 border-none hover:bg-emerald-700 font-bold mt-2" label="Kirim">
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

    <!-- DIALOG DETAIL/TIMELINE -->
    <Dialog v-model:visible="timelineDialog" modal :breakpoints="{ '641px': '95vw' }" :style="{ width: '480px' }" class="rounded-2xl">
      <template #header>
        <div class="w-full text-center font-bold text-lg text-slate-800">
          Detail Permohonan
        </div>
      </template>
      <div v-if="selectedRequest" class="mt-2">
        <!-- List Barang yang Diminta -->
        <div class="mb-6">
          <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Daftar Barang:</h4>
          <div class="border rounded-2xl overflow-hidden bg-white shadow-sm">
            <!-- Header Kolom -->
            <div class="grid grid-cols-12 gap-2 p-3 bg-slate-50/80 border-b text-xs font-bold text-slate-400 uppercase tracking-wider">
              <span class="col-span-6">Nama Barang</span>
              <span class="col-span-3 text-center">Diajukan</span>
              <span class="col-span-3 text-center">Disetujui</span>
            </div>

            <!-- List Item -->
            <div v-for="item in getItemsInRequest(selectedRequest)" :key="item.id"
              class="grid grid-cols-12 gap-2 items-center p-3 border-b last:border-0 text-sm">
              <span class="col-span-6 text-slate-700 font-semibold">{{ item.item_name }}</span>
              <!-- Angka Diajukan -->
              <span class="col-span-3 text-center text-slate-500 font-medium">
                {{ item.stock_requested }}
              </span>
              <!-- Angka Disetujui -->
              <span class="col-span-3 text-center font-black text-emerald-600">
                {{ item.final_approved_stock ?? item.adjusted_stock_requested ?? '-' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Status Riwayat:</h4>
        <Timeline :value="getTimelineData(selectedRequest)" class="customized-timeline">
          <template #marker="slotProps">
            <span class="flex w-8 h-8 items-center justify-center text-white rounded-full shadow-md"
              :class="slotProps.item.color">
              <component :is="slotProps.item.icon" class="w-4 h-4" />
            </span>
          </template>
          <template #content="slotProps">
            <div class="bg-slate-50/50 border border-slate-200/80 p-3.5 rounded-2xl mb-4 ml-2 shadow-sm">
              <p class="font-bold text-slate-800 text-sm">{{ slotProps.item.status }}</p>
              <p class="text-xs text-slate-400 mt-1 font-medium">{{ slotProps.item.date }}</p>
              <p v-if="slotProps.item.by" class="text-xs text-slate-500 mt-1.5 italic font-medium">Oleh: {{ slotProps.item.by }}</p>
            </div>
          </template>
        </Timeline>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed, h, nextTick } from "vue";
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
import SignaturePad from 'signature_pad'; // 1. Import library-nya
import sipanda from '@/assets/sipanda.png'

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

// Referensi Elemen Canvas
const signatureCanvas = ref(null);
let isDrawing = false;
let ctx = null;

const form = ref({ employee_name: "", division: "", signature: '', item_id: null, stock_requested: 1 });

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
watch([selectedPegawai,], (newValue) => {
  if (newValue) {
    form.value.employee_name = newValue.nama;
    form.value.division = newValue.jabatan;

  } else {
    form.value.employee_name = "";
    form.value.division = "";
  }
});

watch(selectedItem, (value) => { form.value.item_id = value?.id ?? null; });

// Referensi Canvas & Instance Signature Pad

let signaturePadInstance = null; // Tempat menyimpan engine signature pad

// 2. Fungsi Inisialisasi Baru
const initSignaturePad = () => {
  const canvas = signatureCanvas.value;
  if (!canvas) return;

  // Menyesuaikan ukuran canvas dengan resolusi layar (anti-blur)
  const ratio = Math.max(window.devicePixelRatio || 1, 1);
  canvas.width = canvas.offsetWidth * ratio;
  canvas.height = canvas.offsetHeight * ratio;
  canvas.getContext("2d").scale(ratio, ratio);

  // Jalankan library signature pad pada canvas
  signaturePadInstance = new SignaturePad(canvas, {
    minWidth: 1.5,     // Ketebalan garis minimum (saat goresan cepat)
    maxWidth: 4.0,     // Ketebalan garis maksimum (saat goresan lambat)
    penColor: '#1e293b' // Warna tinta (slate-800)
  });
};

// 3. Fungsi Hapus Tanda Tangan
const clearSignature = () => {
  if (signaturePadInstance) {
    signaturePadInstance.clear();
    form.value.signature = '';
  }
};



// Pantau saat dialog terbuka
watch(dialog, async (newVal) => {
  if (newVal) {
    await nextTick();
    initSignaturePad();
  }
});

// Pantau saat dialog terbuka untuk inisialisasi context canvas
watch(dialog, async (newVal) => {
  if (newVal) {
    await nextTick();
    initSignaturePad();
  }
});






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
// 4. Saat Kirim Form
const submit = async () => {
  if (!signaturePadInstance || signaturePadInstance.isEmpty()) {
    // Opsional: Beri peringatan jika ttd masih kosong
    toast.add({ severity: 'warn', summary: 'Peringatan', detail: 'Tanda tangan wajib diisi', life: 3000 });
    return;
  }

  isLoading.value = true;

  // Ambil base64 langsung dari library
  const signatureData = signaturePadInstance.toDataURL('image/png');

  const payload = {
    employee_name: selectedPegawai.value.nama,
    division: selectedPegawai.value.jabatan,
    signature: signatureData,
    items: cart.value.map(item => ({
      item_id: item.item_id,
      qty: item.qty
    }))
  };

  try {
    await store.submitRequest(payload);
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Permintaan barang telah diajukan', life: 3000 });

    cart.value = [];
    selectedPegawai.value = null;
    dialog.value = false;
    clearSignature();
    await store.fetchRequests();
  } catch (err) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: err.response?.data?.message || 'Terjadi kesalahan', life: 5000 });
  } finally {
    isLoading.value = false;
  }
};
/* 1. STATUS COLOR (Disamakan dengan Halaman Approval) */
const statusColor = (status) => {
  if (status === "pending") return "warn";             // Kuning
  if (status === "approved_kaur") return "secondary";    // Abu-abu (Diproses)
  if (status === "ready") return "info";                // Biru (Siap Diambil)
  if (status === "completed") return "success";         // Hijau (Selesai/Sudah Diambil)
  if (status === "rejected") return "danger";           // Merah
  return "secondary";
};

/* 2. MAPPING LABEL STATUS (Disamakan dengan Halaman Approval) */
const getStatusLabel = (status) => {
  const labels = {
    'pending': 'Menunggu Persetujuan Kaur',
    'approved_kaur': 'Barang Sedang Disiapkan Staf',
    'ready': 'Barang Ready (Siap Diambil)',
    'completed': 'Barang Sudah Diambil (Selesai)',
    'rejected': 'Ditolak'
  };
  return labels[status] || status;
};

const openTimeline = (data) => {
  selectedRequest.value = data;
  timelineDialog.value = true;
};

/* 3. HEROICONS RENDER (Ditambah ikon Box untuk tanda barang Ready) */
const PaperAirplaneIcon = h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '2', stroke: 'currentColor' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5' })]);
const CheckIcon = h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '2', stroke: 'currentColor' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'm4.5 12.75 6 6 9-13.5' })]);
const XCircleIcon = h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '2', stroke: 'currentColor' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' })]);
// Ikon Box Baru untuk status Ready
const BoxIcon = h('svg', { xmlns: 'http://www.w3.org/2000/svg', fill: 'none', viewBox: '0 0 24 24', 'stroke-width': '2', stroke: 'currentColor' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-5.25v9' })]);

/* 4. DATA TIMELINE (DIURUTKAN SESUAI ALUR BARU) */
const getTimelineData = (req) => {
  // Titik Awal: Pengajuan masuk
  const data = [{
    status: 'Menunggu Persetujuan Kaur',
    date: req.formatted_requested_at || req.formatted_created_at,
    icon: PaperAirplaneIcon,
    color: 'bg-blue-500'
  }];

  // Tahap 1: Disetujui Kaur perlengkapan
  if (req.approved_kaur_at || req.formatted_approved_kaur_at) {
    data.push({
      status: 'Disetujui Kaur (Barang Sedang Disiapkan)',
      date: req.formatted_approved_kaur_at,
      by: req.formatted_approved_kaur_by,
      icon: CheckIcon,
      color: 'bg-amber-500'
    });
  }

  // Tahap 2 Baru: Staf mengonfirmasi barang telah READY di gudang

  if (req.confirmed_by_staff_at || req.formatted_confirmed_by_staff_at) {
    data.push({
      status: 'Barang Ready (Siap Diambil)',
      date: req.formatted_confirmed_by_staff_at || req.confirmed_by_staff_at,
      by: req.formatted_confirmed_by_staff_by || req.confirmed_by_staff_by,
      icon: BoxIcon,
      color: 'bg-cyan-500' // Berwarna Biru Cyan menandakan instruksi aksi ambil barang
    });
  }

  // Tahap 3 Baru: Barang diserahkan ke tangan pemohon dan status COMPLETED
  if (req.completed_at || req.formatted_completed_at) {
    data.push({
      status: 'Barang Sudah Diambil (Selesai)',
      date: req.formatted_completed_at || req.completed_at,
      by: req.formatted_completed_by || req.completed_by || 'Staf Perlengkapan',
      icon: CheckIcon,
      color: 'bg-emerald-500' // Berwarna Hijau Sukses penuh
    });
  }

  // Jika ditolak di awal
  if (req.rejected_at || req.formatted_rejected_at) {
    data.push({
      status: 'Permohonan Ditolak',
      date: req.formatted_rejected_at || req.rejected_at,
      by: req.formatted_rejected_by || req.rejected_by,
      icon: XCircleIcon,
      color: 'bg-red-500'
    });
  }

  return data;
};

const groupedRequests = computed(() => {
  const requests = store.requests || [];
  const map = new Map();

  requests.forEach(req => {
    let key = "";

    // 1. Tentukan key pengelompokan (Gunakan variabel 'req', bukan 'item')
    if (req.request_code) {
      // Utama: Gunakan request_code dari backend
      key = req.request_code;
    } else {
      // Cadangan: Untuk data lama milik user yang request_code-nya masih NULL
      const minuteOnly = req.created_at ? req.created_at.substring(0, 16) : 'no-date';
      key = `${req.employee_name}_${minuteOnly}`;
    }

    // 2. Masukkan ke dalam Map
    if (!map.has(key)) {
      // Jika kelompok belum ada, buat baru dan bungkus item pertamanya ke dalam array jika perlu
      map.set(key, {
        ...req,
        items_count: 1,
        items: [req] // Bagus untuk jaga-jaga kalau mau looping rincian barang di dalam komponen detail/buka-tutup
      });
    } else {
      // Jika kelompok sudah ada, naikkan jumlah itemnya
      const entry = map.get(key);
      entry.items_count += 1;
      entry.items.push(req);
    }
  });

  return Array.from(map.values());
});

const getItemsInRequest = (req) => {
  return store.requests.filter(item =>
    item.employee_name === req.employee_name &&
    item.created_at === req.created_at
  );
};


</script>