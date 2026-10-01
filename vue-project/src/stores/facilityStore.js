import { defineStore } from "pinia";
import facilityService from "@/services/facilityService";

export const useFacilityStore = defineStore("facility", {
  state: () => ({
    locations: [],
    facilityTypes: [],
    reports: [],
    pagination: {
      currentPage: 1,
      lastPage: 1,
      total: 0,
    },
    dashboardStats: {
      summary: { total: 0, completed: 0, pending: 0 },
      chart_by_facility: [],
      chart_by_location: [],
      chart_monthly: [],
      gallery: [],
    },
    loading: false,
    error: null,
  }),

  actions: {
    // 1. Fetch Master Options
    async fetchOptions() {
      try {
        const res = await facilityService.getOptions();
        this.locations = res.data.locations;
        this.facilityTypes = res.data.facility_types;
      } catch (err) {
        this.error = err.response?.data?.message || "Gagal memuat data master";
      }
    },

    // src/stores/facilityStore.js
    async fetchReports(page = 1, status = "") {
      this.loading = true;
      try {
        // Siapkan params
        const params = { page };
        if (status) params.status = status;

        const res = await facilityService.getReports(params);

        // --- PRINT RAW RESPONSE DARIPADA MENEBAK ---
        console.log(">>> RAW AXIOS RES:", res);
        console.log(">>> RES DATA:", res?.data);

        // Ambil array data dengan aman
        const listData = res?.data?.data || res?.data || [];

        if (Array.isArray(listData)) {
          this.reports = listData;
        } else {
          console.warn("listData bukan Array!", listData);
          this.reports = [];
        }

        if (res?.data?.total !== undefined) {
          this.pagination.total = res.data.total;
        }
      } catch (err) {
        console.error("Error fetching reports:", err);
        this.reports = [];
      } finally {
        this.loading = false;
      }
    },

    // 3. Submit Laporan Kerusakan
    async submitReport(payload) {
      this.loading = true;
      try {
        const formData = new FormData();
        formData.append("reporter_id", payload.reporter_id);
        formData.append("location_id", payload.location_id);
        formData.append("facility_type_id", payload.facility_type_id);
        formData.append("description", payload.description);
        formData.append("photo_before", payload.photo_before);

        const res = await facilityService.createReport(formData);
        return res.data;
      } catch (err) {
        throw err.response?.data || err;
      } finally {
        this.loading = false;
      }
    },

    // 4. Submit Perbaikan (Kaur Perlengkapan)
    async submitRepair(id, payload) {
      this.loading = true;
      try {
        const formData = new FormData();

        formData.append("repaired_at", payload.repaired_at);
        formData.append("budget_source", payload.budget_source);
        formData.append("cost", payload.cost);

        // Hanya append photo_after jika ada File baru yang diunggah
        if (payload.photo_after instanceof File) {
          formData.append("photo_after", payload.photo_after);
        }

        if (payload.repair_notes) {
          formData.append("repair_notes", payload.repair_notes);
        }

        const res = await facilityService.updateRepair(id, formData);
        return res.data;
      } catch (err) {
        throw err.response?.data || err;
      } finally {
        this.loading = false;
      }
    },

    async deleteReport(id) {
      this.loading = true;
      try {
        const res = await facilityService.deleteReport(id);
        return res.data;
      } catch (err) {
        throw err.response?.data || err;
      } finally {
        this.loading = false;
      }
    },

    // 5. Fetch Dashboard Stats
    async fetchDashboardStats() {
      this.loading = true;
      try {
        const res = await facilityService.getDashboardStats();
        this.dashboardStats = res.data;
      } catch (err) {
        this.error = err.response?.data?.message || "Gagal memuat statistik";
      } finally {
        this.loading = false;
      }
    },
  },
});
