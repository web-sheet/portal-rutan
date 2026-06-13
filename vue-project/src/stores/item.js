import { defineStore } from "pinia";
import api from "@/api/axios";

export const useItemStore = defineStore("item", {
  state: () => ({
    items: [],
    loading: false,
  }),

  actions: {
    async fetchItems() {
      this.loading = true;
      const res = await api.get("/items");
      this.items = res.data;
      this.loading = false;
    },

    async createItem(data) {
      await api.post("/items", data);
    },

    async updateItem(id, data) {
      await api.put(`/items/${id}`, data);
    },

    async deleteItem(id) {
      await api.delete(`/items/${id}`);
    },

    async deleteMultipleItems(ids) {
      this.loading = true;
      try {
        await api.post("/items/bulk-delete", { ids });
      } finally {
        this.loading = false;
      }
    },
    async importExcelItems(itemsArray) {
      this.loading = true;
      try {
        // Mengirim array dari excel langsung ke route backend
        await api.post("/items/import-excel", { items: itemsArray });
      } catch (error) {
        console.error("Gagal import data excel ke server:", error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
