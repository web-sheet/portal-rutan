import { defineStore } from "pinia";
import api from "@/api/axios";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null, // Berisi data user lengkap (name, email, username, role) setelah fetchUser()
  }),

  getters: {
    // Getter untuk mempermudah pengecekan hak akses di Vue Components / Router Guards
    isAdmin: (state) => state.user?.role === 'admin',
    isKasi: (state) => state.user?.role === 'kasi',
    isKepegawaian: (state) => state.user?.role === 'kepegawaian',
    isPerlengkapan: (state) => state.user?.role === 'perlengkapan',
    isKarutan: (state) => state.user?.role === 'karutan',
    isStafPerlengkapan: (state) => state.user?.role === 'staf_perlengkapan',
  },

  actions: {
    // Parameter pertama diubah menjadi 'loginInput' karena isinya bisa email / username
    async login(loginInput, password) {
      const res = await api.post("/login", {
        login: loginInput, // Dikirim dengan key 'login' sesuai penyesuaian di Laravel Controller
        password,
      });

      // Simpan JWT Token ke localStorage
      localStorage.setItem("token", res.data.access_token);
      
      // Setelah login sukses, langsung panggil fetchUser untuk mengisi data state.user beserta role-nya
      await this.fetchUser();
    },

    async fetchUser() {
      try {
        const res = await api.get("/me");
        this.user = res.data; // Pastikan response dari endpoint /me di Laravel juga mengembalikan kolom 'role'
      } catch (error) {
        this.user = null;
        localStorage.removeItem("token");
        throw error;
      }
    },

    async logout() {
      try {
        await api.post("/logout");
      } catch (error) {
        console.error("Logout backend gagal, membersihkan session lokal...", error);
      } finally {
        localStorage.removeItem("token");
        this.user = null;
      }
    },
  },
});