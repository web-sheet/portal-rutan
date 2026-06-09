<template>
  <div class="p-20">

    <!-- HEADER -->
    <div class="flex justify-between mb-4">
      <h2 class="text-xl font-bold">Request Barang</h2>

      <Button label="Ajukan Barang" icon="pi pi-plus" @click="openDialog" />
    </div>

    <!-- TABLE TRACKING -->
    <DataTable :value="store.requests" :loading="store.loading" paginator :rows="10" stripedRows>
      <Column field="employee_name" header="Pegawai" />
      <Column field="division" header="Divisi" />
      <Column field="item_name" header="Barang" />
      <Column field="stock_requested" header="Jumlah" />
      <Column field="final_approved_stock" header="Disetujui" />

      <Column header="Status">
        <template #body="{ data }">
          <Tag :value="data.status" :severity="statusColor(data.status)" />
        </template>
      </Column>

      <Column header="Detail">
        <template #body="{ data }">

          <Button icon="pi pi-eye" size="small" severity="info" @click="openTimeline(data)" />

        </template>
      </Column>

      <Column field="formatted_created_at" header="Tanggal" />
    </DataTable>

    <!-- MODAL FORM -->
    <Dialog v-model:visible="dialog" header="Ajukan Barang" modal style="width: 450px">

      <div class="flex flex-col gap-3">

        <!-- Nama -->
        <InputText v-model="form.employee_name" placeholder="Nama Pegawai" />

        <!-- Divisi -->
        <InputText v-model="form.division" placeholder="Divisi" />

        <!-- Item -->
        <Select v-model="selectedItem" :options="store.items" optionLabel="name" placeholder="Pilih Barang" />

        <!-- Info otomatis -->
        <div v-if="selectedItem" class="p-2 bg-gray-100 rounded">
          <p><b>Kategori:</b> {{ selectedItem.category }}</p>
          <p><b>Stok:</b> {{ selectedItem.stock }}</p>
        </div>

        <!-- WARNING -->
        <div v-if="stockNotEnough" class="p-3 bg-red-100 border border-red-300 rounded text-red-700 text-sm">
          Stock tidak mencukupi untuk jumlah permintaan.
        </div>

        <!-- Jumlah -->
        <InputNumber v-model="form.stock_requested" placeholder="Jumlah" />

        <!-- Submit -->
        <Button label="Kirim Request" icon="pi pi-send" :disabled="stockNotEnough" @click="submit" />
      </div>

    </Dialog>

    <Dialog v-model:visible="timelineDialog" modal header="Timeline Permohonan" :style="{ width: '500px' }">

      <div v-if="selectedRequest" class="space-y-4">

        <!-- REQUEST -->
        <div class="flex gap-3 items-start">
          <i class="pi pi-send text-blue-500 mt-1"></i>

          <div>
            <p class="font-semibold">
              Permohonan Dibuat
            </p>

            <p>
              {{ selectedRequest.formatted_requested_at }}
            </p>
          </div>
        </div>

        <!-- KAUR -->
        <div v-if="selectedRequest.approved_kaur_at" class="flex gap-3 items-start">
          <i class="pi pi-check-circle text-yellow-500 mt-1"></i>

          <div>
            <p class="font-semibold">
              Disetujui Kaur
            </p>

            <p>
              {{ selectedRequest.formatted_approved_kaur_at }}
            </p>

            <small>
              Oleh:
              {{ selectedRequest.formatted_approved_kaur_by }}
            </small>
          </div>
        </div>

        <!-- KASI -->
        <div v-if="selectedRequest.formatted_approved_kasi_at" class="flex gap-3 items-start">
          <i class="pi pi-check-circle text-green-500 mt-1"></i>

          <div>
            <p class="font-semibold">
              Disetujui Kasi
            </p>

            <p>
              {{ selectedRequest.formatted_approved_kasi_at }}
            </p>

            <small>
              Oleh:
              {{ selectedRequest.formatted_approved_kasi_by }}
            </small>
          </div>
        </div>

        <!-- COMPLETED -->
        <div v-if="selectedRequest.formatted_completed_at" class="flex gap-3 items-start">
          <i class="pi pi-box text-green-600 mt-1"></i>

          <div>
            <p class="font-semibold">
              Barang Dikeluarkan
            </p>

            <p>
              {{ selectedRequest.formatted_completed_at }}
            </p>
          </div>
        </div>

        <!-- REJECT -->
        <div v-if="selectedRequest.formatted_rejected_at" class="flex gap-3 items-start">
          <i class="pi pi-times-circle text-red-500 mt-1"></i>

          <div>
            <p class="font-semibold">
              Permohonan Ditolak
            </p>

            <p>
              {{ selectedRequest.formatted_rejected_at }}
            </p>

            <small>
              Oleh:
              {{ selectedRequest.formatted_rejected_by }}
            </small>
          </div>
        </div>

      </div>

    </Dialog>

  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useRequestStore } from "@/stores/request";

const timelineDialog = ref(false);
const selectedRequest = ref(null);

const store = useRequestStore();

const dialog = ref(false);
const selectedItem = ref(null);

const form = ref({
  employee_name: "",
  division: "",
  item_id: null,
  stock_requested: 1,
});

onMounted(async () => {
  await store.fetchItems();
  await store.fetchRequests();

});

watch(selectedItem, (value) => {
  form.value.item_id = value?.id ?? null;
});

const openDialog = () => {
  dialog.value = true;
};


const stockNotEnough = computed(() => {

  if (!selectedItem.value) return false;

  return form.value.stock_requested > selectedItem.value.stock;
});



const submit = async () => {

  console.log(form.value);
  await store.submitRequest(form.value);



  dialog.value = false;

  // reset form
  form.value = {
    employee_name: "",
    division: "",
    item_id: null,
    stock_requested: 1,
  };

  selectedItem.value = null;

  await store.fetchRequests();
};

const statusColor = (status) => {
  if (status === "pending") return "warning";
  if (status === "approved") return "success";
  if (status === "rejected") return "danger";
};

const openTimeline = (data) => {
  selectedRequest.value = data;
  timelineDialog.value = true;
};
</script>