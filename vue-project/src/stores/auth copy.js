import { defineStore } from "pinia";
import api from "@/api/axios";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
  }),

  actions: {
 

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
