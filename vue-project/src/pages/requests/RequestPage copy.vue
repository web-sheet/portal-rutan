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

        <Column header="Status">
          <template #body="{ data }">
            <Tag :value="data.status" :severity="statusColor(data.status)" class="uppercase font-bold text-[10px]" />
          </template>
        </Column>

        <Column field="formatted_created_at" header="Tanggal" sortable />

        <Column header="Detail" class="text-center" headerClass="text-center">
          <template #body="{ data }">
            <Button icon="pi pi-eye" severity="info" text rounded v-tooltip.top="'Lihat Timeline'"
              @click="openTimeline(data)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <Dialog v-model:visible="dialog" header="📝 Ajukan Permintaan Barang" modal
      :breakpoints="{ '960px': '75vw', '641px': '92vw' }" :style="{ width: '450px' }" class="p-fluid">
      <div class="flex flex-col gap-4 mt-2">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-semibold text-slate-700">Nama Pegawai</label>
          <InputText v-model="form.employee_name"  />
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-semibold text-slate-700">Jabatan</label>
          <InputText v-model="form.division"   />
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
          <i class="pi pi-exclamation-triangle" />
          <span>Stok di gudang tidak mencukupi untuk jumlah yang Anda minta.</span>
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-semibold text-slate-700">Jumlah Permintaan</label>
          <InputNumber v-model="form.stock_requested" placeholder="Kuantitas barang" showButtons :min="1" />
        </div>

        <div class="pt-2">
          <Button label="Kirim Request" icon="pi pi-send" severity="success" :disabled="stockNotEnough" @click="submit"
            class="w-full" />
        </div>
      </div>
    </Dialog>

    <Dialog v-model:visible="timelineDialog" modal header="⏳ Alur Riwayat Permohonan" :breakpoints="{ '641px': '95vw' }"
      :style="{ width: '480px' }">
      <div v-if="selectedRequest" class="mt-4 px-1">
        <Timeline :value="getTimelineData(selectedRequest)" class="customized-timeline">
          <template #marker="slotProps">
            <span class="flex w-8 h-8 items-center justify-center text-white rounded-full shadow-sm"
              :class="slotProps.item.color">
              <i :class="slotProps.item.icon"></i>
            </span>
          </template>
          <template #content="slotProps">
            <div class="bg-slate-50 border border-slate-200 p-3 rounded-lg mb-4 ml-2">
              <p class="font-bold text-slate-800 text-sm">{{ slotProps.item.status }}</p>
              <p class="text-xs text-slate-500 mt-0.5">{{ slotProps.item.date }}</p>
              <p v-if="slotProps.item.by" class="text-xs text-slate-400 mt-1 italic">
                Oleh: {{ slotProps.item.by }}
              </p>
            </div>
          </template>
        </Timeline>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useRequestStore } from "@/stores/request";

// Pendaftaran komponen PrimeVue internal
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Timeline from 'primevue/timeline';
import Toast from 'primevue/toast';

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
  await store.submitRequest(form.value);
  dialog.value = false;
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
  if (status === "pending") return "warn";
  if (status === "approved") return "success";
  if (status === "rejected") return "danger";
};

const openTimeline = (data) => {
  selectedRequest.value = data;
  timelineDialog.value = true;
};

// Formatter data untuk dipasok masuk ke komponen PrimeVue Timeline secara dinamis
const getTimelineData = (req) => {
  const data = [
    {
      status: 'Permohonan Diajukan',
      date: req.formatted_requested_at || req.formatted_created_at,
      icon: 'pi pi-send',
      color: 'bg-blue-500'
    }
  ];

  if (req.approved_kaur_at || req.formatted_approved_kaur_at) {
    data.push({
      status: 'Disetujui Kaur',
      date: req.formatted_approved_kaur_at,
      by: req.formatted_approved_kaur_by,
      icon: 'pi pi-check',
      color: 'bg-amber-500'
    });
  }

  if (req.formatted_approved_kasi_at) {
    data.push({
      status: 'Disetujui Kasi',
      date: req.formatted_approved_kasi_at,
      by: req.formatted_approved_kasi_by,
      icon: 'pi pi-check-circle',
      color: 'bg-emerald-500'
    });
  }

  // if (req.formatted_completed_at) {
  //   data.push({
  //     status: 'Barang Dikeluarkan (Selesai)',
  //     date: req.formatted_completed_at,
  //     icon: 'pi pi-box',
  //     color: 'bg-green-600'
  //   });
  // }

  if (req.formatted_rejected_at) {
    data.push({
      status: 'Permohonan Ditolak',
      date: req.formatted_rejected_at,
      by: req.formatted_rejected_by,
      icon: 'pi pi-times-circle',
      color: 'bg-red-500'
    });
  }

  return data;
};
</script>