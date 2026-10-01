<template>
  <div class="card p-4">
    <!-- Header / Toolbar -->
    <div class="flex justify-between items-center mb-4 gap-4">
      <h2 class="text-xl font-bold text-surface-900 dark:text-surface-0">{{ title }}</h2>
      
      <div class="flex items-center gap-2">
        <!-- Search Input -->
        <IconField iconPosition="left">
      
          <InputText v-model="filters['global'].value" placeholder="Cari..." class="p-inputtext-sm" />
        </IconField>

        <!-- Tombol Tambah -->
        <Button label="Tambah" icon="pi pi-plus" severity="primary" @click="openNewModal" />
      </div>
    </div>

    <!-- PrimeVue DataTable -->
    <DataTable
      :value="masterStore.items"
      :loading="masterStore.loading"
      v-model:filters="filters"
      paginator
      :rows="10"
      :rowsPerPageOptions="[5, 10, 25]"
      dataKey="id"
      stripedRows
      responsiveLayout="scroll"
      :globalFilterFields="['name']"
      class="p-datatable-sm"
    >
      <template #empty>
        <div class="text-center p-4 text-surface-500">Data tidak ditemukan.</div>
      </template>

      <Column header="No" headerStyle="width: 4rem">
        <template #body="slotProps">
          {{ slotProps.index + 1 }}
        </template>
      </Column>

      <Column field="name" header="Nama" sortable></Column>

      <Column header="Aksi" headerStyle="width: 8rem" bodyClass="text-center">
        <template #body="slotProps">
          <div class="flex justify-center gap-2">
            <Button icon="pi pi-pencil" severity="warn" text rounded @click="editItem(slotProps.data)" />
            <Button icon="pi pi-trash" severity="danger" text rounded @click="confirmDelete(slotProps.data.id)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <!-- Dialog Form (Tambah / Edit) -->
    <Dialog
      v-model:visible="itemDialog"
      :header="isEdit ? 'Edit ' + title : 'Tambah ' + title"
      :modal="true"
      class="p-fluid max-w-md w-full"
    >
      <div class="flex flex-col gap-2 mt-2">
        <label for="name" class="font-semibold">Nama</label>
        <InputText id="name" v-model.trim="form.name" required autofocus :class="{ 'p-invalid': submitted && !form.name }" />
        <small class="p-error" v-if="submitted && !form.name">Nama wajib diisi.</small>
      </div>

      <template #footer>
        <Button label="Batal" icon="pi pi-times" text @click="hideDialog" />
        <Button label="Simpan" icon="pi pi-check" severity="primary" @click="saveItem" :loading="saving" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { FilterMatchMode } from '@primevue/core/api'
import { useMasterStore } from '@/stores/masterStore'

const props = defineProps({
  apiEndpoint: { type: String, required: true }, // '/api/locations' atau '/api/facility-types'
  title: { type: String, default: 'Master Data' }
})

const masterStore = useMasterStore()

const itemDialog = ref(false)
const isEdit = ref(false)
const submitted = ref(false)
const saving = ref(false)
const form = ref({ id: null, name: '' })

// PrimeVue Table Filter
const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS }
})

onMounted(() => {
  masterStore.fetchItems(props.apiEndpoint)
})

const openNewModal = () => {
  form.value = { id: null, name: '' }
  submitted.value = false
  isEdit.value = false
  itemDialog.value = true
}

const hideDialog = () => {
  itemDialog.value = false
  submitted.value = false
}

const editItem = (data) => {
  form.value = { ...data }
  isEdit.value = true
  itemDialog.value = true
}

const saveItem = async () => {
  submitted.value = true

  if (!form.value.name) return

  saving.value = true
  try {
    if (isEdit.value) {
      await masterStore.updateItem(props.apiEndpoint, form.value.id, { name: form.value.name })
    } else {
      await masterStore.createItem(props.apiEndpoint, { name: form.value.name })
    }
    itemDialog.value = false
  } catch (err) {
    console.error(err)
  } finally {
    saving.value = false
  }
}

const confirmDelete = async (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
    await masterStore.deleteItem(props.apiEndpoint, id)
  }
}
</script>