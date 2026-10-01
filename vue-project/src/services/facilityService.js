import api from "@/api/axios";

export default {
  // Get Master Options
  getOptions() {
    return api.get('/facility-reports/options');
  },

  // Get List Laporan
  getReports(params = {}) {
    return api.get('/facility-reports', { params });
  },

  // Post Laporan Kerusakan Baru (Pegawai)
  createReport(formData) {
    return api.post('/facility-reports', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },

  // Post Perbaikan (Kaur Perlengkapan)
  updateRepair(id, formData) {
    return api.post(`/facility-reports/${id}/repair`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },

  deleteReport(id) {
    return api.delete(`/facility-reports/${id}`);
},

  // Get Dashboard Stats
  getDashboardStats() {
    return api.get('/facility-reports/dashboard-stats');
  },
};