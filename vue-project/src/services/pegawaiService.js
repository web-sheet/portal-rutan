import { defineStore } from "pinia";
import api from "@/api/axios";

export const usePegawaiStore = defineStore("pegawai", {
  state: () => ({
    pegawais: [],
    loading: false,
  }),

  actions: {
    // GET /api/pegawai
    async fetchPegawais() {
      try {
        this.loading = true;

        const res = await api.get("/pegawai");

        this.pegawais = res.data;
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    // POST /api/pegawai
    async createPegawai(data) {
      try {
        await api.post("/pegawai", data);
      } catch (error) {
        console.error(error);
      }
    },

    // PUT /api/pegawai/{id}
    async updatePegawai(id, data) {
      try {
        await api.put(`/pegawai/${id}`, data);
      } catch (error) {
        console.error(error);
      }
    },

    // DELETE /api/pegawai/{id}
    async deletePegawai(id) {
      try {
        await api.delete(`/pegawai/${id}`);
      } catch (error) {
        console.error(error);
      }
    },
  },
});