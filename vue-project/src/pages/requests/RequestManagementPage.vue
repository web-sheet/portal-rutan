<template>
    <div class="p-3 md:p-4">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <h2 class="text-2xl font-bold">
                Permohonan Barang
            </h2>
        </div>

        <!-- FILTER -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <!-- SEARCH -->
            <span class="relative w-full">
                <!-- <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /> -->

                <InputText v-model="search" placeholder="search" class=" w-full pl-0" />
            </span>

            <!-- STATUS -->
            <Select v-model="statusFilter" :options="[
                { label: 'Pending', value: 'pending' },
                { label: 'Approved Kaur', value: 'approved_kaur' },
                { label: 'Completed', value: 'completed' },
                { label: 'Rejected', value: 'rejected' }
            ]" optionLabel="label" optionValue="value" placeholder="Filter Status" showClear class="w-full md:w-56" />
        </div>
        <p class="text-sm text-gray-500 mb-3">
            Total: {{ filteredRequests.length }} permohonan
        </p>

        <!-- TABLE -->
        <div class="overflow-x-auto">
            <!-- TABLE -->
            <DataTable :value="filteredRequests" :loading="store.loading" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20]" stripedRows class="text-sm">
                <!-- always visible -->
                <Column field="employee_name" header="Pegawai" />
                <Column field="item_name" header="Barang" />

                <!-- desktop only -->
                <Column field="division" header="Jabatan" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <Column field="category" header="Kategori" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <Column field="stock_requested" header="Jumlah" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <Column field="final_approved_stock" header="Disetujui" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <Column header="Stock" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">
                                {{ data.item?.stock }}
                            </span>
                            <span class="text-gray-400 text-sm">stok</span>
                        </div>
                    </template>
                </Column>

                <!-- status always visible -->
                <Column header="Status">
                    <template #body="{ data }">
                        <Tag :value="data.status" :severity="statusColor(data.status)" />
                    </template>
                </Column>

                <!-- desktop only -->
                <Column field="formatted_created_at" header="Tanggal" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <!-- actions -->
                <Column header="Aksi">
                    <template #body="{ data }">
                        <div class="flex flex-wrap gap-2 items-center">

                            <!-- DETAIL -->
                            <div class="md:hidden">
                                <Button icon="pi pi-eye" size="small" severity="info" @click="openDetail(data)" />
                            </div>
                            <!-- APPROVE KAUR -->
                            <Button v-if="data.status === 'pending'" icon="pi pi-check-circle" severity="warning"
                                size="small" @click="openApprove(data, 'kaur')">
                                <!-- <span class="hidden md:inline ml-2">Approve</span> -->
                            </Button>

                            <!-- APPROVE KASI -->
                            <Button v-if="data.status === 'approved_kaur'" icon="pi pi-check-circle" severity="success"
                                size="small" @click="openApprove(data, 'kasi')">
                                <!-- <span class="hidden md:inline ml-2">Approve</span> -->
                            </Button>

                            <!-- REJECT -->
                            <Button v-if="data.status !== 'completed' && data.status !== 'rejected'" icon="pi pi-times"
                                size="small" severity="danger" @click="reject(data.id)" />





                            <Menu :ref="el => menus[data.id] = el" :model="getMenuItems(data)" popup />

                            <Button icon="pi pi-ellipsis-v" size="small" severity="secondary" text
                                @click="toggleMenu($event, data.id)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>


        <Dialog v-model:visible="timelineDialog" modal header="Timeline Permohonan"
            :style="{ width: '95vw', maxWidth: '500px' }">

            <div v-if="selectedRequest" class="space-y-4">

                <!-- REQUESTED -->
                <div class="flex gap-3 items-start">
                    <i class="pi pi-send text-blue-500 mt-1"></i>

                    <div>
                        <p class="font-semibold">Permohonan Dibuat</p>

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
                <div v-if="selectedRequest.approved_kasi_at" class="flex gap-3 items-start">
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
                <div v-if="selectedRequest.rejected_at" class="flex gap-3 items-start">
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

        <Dialog v-model:visible="detailDialog" modal header="Detail Permohonan"
            :style="{ width: '95vw', maxWidth: '450px' }">
            <div v-if="selectedRequest" class="space-y-3">
                <p><b>Pegawai:</b> {{ selectedRequest.employee_name }}</p>
                <p><b>Jabatan:</b> {{ selectedRequest.division }}</p>
                <p><b>Barang:</b> {{ selectedRequest.item_name }}</p>
                <p><b>Kategori:</b> {{ selectedRequest.category }}</p>
                <p><b>Jumlah:</b> {{ selectedRequest.stock_requested }}</p>
                <p><b>Disetujui:</b> {{ selectedRequest.final_approved_stock ?? '-' }}</p>
                <p><b>Stock:</b> {{ selectedRequest.item?.stock }}</p>
                <p><b>Tanggal:</b> {{ selectedRequest.formatted_created_at }}</p>

                <div class="pt-2">
                    <Tag :value="selectedRequest.status" :severity="statusColor(selectedRequest.status)" />
                </div>
            </div>
        </Dialog>

        <Dialog v-model:visible="approvalDialog" header="Approval Request" modal
            :style="{ width: '95vw', maxWidth: '420px' }" :pt="{
                header: { class: 'justify-center' },
                title: { class: 'w-full text-center' },

            }">

            <div v-if="approvalData" class="flex flex-col gap-3">

                <!-- ITEM INFO -->
                <p>
                    <b>Barang:</b> {{ approvalData.item_name }}
                </p>

                <!-- REQUEST AWAL -->
                <p>
                    <b>Request awal:</b>
                    {{ approvalData.stock_requested }}
                </p>

                <!-- HASIL KAUR -->
                <p>
                    <b>Disetujui Kaur:</b>

                    <span class="font-bold"
                        :class="approvalData.adjusted_stock_requested ? 'text-orange-600' : 'text-gray-500'">
                        {{ approvalData.adjusted_stock_requested ?? '-' }}
                    </span>
                </p>

                <!-- STOCK -->
                <p v-if="approvalData.item">
                    <b>Stock tersedia:</b> {{ approvalData.item.stock }}
                </p>

                <hr />

                <!-- INPUT FINAL APPROVAL -->
                <div>
                    <p class="text-sm text-gray-500 mb-1">

                        <span v-if="approvalType === 'kaur'">
                            Qty rekomendasi Kaur:
                        </span>

                        <span v-else>
                            Qty final disetujui Kasi:
                        </span>

                    </p>

                    <InputNumber v-model="approvalQty" :min="1" />
                </div>

                <!-- ACTION -->
                <Button :label="approvalType === 'kaur' ? 'Approve Kaur' : 'Approve Kasi'" icon="pi pi-check"
                    @click="submitApproval" />

            </div>

        </Dialog>

        <ConfirmDialog :pt="{
            header: {
                class: 'justify-center text-center'
            },
            title: {
                class: 'w-full text-center'
            },
            message: {
                class: 'text-center'
            },
            footer: {
                class: 'justify-center'
            }
        }" />

    </div>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import { useRequestStore } from "@/stores/request";
import Menu from 'primevue/menu'
import { useConfirm } from "primevue/useconfirm";

const confirm = useConfirm();

const detailDialog = ref(false);

const openDetail = (data) => {
    selectedRequest.value = data;
    detailDialog.value = true;
};


const menus = ref({});


const toggleMenu = (event, id) => {
    menus.value[id].toggle(event);
};

const getMenuItems = (data) => {
    return [
        {
            label: 'Timeline',
            icon: 'pi pi-clock',
            command: () => openTimeline(data)
        },
        {
            separator: true
        },
        {
            label: 'Delete Data',
            icon: 'pi pi-trash',
            command: () => deleteRequest(data.id)
        }
    ];
};

const timelineDialog = ref(false);
const selectedRequest = ref(null);



const store = useRequestStore();

onMounted(async () => {
    await store.fetchRequests();
});

/* STATUS COLOR */
const statusColor = (status) => {
    if (status === "pending") return "warning";
    if (status === "approved_kaur") return "info";
    if (status === "approved_kasi") return "success";
    if (status === "rejected") return "danger";
    if (status === "completed") return "success";

    return "secondary";
};


const reject = (id) => {
    confirm.require({
        message: 'Yakin ingin menolak permohonan ini?',
        header: 'Konfirmasi',
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Batal',
        acceptLabel: 'Ya, Reject',
        acceptClass: 'p-button-danger',
        accept: async () => {
            await store.reject(id);
            await store.fetchRequests();
        }
    });
};

const deleteRequest = (id) => {
    confirm.require({
        message: 'Data permohonan akan dihapus permanen. Lanjutkan?',
        header: 'Konfirmasi',
        icon: 'pi pi-trash',
        rejectLabel: 'Batal',
        acceptLabel: 'Ya, Hapus',
        acceptClass: 'p-button-danger',
        accept: async () => {
            await store.deleteRequest(id);
            await store.fetchRequests();
        }
    });
};

const openTimeline = (data) => {
    selectedRequest.value = data;
    timelineDialog.value = true;
};


const approvalDialog = ref(false);
const approvalData = ref(null);
const approvalQty = ref(0);
const approvalType = ref(""); // kaur / kasi
const search = ref("");
const statusFilter = ref(null);

const filteredRequests = computed(() => {
    return store.requests.filter((item) => {

        // SEARCH
        const keyword = search.value.toLowerCase();

        const matchesSearch =
            item.employee_name?.toLowerCase().includes(keyword) ||
            item.division?.toLowerCase().includes(keyword) ||
            item.item_name?.toLowerCase().includes(keyword) ||
            item.category?.toLowerCase().includes(keyword);

        // STATUS FILTER
        const matchesStatus =
            !statusFilter.value ||
            item.status === statusFilter.value;

        return matchesSearch && matchesStatus;
    });
});


const openApprove = async (data, type) => {
    await store.fetchRequests();

    const freshData = store.requests.find(r => r.id === data.id);

    approvalData.value = freshData;
    approvalType.value = type;

    if (type === "kaur") {
        approvalQty.value = freshData.stock_requested;
    } else {
        approvalQty.value =
            freshData.adjusted_stock_requested
            ?? freshData.stock_requested;
    }

    approvalDialog.value = true;
};

const submitApproval = async () => {

    if (approvalType.value === "kaur") {
        await store.approveKaur(approvalData.value.id, approvalQty.value);
    }

    if (approvalType.value === "kasi") {
        await store.approveKasi(approvalData.value.id, approvalQty.value);
    }

    approvalDialog.value = false;
    await store.fetchRequests();
};

</script>