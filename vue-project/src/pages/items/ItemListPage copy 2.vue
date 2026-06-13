<template>
  <div class="p-4">
    <Toast />

    <div class="flex justify-between align-items-center mb-4 ">
      <div>
        <h2 class="text-2xl font-bold">📦 Stok Barang</h2>
        <p class="text-sm text-gray-500">Manajemen data barang gudang</p>
      </div>

      <div class="flex gap-2">
        <Button v-if="selectedItems.length > 0" :label="`Hapus Terpilih (${selectedItems.length})`" icon="pi pi-trash"
          severity="danger" @click="confirmBulkDelete" />
        <Button label="Tambah Item" icon="pi pi-plus" severity="success" @click="openCreate" />
      </div>
    </div>

    <div class="flex flex-col md:flex-row gap-3 mb-4">
      <span class="p-input-icon-left w-full">
        <i class="pi pi-search" />
        <InputText v-model="search" placeholder="Cari" class="w-full" />
      </span>

      <Select v-model="categoryFilter" :options="categories" placeholder="Filter Kategori" showClear
        class="w-full md:w-60" />

      <Select v-model="stockFilter" :options="[
        { label: 'Stok Rendah (≤5)', value: 'low' },
        { label: 'Stok Sedang (6-20)', value: 'medium' },
        { label: 'Stok Tinggi (>20)', value: 'high' }
      ]" optionLabel="label" optionValue="value" placeholder="Filter Stock" showClear class="w-full md:w-60" />
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

    <Dialog v-model:visible="dialog" :header="editMode ? 'Edit Item' : 'Tambah Item'" modal :style="{ width: '450px' }"
      class="p-fluid">
      <div class="grid gap-3">
        <div class="field">
          <label>Nama Barang</label>
          <InputText v-model="form.name" />
        </div>
        <div class="field">
          <label>Kategori</label>
          <InputText v-model="form.category" />
        </div>
        <div class="field">
          <label>Stock</label>
          <InputNumber v-model="form.stock" class="w-full" />
        </div>
        <div class="field">
          <label>Kondisi</label>
          <InputText v-model="form.description" rows="3" />
        </div>
      </div>

      <template #footer>
        <Button label="Batal" icon="pi pi-times" text severity="secondary" @click="dialog = false" />
        <Button label="Simpan" icon="pi pi-check" severity="success" @click="save" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useItemStore } from "@/stores/item";
import { useToast } from "primevue/usetoast";


const store = useItemStore();
const toast = useToast();

const dialog = ref(false);
const editMode = ref(false);
const selectedId = ref(null);
const search = ref("");
const categoryFilter = ref(null);
const stockFilter = ref(null);

// Penampung data item terpilih (wajib array kosong)
const selectedItems = ref([]);

const categories = computed(() => {
  return [...new Set(store.items.map(i => i.category))];
});

const form = ref({ name: "", category: "", stock: 0, description: "" });

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

onMounted(() => {
  store.fetchItems();
});

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

// Logika Hapus Massal Aman
// Ganti fungsi confirmBulkDelete yang lama dengan yang baru ini:
const confirmBulkDelete = async () => {
  if (confirm(`Yakin ingin menghapus ${selectedItems.value.length} item ini secara massal?`)) {
    try {
      // 1. Kumpulkan semua ID barang yang dicentang petugas ke dalam satu Array
      const ids = selectedItems.value.map(item => item.id);

      // 2. Tembak langsung ke fungsi massal di Pinia Store (Hanya 1 kali request ke Laravel)
      await store.deleteMultipleItems(ids);

      // 3. Tampilkan notifikasi sukses
      toast.add({
        severity: 'success',
        summary: 'Sukses',
        detail: `${ids.length} item berhasil dihapus sekaligus`,
        life: 3000
      });

      // 4. Reset dan bersihkan data checkbox di tabel
      selectedItems.value = [];

      // 5. Segarkan data tabel gudang
      await store.fetchItems();
    } catch (err) {
      toast.add({
        severity: 'error',
        summary: 'Gagal',
        detail: err.response?.data?.message || 'Gagal mengeksekusi hapus massal',
        life: 3000
      });
    }
  }
};
</script>

<style scoped>
.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
</style>