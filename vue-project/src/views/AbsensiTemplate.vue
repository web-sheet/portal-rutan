<script setup>
import { ref, onMounted, watch } from "vue";
import { useAbsensiStore } from "@/stores/absensi";
import { useRouter } from "vue-router"; // 1. Tambahkan ini
import { useToast } from "primevue/usetoast";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

const store = useAbsensiStore();
const toast = useToast();
const router = useRouter(); // 2. Inisialisasi router
const content = ref("");
const myEditor = ref(null); // <-- Tambahkan ref untuk menangkap elemen komponen

onMounted(async () => {
    // 1. Ambil data terbaru dari Laravel ke Pinia Store
    await store.fetchTemplate();

    // 2. Set isi variabel content untuk model Quill
    content.value = store.templateHtml;
});


// KUNCI PERBAIKAN: Gunakan watch agar jika data di store berubah, Quill ikut berubah
watch(() => store.templateHtml, (newVal) => {
    if (newVal && myEditor.value) {
        myEditor.value.setHTML(newVal);
    }
});

// Fungsi penanganan saat Quill siap sepenuhnya di layar
const onEditorReady = () => {
    // Saat editor sudah siap, pastikan dia mengambil data terbaru dari store
    if (myEditor.value && store.templateHtml) {
        myEditor.value.setHTML(store.templateHtml);
    }
};

const save = async () => {
    try {
        await store.updateTemplate(content.value);

        toast.add({
            severity: 'success',
            summary: 'Berhasil',
            detail: 'Template PDF berhasil disimpan ke Database!',
            life: 2000 // Dipercepat sedikit agar transisi terasa natural
        });

        // 3. Tambahkan jeda singkat agar user sempat melihat notifikasi sukses sebelum pindah
        setTimeout(() => {
            router.push({ name: 'absensi' });
        }, 1500);

    } catch (err) {
        toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal menyambung ke server', life: 3000 });
    }
};
const toolbarOptions = [
    [{ header: [1, 2, 3, false] }],
    [{ size: ['small', false, 'large', 'huge'] }], // Tambahkan kontrol ukuran font
    ["bold", "italic", "underline", "strike"],
    [{ color: [] }, { background: [] }],
    [{ align: [] }],
    [{ list: "ordered" }, { list: "bullet" }],
    ["link", "image"], // Tambahkan image
    ["clean"]
];
</script>

<template>
    <div class="p-6 max-w-6xl mx-auto">
        <Toast />

        <div class="flex items-center gap-3 mb-5">
            <Button icon="pi pi-arrow-left" severity="secondary" text rounded
                @click="$router.push({ name: 'absensi.index' })" class="hover:bg-slate-100" />
            <div>
                <h1 class="text-xl font-bold text-slate-800">Pengaturan Template PDF</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Sesuaikan tata letak Kop dan Tanda tangan laporan bulanan.
                </p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div
                class="p-4 border-b border-slate-100 flex flex-col sm:flex-row justify-between sm:items-center gap-3 bg-slate-50/50">
                <span class="text-xs text-slate-500">
                    Tag <code
                        class="bg-slate-100 text-red-500 font-mono px-1 py-0.5 rounded font-bold">{{ TABEL_ABSENSI }}</code>
                    dan <code
                        class="bg-slate-100 text-red-500 font-mono px-1 py-0.5 rounded font-bold">{{ TANGGAL_SEKARANG }}</code>
                    wajib disertakan.
                </span>
                <Button label="Simpan Perubahan" icon="pi pi-save" @click="save" severity="success"
                    class="p-button-sm shadow-sm self-end sm:self-auto" />
            </div>

            <div class="p-4 bg-white">
                <QuillEditor ref="myEditor" v-model:content="content" contentType="html" theme="snow"
                    :toolbar="toolbarOptions" @ready="onEditorReady" style="height: 400px;" />
            </div>
        </div>
    </div>
</template>

<style>
/* Kustomisasi sedikit agar tampilan Quill menyatu dengan tema Tailwind/Slate Anda */
.ql-toolbar.ql-snow {
    border: 1px solid #e2e8f0 !important;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    background-color: #f8fafc;
}

.ql-container.ql-snow {
    border: 1px solid #e2e8f0 !important;
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    font-family: Arial, sans-serif;
    font-size: 14px;
}
.ql-editor p {
    margin-bottom: 0.8rem !important;
    line-height: 1.5;
    padding: 10px 10px !important; /* Memberi jarak di dalam kotak editor */
}

/* Mengatur ukuran gambar agar tidak terlalu besar */
.ql-editor img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 10px auto;
}
</style>