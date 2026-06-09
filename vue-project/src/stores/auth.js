import { defineStore } from "pinia";
import api from "@/api/axios";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
  }),

  actions: {
    // async login(email, password) {
    //   // 1. WAJIB ini dulu
    //   await api.get("/sanctum/csrf-cookie");

    //   // 2. baru login
    //   await api.post("/api/login", {
    //     email,
    //     password,
    //   });

    //   // 3. ambil user
    //   await this.fetchUser();
    // },

    async login(email, password) {
      const res = await api.post("/login", {
        email,
        password,
      });

      localStorage.setItem("token", res.data.access_token);
    },

    async fetchUser() {
      const res = await api.get("/me");
      this.user = res.data;
    },

    async logout() {
      await api.post("/logout");
      localStorage.removeItem("token");
      this.user = null;
    },
  },
});
