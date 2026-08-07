<template>
    <div class="min-h-screen flex flex-col bg-slate-50 text-slate-800 font-sans">

        <!-- TOPBAR / NAVIGASI RESMI -->
        <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">

                    <!-- BRAND / LOGO -->
                    <div class="flex items-center gap-3 cursor-pointer" @click="navigateTo('beranda')">
                        <img :src="logoPemasyarakatan" alt="Logo Pemasyarakatan" class="w-12 h-12 object-contain" />
                        <div>
                            <h1 class="text-lg md:text-xl font-bold text-slate-900 leading-tight">
                                RUTAN KELAS I PONDOK BAMBU
                            </h1>
                            <p class="text-xs text-slate-500 font-medium">
                                Kementerian Imigrasi dan Pemasyarakatan Republik Indonesia
                            </p>
                        </div>
                    </div>

                    <!-- DESKTOP NAVIGATION -->
                    <nav class="hidden md:flex items-center gap-6">
                        <button @click="activeTab = 'beranda'"
                            class="text-sm font-semibold transition-colors py-2 border-b-2 cursor-pointer"
                            :class="activeTab === 'beranda' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-600 hover:text-emerald-600'">
                            Beranda
                        </button>

                        <!-- DROPDOWN PROFIL -->
                        <div class="relative group">
                            <button
                                class="flex items-center gap-1 text-sm font-semibold py-2 border-b-2 transition-colors cursor-pointer"
                                :class="['gambaran-umum', 'sejarah', 'struktur'].includes(activeTab) ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-600 hover:text-emerald-600'">
                                <span>Profil</span>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- DROPDOWN MENU -->
                            <div
                                class="absolute left-0 mt-1 w-52 bg-white rounded-xl shadow-xl border border-slate-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <button @click="activeTab = 'gambaran-umum'"
                                    class="cursor-pointer w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">
                                    Gambaran Umum
                                </button>
                                <button @click="activeTab = 'sejarah'"
                                    class=" cursor-pointer w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">
                                    Sejarah
                                </button>
                                <button @click="activeTab = 'struktur'"
                                    class="cursor-pointer w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">
                                    Struktur Organisasi
                                </button>
                            </div>
                        </div>

                        <!-- LAYANAN/PRISMA -->
                        <button @click="scrollToLayanan"
                            class="cursor-pointer text-sm font-semibold transition-colors py-2 border-b-2 border-transparent text-slate-600 hover:text-emerald-600">
                            Layanan PRISMA
                        </button>
                    </nav>

                    <!-- AKSI LOGIN & HAMBURGER BUTTON -->
                    <div class="flex items-center gap-3">
                        <Button severity="secondary" outlined
                            class="!hidden md:!flex text-slate-700 border-slate-300 hover:bg-slate-50 px-4 py-2 items-center gap-2 text-sm rounded-xl transition-all cursor-pointer"
                            @click="goToLogin">
                            <span>Login Pegawai</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                            </svg>
                        </Button>

                        <!-- HAMBURGER BUTTON (MOBILE) -->
                        <button @click="isMobileMenuOpen = !isMobileMenuOpen"
                            class="md:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none transition-colors"
                            aria-label="Toggle Menu">
                            <svg v-if="!isMobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                </div>

                <!-- MOBILE MENU DROPDOWN -->
                <div v-if="isMobileMenuOpen"
                    class="md:hidden border-t border-slate-100 py-4 px-2 space-y-3 bg-white animate-fadeIn">
                    <button @click="navigateTo('beranda')"
                        class="w-full text-left px-3 py-2 rounded-lg font-semibold text-sm transition-colors"
                        :class="activeTab === 'beranda' ? 'bg-emerald-50 text-emerald-600' : 'text-slate-700 hover:bg-slate-50'">
                        Beranda
                    </button>

                    <!-- PROFIL SECTION FOR MOBILE -->
                    <div class="space-y-1">
                        <div class="px-3 py-1 text-xs font-bold text-slate-400 uppercase tracking-wider">Profil</div>
                        <button @click="navigateTo('gambaran-umum')"
                            class="w-full text-left pl-6 pr-3 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="activeTab === 'gambaran-umum' ? 'bg-emerald-50 text-emerald-600' : 'text-slate-600 hover:bg-slate-50'">
                            Gambaran Umum
                        </button>
                        <button @click="navigateTo('sejarah')"
                            class="w-full text-left pl-6 pr-3 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="activeTab === 'sejarah' ? 'bg-emerald-50 text-emerald-600' : 'text-slate-600 hover:bg-slate-50'">
                            Sejarah
                        </button>
                        <button @click="navigateTo('struktur')"
                            class="w-full text-left pl-6 pr-3 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="activeTab === 'struktur' ? 'bg-emerald-50 text-emerald-600' : 'text-slate-600 hover:bg-slate-50'">
                            Struktur Organisasi
                        </button>
                    </div>

                    <button @click="scrollToLayananMobile"
                        class="w-full text-left px-3 py-2 rounded-lg font-semibold text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        Layanan PRISMA
                    </button>

                    <div class="pt-2 border-t border-slate-100 sm:hidden">
                        <button @click="goToLogin"
                            class="w-40 bg-emerald-600 text-white font-semibold py-2.5 px-4 rounded-xl text-sm flex items-center justify-center gap-2">
                            <span>Login </span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        </header>

        <!-- CONTENT BODY -->
        <main class="flex-1">

            <!-- TAB 1: BERANDA / HOME -->
            <div v-if="activeTab === 'beranda'">
                <!-- HERO BANNER -->
                <section
                    class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white py-16 md:py-24 overflow-hidden">
                    <div
                        class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(16,185,129,0.15),transparent)]">
                    </div>
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            <div class="lg:col-span-7 text-center lg:text-left space-y-4">
                                <h2 class="text-3xl md:text-5xl font-black tracking-tight leading-tight">
                                    Pelayanan Publik Prima
                                </h2>
                                <p class="text-slate-300 text-sm md:text-base max-w-2xl leading-relaxed">
                                    Selamat Datang di Portal Rumah Tahanan Negara Kelas I Pondok Bambu. Berkomitmen
                                    memberikan pelayanan pemasyarakatan terbaik melalui inovasi digital PRISMA.
                                </p>
                                <div class="pt-2 flex flex-wrap justify-center lg:justify-start gap-3">
                                    <button @click="activeTab = 'gambaran-umum'"
                                        class="cursor-pointer bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all">
                                        Profil Rutan
                                    </button>
                                    <button @click="scrollToLayanan"
                                        class=" cursor-pointer bg-white/10 hover:bg-white/20 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all backdrop-blur-sm">
                                        Akses Layanan
                                    </button>
                                </div>
                            </div>

                            <!-- LOGO CONTAINER (SEKARANG BACKGROUND PUTIH BER-SHADOW) -->
                            <div class="lg:col-span-5 flex justify-center">
                                <div
                                    class="w-64 h-64 md:w-80 md:h-80 bg-white rounded-3xl p-8 border border-slate-100 shadow-2xl flex items-center justify-center transform hover:scale-105 transition-transform duration-300">
                                    <img :src="logoPemasyarakatan" alt="Logo Pemasyarakatan"
                                        class="w-full h-full object-contain drop-shadow-md" />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- PINTASAN LAYANAN PRISMA -->
                <section id="layanan-prisma" class="py-16 bg-slate-50">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="text-center max-w-xl mx-auto mb-10">
                            <h2 class="text-3xl font-black text-slate-900">Layanan Digital PRISMA</h2>
                            <p class="text-slate-500 text-sm mt-2">Silahkan pilih layanan di bawah ini untuk melanjutkan
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                            <!-- Tombol SIPANDA -->
                            <button
                                class="group p-6 bg-white hover:bg-emerald-600 border border-slate-200 hover:border-emerald-600 shadow-sm hover:shadow-xl rounded-2xl transition-all duration-300 flex items-center justify-between text-left cursor-pointer"
                                @click="goToPengajuanBarang">
                                <div class="flex items-center gap-4">
                                    <div class="w-20 h-20 shrink-0 p-1">
                                        <img :src="sipanda" alt="SIPANDA" class="w-full h-full object-contain" />
                                    </div>
                                    <div>
                                        <h3
                                            class="font-bold text-slate-800 group-hover:text-white text-xl transition-colors">
                                            SIPANDA</h3>
                                        <p
                                            class="text-xs text-slate-500 group-hover:text-emerald-100 transition-colors mt-1">
                                            Sistem Informasi Permintaan & Pendistribusian Barang</p>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-6 h-6 text-slate-400 group-hover:text-white transition-colors" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>

                            <!-- Tombol KEPEGAWAIAN -->
                            <button
                                class="group p-6 bg-white hover:bg-emerald-600 border border-slate-200 hover:border-emerald-600 shadow-sm hover:shadow-xl rounded-2xl transition-all duration-300 flex items-center justify-between text-left cursor-pointer"
                                @click="goToLogin">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-20 h-20 shrink-0 rounded-xl bg-emerald-50 group-hover:bg-emerald-500/20 text-emerald-600 group-hover:text-white flex items-center justify-center transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 7.588 15m0 3.72a9.094 9.094 0 0 1-3.741-.479 3 3 0 0 1 4.682-2.72m-.94 3.198-.002.031c0 .225.012.447.037.666A11.944 11.944 0 0 0 12 21c2.17 0 4.207-.576 5.963-1.584A6.062 6.062 0 0 0 18 18.722m-12 0a5.971 5.971 0 0 1 .94-3.197m0 0a5.995 5.995 0 0 1 9.471 0M12 10.5a3 3 0 1 1 0-6 3 3 0 0 1 0 6ZM19.5 8.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM6.75 8.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3
                                            class="font-bold text-slate-800 group-hover:text-white text-xl transition-colors">
                                            Kepegawaian</h3>
                                        <p
                                            class="text-xs text-slate-500 group-hover:text-emerald-100 transition-colors mt-1">
                                            Sistem Informasi Pengelolaan Kepegawaian</p>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-6 h-6 text-slate-400 group-hover:text-white transition-colors" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- TAB 2: GAMBARAN UMUM -->
            <div v-else-if="activeTab === 'gambaran-umum'" class="max-w-5xl mx-auto px-4 py-12">
                <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-6 overflow-hidden">
                    <h2 class="text-3xl font-bold text-slate-900 border-b pb-4">Gambaran Umum</h2>

                    <!-- CONTAINER KONTEN (DENGAN FLOAT IMAGE DI KIRI) -->
                    <div class="space-y-4">
                        <!-- FOTO RUTAN (FLOAT LEFT DI DESKTOP/TABLET) -->
                        <div
                            class="w-full md:w-1/2 lg:w-5/12 float-none md:float-left md:mr-6 md:mb-4 rounded-2xl overflow-hidden shadow-lg border border-slate-100 group">
                            <img :src="gambarRutan" alt="Gedung Rutan Kelas I Pondok Bambu"
                                class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-300" />
                        </div>

                        <!-- PARAGRAF TEKS (MENGALIR DI KANAN DAN DI BAWAH GAMBAR) -->
                        <p class="text-slate-600 leading-relaxed">
                            Rutan Kelas I Pondok Bambu didirikan pada tahun 1974 oleh Pemerintah Daerah (Pemda) DKI
                            Jakarta.
                            Pada awal pembentukannya, rumah tahanan ini ditujukan bagi para pelanggar Perda seperti tuna
                            susila, tuna wisma, gelandangan, dan pengemis. Rutan Kelas I Pondok Bambu berdiri di atas
                            tanah
                            seluas ± 14.945 m² dengan status hak pinjam pakai dari Pemda DKI Jakarta.
                        </p>

                        <p class="text-slate-600 leading-relaxed">
                            Pada tahun 2017, area bangunan Rutan Kelas I Pondok Bambu dibagi menjadi dua bersama Lembaga
                            Pemasyarakatan Perempuan Kelas IIA Jakarta. Pembagian ini berdasarkan Keputusan Kepala
                            Kantor
                            Wilayah Kementerian Hukum dan HAM DKI Jakarta Nomor W.10.PL.05.02-005 Tahun 2017 tertanggal
                            06
                            Januari 2017.
                        </p>

                        <p class="text-slate-600 leading-relaxed">
                            Secara keseluruhan, fasilitas Rutan Kelas I Pondok Bambu terdiri dari gedung perkantoran
                            (Ruang
                            Kerja Pegawai, Poliklinik, Bimbingan Kerja/Bimker, serta fasilitas umum), sarana ibadah (1
                            Masjid, 1 Gereja, Vihara), 1 dapur utama, 4 paviliun hunian, serta 1 paviliun isolasi.
                        </p>

                        <p class="text-slate-600 leading-relaxed">
                            Sebagai Rumah Tahanan Khusus Perempuan di DKI Jakarta, Rutan ini memiliki 4 paviliun utama:
                            <strong class="font-semibold text-slate-800">Paviliun Anggrek</strong> (18 kamar untuk
                            tahanan/narapidana umum, wanita menyusui, hamil, dan lansia),
                            <strong class="font-semibold text-slate-800">Paviliun Bougenville</strong> (5 kamar untuk
                            isolasi),
                            <strong class="font-semibold text-slate-800">Paviliun Cendana</strong> (12 kamar untuk kasus
                            Narkotika/Psikotropika dan 1 kamar Mapenaling), serta
                            <strong class="font-semibold text-slate-800">Paviliun Dahlia</strong> (12 kamar kasus
                            kriminal/narkoba dan 3 kamar khusus anak di bawah umur).
                        </p>

                        <p class="text-slate-600 leading-relaxed">
                            Selain itu, terdapat 3 kamar khusus karantina bagi WBP dengan penyakit menular (HIV, TBC,
                            Hepatitis C) serta 2 kamar Ruang Pelanggaran Tata Tertib (RPTT) atau sel isolasi bagi WBP
                            yang
                            melanggar aturan internal Rutan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- TAB 3: SEJARAH -->
            <div v-else-if="activeTab === 'sejarah'" class="max-w-5xl mx-auto px-4 py-12">
                <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-6">
                    <h2 class="text-3xl font-bold text-slate-900 border-b pb-4">Sejarah Singkat</h2>

                    <p class="text-slate-600 leading-relaxed">
                        Rumah Tahanan Negara Kelas I Pondok Bambu berdiri sejak tahun 1974 atas prakarsa Pemerintah
                        Daerah (Pemda) DKI Jakarta. Pada awal pendiriannya, fasilitas ini berfungsi untuk menampung para
                        pelanggar Peraturan Daerah (Perda), seperti tuna susila, tuna wisma, gelandangan, dan pengemis.
                    </p>

                    <p class="text-slate-600 leading-relaxed">
                        Berdasarkan Keputusan Menteri Kehakiman RI Nomor: M.04.PR.07.03 Tahun 1985 tanggal 20 September
                        1985, bangunan ini resmi dialihfungsikan menjadi Rumah Tahanan Negara Kelas I Pondok Bambu.
                    </p>

                    <p class="text-slate-600 leading-relaxed">
                        Selanjutnya, melalui Keputusan Kepala Kantor Wilayah Kementerian Hukum dan HAM DKI Jakarta Nomor
                        W.10.PL.05.02-005 Tahun 2017 tanggal 06 Januari 2017, area gedung ini ditetapkan untuk dibagi
                        dua penggunaannya bersama Lembaga Pemasyarakatan Perempuan Kelas IIA Jakarta.
                    </p>
                </div>
            </div>

            <!-- TAB 4: STRUKTUR ORGANISASI -->
            <div v-else-if="activeTab === 'struktur'" class="max-w-5xl mx-auto px-4 py-12">
                <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm space-y-6 text-center">
                    <h2 class="text-3xl font-bold text-slate-900 border-b pb-4 text-left">Struktur Organisasi</h2>

                    <div
                        class="p-4 md:p-8 bg-slate-50 rounded-xl border border-dashed border-slate-300 flex flex-col items-center justify-center gap-4">
                        <p class="text-slate-500 font-medium text-sm md:text-base">
                            Bagan Struktur Organisasi Rutan Kelas I Pondok Bambu
                        </p>

                        <!-- WRAPPER GAMBAR: Diperbesar hingga full width container -->
                        <div
                            class="w-full max-w-4xl bg-white rounded-2xl p-2 md:p-4 border border-slate-100 shadow-md flex items-center justify-center overflow-hidden">
                            <img :src="strukturBagan" alt="Struktur Organisasi"
                                class="w-full h-auto max-h-[700px] object-contain cursor-pointer hover:scale-[1.02] transition-transform duration-300" />
                        </div>


                    </div>
                </div>
            </div>

        </main>

        <!-- FOOTER RESMI INSTANSI -->
        <footer class="bg-slate-900 text-slate-400 text-sm border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-white font-bold mb-3">RUTAN KELAS I PONDOK BAMBU</h3>
                    <p class="text-xs leading-relaxed">Jl. Pahlawan Revolusi No.119, Jakarta Timur, DKI Jakarta.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Navigasi Cepat</h4>
                    <ul class="space-y-2 text-xs">
                        <li><button @click="navigateTo('gambaran-umum')"
                                class="cursor-pointer hover:text-white transition-colors">Gambaran Umum</button></li>
                        <li><button @click="navigateTo('sejarah')"
                                class="cursor-pointer hover:text-white transition-colors">Sejarah</button></li>
                        <li><button @click="navigateTo('struktur')"
                                class="cursor-pointer hover:text-white transition-colors">Struktur
                                Organisasi</button></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-3">Kontak & Bantuan</h4>
                    <p class="text-xs">Email: info@rutanpondokbambu.go.id</p>
                    <p class="text-xs mt-1">Telepon: (021) 861-xxxx</p>
                </div>
            </div>
            <div class="border-t border-slate-800 text-center py-4 text-xs text-slate-500">
                © 2026 Rutan Kelas I Pondok Bambu. Hak Cipta Dilindungi Perundang-undangan.
            </div>
        </footer>

    </div>
</template>

<script setup>
import { ref } from 'vue'
import Button from 'primevue/button'
import { useRouter } from 'vue-router'
import logoPemasyarakatan from '@/assets/logo-pemasyarakatan.png'
import strukturBagan from '@/assets/struktur_bagan.png'
import sipanda from '@/assets/sipanda.png'
import gambarRutan from '@/assets/gambarRutan.jpg'

const router = useRouter()
const activeTab = ref('beranda')
const isMobileMenuOpen = ref(false)

const goToLogin = () => {
    isMobileMenuOpen.value = false
    router.push('/login')
}

const goToPengajuanBarang = () => {
    router.push('/requests')
}

const navigateTo = (tab) => {
    activeTab.value = tab
    isMobileMenuOpen.value = false
}

const scrollToLayanan = () => {
    activeTab.value = 'beranda'
    setTimeout(() => {
        const el = document.getElementById('layanan-prisma')
        if (el) el.scrollIntoView({ behavior: 'smooth' })
    }, 100)
}

const scrollToLayananMobile = () => {
    isMobileMenuOpen.value = false
    scrollToLayanan()
}
</script>