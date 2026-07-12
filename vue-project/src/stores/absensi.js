import { defineStore } from "pinia";
import api from "@/api/axios";

export const useAbsensiStore = defineStore("absensi", {
  state: () => ({
    data: [],
    loading: false,
    // Template Default (bisa diedit admin nanti)
    //     templateHtml: `
    //       <div style="font-family: Arial, sans-serif; color: #334155;">
    // <div style="font-family: Arial, sans-serif; color: #000; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
    //   <table style="width: 100%; border-collapse: collapse;">
    //     <tr>
    //       <td style="width: 120px; vertical-align: middle; text-align: center;">
    //         <img src="/logo-imipas.png" style="width: 100px; height: auto;" />
    //       </td>

    //       <td style="text-align: center; vertical-align: middle; line-height: 1;">
    //         <div style="font-size: 14px; font-weight: bold; text-transform: uppercase;">KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN REPUBLIK INDONESIA</div>
    //         <div style="font-size: 14px; font-weight: bold; text-transform: uppercase;">DIREKTORAT JENDERAL PEMASYARAKATAN</div>
    //         <div style="font-size: 14px; font-weight: bold; text-transform: uppercase;">KANTOR WILAYAH DAERAH KHUSUS JAKARTA</div>
    //         <div style="font-size: 16px; font-weight: bold; text-transform: uppercase;">RUMAH TAHANAN NEGARA KELAS I PONDOK BAMBU</div>
    //         <div style="font-size: 11px; margin-top: 5px;">
    //           Jalan Percetakan Negara VIII No. 54 Jakarta Pusat No. HP 08111150528<br/>
    //           Laman: https://bapasjakpus.kemenkumham.go.id Pos-el: bapasjakpus.dki@gmail.com
    //         </div>
    //       </td>
    //     </tr>
    //   </table>
    // </div>

    // <p>Absensi Pegawai Rumah Tahanan Negara Kelas I Pondok Bambu Bulan : {{BULAN_TAHUN}}</p>

    //         {{TABEL_ABSENSI}}

    //         <div style="margin-top: 50px; width: 100%;">
    //           <table style="width: 100%; border: none;">
    //             <tr>
    //               <td style="width: 70%;"></td>
    //               <td style="text-align: center;">
    //                 <p>Jakarta, {{TANGGAL_SEKARANG}}</p>
    //                 <p style="margin-bottom: 70px;">Kepegawaian</p>
    //                 <p><b>( ____________________ )</b></p>
    //               </td>
    //             </tr>
    //           </table>
    //         </div>
    //       </div>
    //     `,

    templateHtml: `
<div style="font-family: Arial, sans-serif; color: #334155;">
  <div style="font-family: Arial, sans-serif; color: #000; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
    <table style="width: 100%; border-collapse: collapse;">
      <tr>
        <td style="width: 120px; vertical-align: middle; text-align: center;">
          <img src="/logo-imipas.png" style="width: 100px; height: auto;" />
        </td>
        <td style="text-align: center; vertical-align: middle; line-height: 1;">
          <div style="font-size: 14px; font-weight: bold; text-transform: uppercase;">KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN REPUBLIK INDONESIA</div>
          <div style="font-size: 14px; font-weight: bold; text-transform: uppercase;">DIREKTORAT JENDERAL PEMASYARAKATAN</div>
          <div style="font-size: 14px; font-weight: bold; text-transform: uppercase;">KANTOR WILAYAH DAERAH KHUSUS JAKARTA</div>
          <div style="font-size: 16px; font-weight: bold; text-transform: uppercase;">RUMAH TAHANAN NEGARA KELAS I PONDOK BAMBU</div>
          <div style="font-size: 11px; margin-top: 5px;">
            Jalan Percetakan Negara VIII No. 54 Jakarta Pusat No. HP 08111150528<br/>
            Laman: https://bapasjakpus.kemenkumham.go.id Pos-el: bapasjakpus.dki@gmail.com
          </div>
        </td>
      </tr>
    </table>
  </div>
        
  <p style="font-size: 14px; font-weight: bold;">Absensi Pegawai Rumah Tahanan Negara Kelas I Pondok Bambu Bulan : {{BULAN_TAHUN}}</p>
  
  {{TABEL_ABSENSI}}
  
  {{BLOK_TANDA_TANGAN}}
</div>
`,
    // Kita simpan string HTML ttd ini di Pinia atau panggil langsung di fungsi cetak
    templateTtdHtml: `
  <div style="margin-top: 40px; width: 100%;">
    <table style="width: 100%; border: none;">
      <tr>
        <td style="width: 70%;"></td>
        <td style="text-align: center; font-size: 13px; color: #000;">
          <p>Jakarta, {{TANGGAL_SEKARANG}}</p>
          <p style="margin-bottom: 60px; font-weight: bold;">Kepegawaian</p>
          <p><b>( ____________________ )</b></p>
        </td>
      </tr>
    </table>
  </div>
`,
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

    async saveBulkAbsensi(bulkData) {
      try {
        // Tembak endpoint bulk yang baru saja dibuat
        await api.post("/absensi/bulk", bulkData);
      } catch (error) {
        console.error("Gagal menyimpan absensi massal di store:", error);
        throw error; // Lempar error ke UI agar try-catch di UI tahu kalau gagal
      }
    },

    // ---- ACTION BARU: AMBIL TEMPLATE DARI LARAVEL ----
    // Di Pinia, ubah sedikit fetchTemplate
    async fetchTemplate() {
      try {
        // Menambahkan query parameter acak agar tidak terkena cache browser
        const res = await api.get(
          "/settings/template?t=" + new Date().getTime(),
        );
        if (res.data && res.data.html) {
          this.templateHtml = res.data.html;
        }
      } catch (error) {
        console.error(error);
      }
    },

    // ---- ACTION UPDATE: KIRIM & SIMPAN KE LARAVEL ----
    async updateTemplate(newHtml) {
      this.templateHtml = newHtml;
      try {
        await api.post("/settings/template", { html: newHtml });
      } catch (error) {
        console.error("Gagal menyimpan template ke database:", error);
        throw error; // Lempar error agar toast di komponen bisa mendeteksi jika gagal
      }
    },

    async fetchDashboardStats(params = {}) {
      try {
        // Pastikan ada properti { params } di argumen kedua Axios GET
        const response = await api.get("/absensi/dashboard-stats", { params });
        return response.data;
      } catch (error) {
        console.error("Gagal mengambil data dashboard:", error);
        return null;
      }
    },
  },
});
