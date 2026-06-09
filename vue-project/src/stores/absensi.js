import { defineStore } from "pinia";
import api from "@/api/axios";

export const useAbsensiStore = defineStore("absensi", {
  state: () => ({
    data: [],
    loading: false,
  }),

  actions: {
    async fetchAbsensi(bulan, tahun) {
      try {
        this.loading = true;

        const res = await api.get("/absensi", {
          params: {
            bulan,
            tahun,
          },
        });

        this.data = res.data;
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    async saveAbsensi(data) {
      try {
        await api.post("/absensi", data);
      } catch (error) {
        console.error(error);
      }
    },
  },
});
