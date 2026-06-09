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
  },
});