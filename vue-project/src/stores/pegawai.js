import { defineStore } from "pinia";
import api from "@/api/axios";

export const usePegawaiStore = defineStore("pegawai", {
  state: () => ({
    pegawais: [],
    loading: false,
  }),

  actions: {
    // 1. Ambil Semua Data Pegawai
    async fetchPegawais() {
      this.loading = true;
      try {
        const response = await api.get("/pegawai");
        // Sesuaikan dengan struktur JSON response backend Anda (misal: response.data atau response.data.data)
        this.pegawais = response.data.data || response.data;
      } catch (error) {
        console.error("Gagal memuat data pegawai:", error);
      } finally {
        this.loading = false;
      }
    },

    // 2. Tambah Pegawai Baru
    async createPegawai(data) {
      this.loading = true;
      try {
        await api.post("/pegawais", data);
      } finally {
        this.loading = false;
      }
    },

    // 3. Update Data Pegawai
    async updatePegawai(id, data) {
      this.loading = true;
      try {
        await api.put(`/pegawais/${id}`, data);
      } finally {
        this.loading = false;
      }
    },

    // 4. Hapus Pegawai Tunggal
    async deletePegawai(id) {
      this.loading = true;
      try {
        await api.delete(`/pegawais/${id}`);
      } catch (error) {
        console.error("Gagal menghapus pegawai:", error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

 
    async importExcelPegawai(mappedData) {
      this.loading = true;
      try {
        await api.post("/pegawais/import", { data: mappedData });
      } catch (error) {
        console.error("Gagal import excel:", error);
        throw error; // <-- WAJIB DITAMBAHKAN agar dibaca oleh catch() di komponen UI
      } finally {
        this.loading = false;
      }
    },

    // 6. Hapus Massal (Bulk Delete)
    async deleteMultiplePegawais(ids) {
      this.loading = true;
      try {
        await api.post("/pegawais/bulk-delete", { ids });
      } catch (error) {
        console.error("Gagal bulk delete:", error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
