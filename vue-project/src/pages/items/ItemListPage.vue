<template>
  <div class="p-4">

    <!-- HEADER -->
    <div class="flex justify-between align-items-center mb-4 ">
      <div>
        <h2 class="text-2xl font-bold">📦 Stok Barang</h2>
        <p class="text-sm text-gray-500">Manajemen data barang gudang</p>
      </div>

      <Button label="Tambah Item" icon="pi pi-plus" class="p-button-success" @click="openCreate" />
    </div>

    <!-- FILTER -->
    <div class="flex flex-col md:flex-row gap-3 mb-4">

      <!-- SEARCH -->
      <span class="p-input-icon-left w-full">
        <i class="pi pi-search" />
        <InputText v-model="search" placeholder="Cari" class="w-full" />
      </span>

      <!-- CATEGORY FILTER -->
      <Select  v-model="categoryFilter" :options="categories" placeholder="Filter Kategori" showClear
        class="w-full md:w-60" />

      <!-- STOCK FILTER -->
      <Select  v-model="stockFilter" :options="[
        { label: 'Stok Rendah (≤5)', value: 'low' },
        { label: 'Stok Sedang (6-20)', value: 'medium' },
        { label: 'Stok Tinggi (>20)', value: 'high' }
      ]" optionLabel="label" optionValue="value" placeholder="Filter Stock" showClear class="w-full md:w-60" />
    </div>

    <p class="text-sm text-gray-500 mb-3">
      Total: {{ filteredItems.length }} item
    </p>

    <!-- TABLE -->
    <Card>
      <DataTable :value="filteredItems" :loading="store.loading" paginator :rows="10" :rowsPerPageOptions="[5, 10, 20]"
        stripedRows responsiveLayout="scroll" class="p-datatable-sm">

        <Column field="name" header="Nama" sortable></Column>
        <Column field="category" header="Kategori" sortable></Column>

        <!-- STOCK dengan badge -->
        <Column header="Stock" sortable>
          <template #body="{ data }">
            <Tag :value="data.stock" :severity="data.stock > 10 ? 'success' : data.stock > 0 ? 'warning' : 'danger'" />
          </template>
        </Column>

        <Column field="description" header="Deskripsi"></Column>

        <!-- ACTION -->
        <Column header="Aksi" style="width: 140px">
          <template #body="{ data }">

            <Button icon="pi pi-pencil" class="p-button-warning p-button-sm mr-2" @click="openEdit(data)" />

            <Button icon="pi pi-trash" class="p-button-danger p-button-sm" @click="hapus(data.id)" />

          </template>
        </Column>

      </DataTable>
    </Card>

    <!-- MODAL FORM -->
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
          <label>Deskripsi</label>
          <Textarea v-model="form.description" rows="3" />
        </div>

      </div>

      <template #footer>
        <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="dialog = false" />

        <Button label="Simpan" icon="pi pi-check" class="p-button-success" @click="save" />
      </template>

    </Dialog>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useItemStore } from "@/stores/item";

const store = useItemStore();

const dialog = ref(false);
const editMode = ref(false);
const selectedId = ref(null);
const search = ref("");
const categoryFilter = ref(null);
const stockFilter = ref(null);


// kategori unik
const categories = computed(() => {
  return [...new Set(store.items.map(i => i.category))];
});

const form = ref({
  name: "",
  category: "",
  stock: 0,
  description: "",
});

const filteredItems = computed(() => {
  return store.items.filter(item => {
    const matchSearch =
      item.name.toLowerCase().includes(search.value.toLowerCase()) ||
      item.description?.toLowerCase().includes(search.value.toLowerCase());

    const matchCategory =
      !categoryFilter.value || item.category === categoryFilter.value;

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
  } else {
    await store.createItem(form.value);
  }

  dialog.value = false;
  await store.fetchItems();
};

const hapus = async (id) => {
  if (confirm("Yakin hapus item ini?")) {
    await store.deleteItem(id);
    await store.fetchItems();
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