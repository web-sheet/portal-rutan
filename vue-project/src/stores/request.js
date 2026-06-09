import { defineStore } from "pinia";
import api from "@/api/axios";

export const useRequestStore = defineStore("request", {
  state: () => ({
    items: [],
    requests: [],
    loading: false,
  }),

  actions: {
    // ambil master item
    async fetchItems() {
      const res = await api.get("/items");
      this.items = res.data;
    },

    // submit request
    async submitRequest(payload) {
      return await api.post("/requests", payload);
    },

    // list request (tracking)
    async fetchRequests() {
      this.loading = true;
      try {
        const res = await api.get("/requests");
        this.requests = res.data;
      } finally {
        this.loading = false;
      }
    },
 
    async approveKaur(id, qty) {
      return await api.post(`/requests/${id}/approve-kaur`, {
        stock_requested: qty,
      });
    },

  

    async approveKasi(id, qty) {
      return await api.post(`/requests/${id}/approve-kasi`, {
        stock_requested: qty,
      });
    },

    async reject(id) {
      await api.post(`/requests/${id}/reject`);
    },

    async deleteRequest(id) {
      await api.delete(`/requests/${id}`);
    },
  },
});
