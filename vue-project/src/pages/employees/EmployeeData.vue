<script setup>
import { ref, computed, onMounted } from "vue";

import { usePegawaiStore } from "@/services/pegawaiService";

import InputText from "primevue/inputtext";
import Select from "primevue/select";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import Button from "primevue/button";
import Dialog from "primevue/dialog";


const dialog = ref(false);

const editMode = ref(false);

const form = ref({
    nama: "",
    nip: "",
    jabatan: "",
    pangkat: "",
    golongan: "",
    status: "",
});


const store = usePegawaiStore();

const search = ref("");
const statusFilter = ref(null);

const detailDialog = ref(false);

const selectedPegawai = ref(null);

onMounted(() => {
    store.fetchPegawais();
});

const filteredPegawais = computed(() => {
    return store.pegawais.filter((item) => {

        const matchSearch =
            item.nama?.toLowerCase().includes(search.value.toLowerCase()) ||
            item.nip?.toLowerCase().includes(search.value.toLowerCase()) ||
            item.jabatan?.toLowerCase().includes(search.value.toLowerCase());

        const matchStatus =
            !statusFilter.value ||
            item.status === statusFilter.value;

        return matchSearch && matchStatus;
    });
});

const statusColor = (status) => {
    switch (status) {
        case "aktif":
            return "success";

        case "cuti":
            return "warning";

        case "pensiun":
            return "danger";

        default:
            return "secondary";
    }
};

const openDetail = (data) => {
    selectedPegawai.value = data;

    detailDialog.value = true;
};

const deletePegawai = async (id) => {
    if (!confirm("Hapus data pegawai?")) return;

    await store.deletePegawai(id);

    await store.fetchPegawais();
};



const openCreate = () => {

    editMode.value = false;

    form.value = {
        nama: "",
        nip: "",
        jabatan: "",
        pangkat: "",
        golongan: "",
        status: "",
    };

    dialog.value = true;
};


const save = async () => {

    try {

        if (editMode.value) {

            await store.updatePegawai(
                form.value.id,
                form.value
            );

        } else {

            await store.createPegawai(form.value);

        }

        dialog.value = false;

        await store.fetchPegawais();

    } catch (error) {

        console.error(error);

    }

};

const openEdit = (data) => {

    editMode.value = true;

    form.value = {
        id: data.id,
        nama: data.nama,
        nip: data.nip,
        jabatan: data.jabatan,
        pangkat: data.pangkat,
        golongan: data.golongan,
        status: data.status,
    };

    dialog.value = true;
};


</script>

<template>
    <div class="p-3 md:p-4">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">

            <h2 class="text-2xl font-bold">
                Data Pegawai
            </h2>

            <Button label="Tambah Pegawai" icon="pi pi-plus" @click="openCreate" />

        </div>

        <!-- FILTER -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">

            <!-- SEARCH -->
            <span class="relative w-full">

                <InputText v-model="search" placeholder="Cari nama, nip, jabatan..." class="w-full" />

            </span>

            <!-- STATUS -->
            <Select v-model="statusFilter" :options="[
                { label: 'Aktif', value: 'aktif' },
                { label: 'Cuti', value: 'cuti' },
                { label: 'Pensiun', value: 'pensiun' }
            ]" optionLabel="label" optionValue="value" placeholder="Filter Status" showClear class="w-full md:w-56" />

        </div>

        <!-- TOTAL -->
        <p class="text-sm text-gray-500 mb-3">
            Total: {{ filteredPegawais.length }} pegawai
        </p>

        <!-- TABLE -->
        <div class="overflow-x-auto">

            <DataTable :value="filteredPegawais" :loading="store.loading" paginator :rows="10"
                :rowsPerPageOptions="[5, 10, 20]" stripedRows class="text-sm">

                <!-- ALWAYS -->
                <Column field="nama" header="Nama" />

                <Column field="nip" header="NIP" />

                <!-- DESKTOP -->
                <Column field="jabatan" header="Jabatan" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <Column field="pangkat" header="Pangkat" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <Column field="golongan" header="Golongan" :pt="{
                    headerCell: { class: 'hidden md:table-cell' },
                    bodyCell: { class: 'hidden md:table-cell' }
                }" />

                <!-- STATUS -->
                <Column header="Status">

                    <template #body="{ data }">

                        <Tag :value="data.status" :severity="statusColor(data.status)" />

                    </template>

                </Column>

                <!-- ACTION -->
                <Column header="Aksi">

                    <template #body="{ data }">

                        <div class="flex gap-2">

                            <!-- DETAIL -->
                            <Button icon="pi pi-eye" severity="info" size="small" @click="openDetail(data)" />

                            <!-- EDIT -->
                            <Button icon="pi pi-pencil" severity="warning" size="small" @click="openEdit(data)"" />

                            <!-- DELETE -->
                            <Button icon=" pi pi-trash" severity="danger" size="small"
                                @click="deletePegawai(data.id)" />

                        </div>

                    </template>

                </Column>

            </DataTable>

        </div>

        <!-- DETAIL -->
        <Dialog v-model:visible="detailDialog" modal header="Detail Pegawai"
            :style="{ width: '95vw', maxWidth: '450px' }">

            <div v-if="selectedPegawai" class="space-y-3">

                <p>
                    <b>Nama:</b>
                    {{ selectedPegawai.nama }}
                </p>

                <p>
                    <b>NIP:</b>
                    {{ selectedPegawai.nip }}
                </p>

                <p>
                    <b>Jabatan:</b>
                    {{ selectedPegawai.jabatan }}
                </p>

                <p>
                    <b>Pangkat:</b>
                    {{ selectedPegawai.pangkat }}
                </p>

                <p>
                    <b>Golongan:</b>
                    {{ selectedPegawai.golongan }}
                </p>

                <div class="pt-2">

                    <Tag :value="selectedPegawai.status" :severity="statusColor(selectedPegawai.status)" />

                </div>

            </div>

        </Dialog>





        <!-- MODAL FORM -->
        <Dialog v-model:visible="dialog" :header="editMode ? 'Edit Pegawai' : 'Tambah Pegawai'" modal
            :style="{ width: '95vw', maxWidth: '520px' }" :breakpoints="{ '768px': '95vw' }" class="rounded-2xl">

            <!-- FORM -->
            <div class="flex flex-col gap-5 mt-2">

                <!-- NAMA -->
                <div class="flex flex-col gap-2">

                    <label class="text-sm font-semibold text-surface-700">
                        Nama Pegawai
                    </label>

                    <InputText v-model="form.nama" placeholder="Masukkan nama pegawai" class="w-full" />

                </div>

                <!-- NIP -->
                <div class="flex flex-col gap-2">

                    <label class="text-sm font-semibold text-surface-700">
                        NIP
                    </label>

                    <InputText v-model="form.nip" placeholder="Masukkan NIP" class="w-full" />

                </div>

                <!-- JABATAN -->
                <div class="flex flex-col gap-2">

                    <label class="text-sm font-semibold text-surface-700">
                        Jabatan
                    </label>

                    <InputText v-model="form.jabatan" placeholder="Masukkan jabatan" class="w-full" />

                </div>

                <!-- GRID -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- PANGKAT -->
                    <div class="flex flex-col gap-2">

                        <label class="text-sm font-semibold text-surface-700">
                            Pangkat
                        </label>

                        <InputText v-model="form.pangkat" placeholder="Contoh: Penata" class="w-full" />

                    </div>

                    <!-- GOLONGAN -->
                    <div class="flex flex-col gap-2">

                        <label class="text-sm font-semibold text-surface-700">
                            Golongan
                        </label>

                        <InputText v-model="form.golongan" placeholder="Contoh: III/a" class="w-full" />

                    </div>

                </div>

                <!-- STATUS -->
                <div class="flex flex-col gap-2">

                    <label class="text-sm font-semibold text-surface-700">
                        Status Pegawai
                    </label>

                    <Select v-model="form.status" :options="[
                        { label: 'Aktif', value: 'aktif' },
                        { label: 'Tidak Aktif', value: 'tidak aktif' },
                   
                    ]" optionLabel="label" optionValue="value" placeholder="Pilih status pegawai" class="w-full" />

                </div>

            </div>

            <!-- FOOTER -->
            <template #footer>

                <div class="flex justify-end gap-2 w-full pt-4">

                    <Button label="Batal" icon="pi pi-times" severity="secondary" text @click="dialog = false" />

                    <Button :label="editMode ? 'Update Data' : 'Simpan Data'" icon="pi pi-check" severity="success"
                        @click="save" />

                </div>

            </template>

        </Dialog>


    </div>



</template>