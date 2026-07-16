<template>
  <div class="p-4">
    <Toast />

    <input type="file" ref="excelInput" class="hidden" accept=".xlsx, .xls" @change="handleExcelUpload" />

    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b border-slate-200 pb-4">
      <div class="flex items-center gap-4">
        <!-- Logo SIPANDA -->
        <img :src="sipanda" alt="Logo SIPANDA" class="w-12 h-12 object-contain filter drop-shadow-sm select-none" />

        <!-- Teks Judul -->
        <div>
          <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">Stok Barang</h2>
          <p class="text-sm font-medium text-slate-500 mt-1.5">Manajemen data barang persediaan</p>
        </div>
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto justify-end self-end sm:self-auto">
        <Button v-if="selectedItems.length > 0" severity="danger" class="p-button-sm flex items-center gap-2"
          @click="confirmBulkDelete">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
          </svg>
          <span>Hapus ({{ selectedItems.length }})</span>
        </Button>

        <Button severity="secondary" outlined class="p-button-sm flex items-center gap-2 bg-white"
          @click="triggerExcelInput">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            class="w-4 h-4 text-emerald-600">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
          </svg>
          <span>Import Excel</span>
        </Button>

        <Button severity="success" class="p-button-sm flex items-center gap-2" @click="openCreate">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
            class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <span>Tambah Item</span>
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-6 items-center">
      <div class="md:col-span-6 w-full">
        <span class="p-input-icon-left w-full h-10">

          <InputText v-model="search" placeholder="Cari barang..." class="w-full h-full" />
        </span>
      </div>

      <div class="md:col-span-3 w-full h-10">
        <Select v-model="categoryFilter" :options="categories" placeholder="Filter Kategori" showClear
          class="w-full h-full flex items-center" />
      </div>

      <div class="md:col-span-3 w-full h-10">
        <Select v-model="stockFilter" :options="[
          { label: 'Stok Rendah (≤5)', value: 'low' },
          { label: 'Stok Sedang (6-20)', value: 'medium' },
          { label: 'Stok Tinggi (>20)', value: 'high' }
        ]" optionLabel="label" optionValue="value" placeholder="Filter Stock" showClear
          class="w-full h-full flex items-center" />
      </div>
    </div>

    <p class="text-sm text-gray-500 mb-3">
      Total: {{ filteredItems.length }} item <span v-if="selectedItems.length > 0"
        class="text-emerald-600 font-semibold">({{ selectedItems.length }} dipilih)</span>
    </p>

    <Card>
      <DataTable :value="filteredItems" v-model:selection="selectedItems" dataKey="id" :loading="store.loading"
        paginator :rows="10" :rowsPerPageOptions="[5, 10, 20]" stripedRows responsiveLayout="scroll"
        class="p-datatable-sm">
        <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>

        <Column field="name" header="Nama" sortable></Column>
        <Column field="category" header="Kategori" sortable></Column>

        <Column header="Stock" sortable>
          <template #body="{ data }">
            <Tag :value="data.stock" :severity="data.stock > 10 ? 'success' : data.stock > 0 ? 'warning' : 'danger'" />
          </template>
        </Column>

        <Column field="description" header="Kondisi"></Column>

        <Column header="Aksi" style="width: 140px">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" severity="warn" class="p-button-sm mr-2" @click="openEdit(data)" />
            <Button icon="pi pi-trash" severity="danger" class="p-button-sm" @click="hapus(data.id)" />
          </template>
        </Column>
      </DataTable>
    </Card>

    <Dialog v-model:visible="dialog" :header="editMode ? '✏️ Edit Data Barang' : '➕ Tambah Barang Baru'" modal
      :style="{ width: '500px' }" class="p-fluid">
      <div class="p-2 space-y-5">

        <div class="grid grid-cols-1 gap-5">
          <div class="field flex flex-col gap-2">
            <label class="text-sm font-bold text-slate-700 uppercase tracking-wider">Nama Barang</label>
            <InputText v-model="form.name" placeholder="Masukkan nama barang" class="h-11" />
          </div>

          <div class="field flex flex-col gap-2">
            <label class="text-sm font-bold text-slate-700 uppercase tracking-wider">Kategori</label>
            <InputText v-model="form.category" placeholder="Masukkan kategori" class="h-11" />
          </div>
        </div>

        <div class="flex gap-4 w-full">

          <div class="w-1/ flex flex-col gap-1.5">
            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">
              Stok
            </label>
            <InputNumber v-model="form.stock" showButtons :min="0" class="h-[42px] [&_.p-inputtext]:h-full" />
          </div>

          <div class="flex-1 flex flex-col gap-1.5">
            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">
              Kondisi Barang
            </label>
            <InputText v-model="form.description" placeholder="Contoh: Baik" class="h-[42px] w-full" />
          </div>

        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
          <Button label="Batal" icon="pi pi-times" text severity="secondary" @click="dialog = false"
            class="px-4 py-2" />
          <Button label="Simpan Barang" icon="pi pi-check" severity="success" @click="save" class="px-4 py-2" />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useItemStore } from "@/stores/item";
import { useToast } from "primevue/usetoast";
import * as XLSX from "xlsx"; // Import engine baca Excel
import { useRoute, useRouter } from "vue-router"; // 1. Import router
import sipanda from '@/assets/sipanda.png'

const route = useRoute();
const router = useRouter();

const store = useItemStore();
const toast = useToast();

const excelInput = ref(null);
const dialog = ref(false);
const editMode = ref(false);
const selectedId = ref(null);
const search = ref("");
// 2. Ambil nilai awal dari URL, jika kosong beri null
const stockFilter = ref(route.query.stock || null);
const categoryFilter = ref(route.query.category || null);

const selectedItems = ref([]);
const form = ref({ name: "", category: "", stock: 0, description: "" });

const categories = computed(() => [...new Set(store.items.map(i => i.category))]);

watch([stockFilter, categoryFilter], ([newStock, newCategory]) => {
  router.replace({
    query: {
      ...route.query,
      stock: newStock || undefined, // undefined akan menghapus param jika kosong
      category: newCategory || undefined
    }
  });
});

const filteredItems = computed(() => {
  return store.items.filter(item => {
    const matchSearch =
      item.name.toLowerCase().includes(search.value.toLowerCase()) ||
      item.description?.toLowerCase().includes(search.value.toLowerCase());
    const matchCategory = !categoryFilter.value || item.category === categoryFilter.value;
    const matchStock =
      !stockFilter.value ||
      (stockFilter.value === "low" && item.stock <= 5) ||
      (stockFilter.value === "medium" && item.stock >= 6 && item.stock <= 20) ||
      (stockFilter.value === "high" && item.stock > 20);

    return matchSearch && matchCategory && matchStock;
  });
});

onMounted(() => { store.fetchItems(); });

const triggerExcelInput = () => { excelInput.value.click(); };

// LOGIKA PEMBACAAN EXCEL DENGAN KOLOM (nama, kategori, jumlah, kondisi)
const handleExcelUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = async (e) => {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, { type: "array" });
      const sheetName = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[sheetName];

      const rawData = XLSX.utils.sheet_to_json(worksheet);

      if (rawData.length === 0) {
        throw new Error("File Excel kosong.");
      }

      // Prosedur pembersihan data sebelum dikirim ke Laravel
      const mappedItems = rawData.map(row => {
        // Gabungkan semua key menjadi lowercase agar tidak sensitif huruf besar/kecil
        const cleanedRow = {};
        Object.keys(row).forEach(key => {
          cleanedRow[key.toLowerCase().trim()] = row[key];
        });

        // Ambil nilai berdasarkan kemungkinan nama kolom di excel petugas
        const name = cleanedRow['nama'] || cleanedRow['nama barang'] || cleanedRow['name'];
        const category = cleanedRow['kategori'] || cleanedRow['category'] || 'Umum';

        // Cari kolom jumlah/stok, pastikan dikonversi ke angka bulat positif
        const rawStock = cleanedRow['jumlah'] || cleanedRow['stock'] || cleanedRow['stok'] || 0;
        const stock = Math.max(0, parseInt(rawStock, 10) || 0);

        const description = cleanedRow['kondisi'] || cleanedRow['deskripsi'] || cleanedRow['description'] || '-';

        return { name, category, stock, description };
      })
        // CRITICAL VALIDATION: Buang baris kosong / baris tanpa nama barang agar tidak memicu eror 422
        .filter(item => item.name && item.name.toString().trim() !== "");

      if (mappedItems.length === 0) {
        toast.add({ severity: 'error', summary: 'Gagal', detail: 'Kolom "Nama" tidak ditemukan atau kosong', life: 4000 });
        return;
      }

      // Kirim ke Pinia
      await store.importExcelItems(mappedItems);

      toast.add({ severity: 'success', summary: 'Sukses Import', detail: `${mappedItems.length} barang berhasil dimasukkan`, life: 3000 });
      await store.fetchItems();
    } catch (err) {
      console.error(err);
      // Menampilkan pesan eror detail dari Laravel jika ada
      const errorMessage = err.response?.data?.message || 'Format Excel tidak sesuai validasi rutan';
      toast.add({ severity: 'error', summary: 'Gagal Import', detail: errorMessage, life: 5000 });
    } finally {
      event.target.value = "";
    }
  };
  reader.readAsArrayBuffer(file);
};

const openCreate = () => {
  editMode.value = false;
  selectedId.value = null;
  form.value = { name: "", category: "", stock: 0, description: "" };
  dialog.value = true;
};

const openEdit = (data) => {
  editMode.value = true;
  selectedId.value = data.id;
  form.value = { ...data };
  dialog.value = true;
};

const save = async () => {
  if (editMode.value) {
    await store.updateItem(selectedId.value, form.value);
    toast.add({ severity: 'success', summary: 'Sukses', detail: 'Item berhasil diperbarui', life: 3000 });
  } else {
    await store.createItem(form.value);
    toast.add({ severity: 'success', summary: 'Sukses', detail: 'Item baru berhasil disimpan', life: 3000 });
  }
  dialog.value = false;
  await store.fetchItems();
};

const hapus = async (id) => {
  if (confirm("Yakin hapus item ini?")) {
    await store.deleteItem(id);
    toast.add({ severity: 'success', summary: 'Sukses', detail: 'Item berhasil dihapus', life: 3000 });
    await store.fetchItems();
    selectedItems.value = selectedItems.value.filter(item => item.id !== id);
  }
};

const confirmBulkDelete = async () => {
  if (confirm(`Yakin ingin menghapus ${selectedItems.value.length} item ini secara massal?`)) {
    try {
      const ids = selectedItems.value.map(item => item.id);
      await store.deleteMultipleItems(ids);
      toast.add({ severity: 'success', summary: 'Sukses', detail: `${ids.length} item berhasil dihapus sekaligus`, life: 3000 });
      selectedItems.value = [];
      await store.fetchItems();
    } catch (err) {
      toast.add({ severity: 'error', summary: 'Gagal', detail: err.response?.data?.message || 'Gagal mengeksekusi hapus massal', life: 3000 });
    }
  }
};
</script>