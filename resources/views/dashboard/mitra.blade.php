<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mitra - NongkrongID</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .page-section { display: none; }
        .page-section.active { display: block; }
    </style>
    <!-- ===== TEMA TAMPILAN NongkrongID (hanya visual, tidak mengubah markup/script) ===== -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body.antialiased { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; background-color: #F5F7FB; color: #1E293B; }
        h1, h2, h3 { font-family: 'Bricolage Grotesque', 'Plus Jakarta Sans', ui-sans-serif, sans-serif; letter-spacing: -.01em; }

        /* Navbar: gradasi biru tua, sama dengan landing page */
        body > nav.sticky { background-image: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); box-shadow: 0 6px 20px -8px rgba(30, 58, 138, .55); }
        body > nav.sticky > div.border-t { background-color: rgba(8, 20, 60, .28); border-color: rgba(255, 255, 255, .12); }
        body > nav.sticky input[type="text"] { background-color: #fff; }
        body > nav.sticky input[type="text"]:focus { box-shadow: 0 0 0 2px #FCD34D; }

        /* Tab aktif di sub-navigasi: garis aksen amber */
        .nav-link { border-bottom: 3px solid transparent; padding-bottom: 2px; }
        .nav-link.border-b-2 { border-bottom-width: 3px; border-color: #FBBF24; }

        /* Kartu: border lembut + bayangan berlapis */
        main div.bg-white.rounded-2xl, main div.bg-white.rounded-3xl {
            border-color: #E6EBF3;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 14px 32px -18px rgba(30, 58, 138, .22);
        }
        main section > div.border-dashed { background-color: rgba(255, 255, 255, .6); }

        /* Modal & transisi antar-section (respon atas aksi pengguna) */
        @keyframes nid-pop { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }
        @keyframes nid-fade { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
        [id$="Modal"] > div { border-radius: 1.5rem; animation: nid-pop .22s ease-out both; }
        .page-section.active { animation: nid-fade .25s ease-out both; }

        :focus-visible { outline: 2px solid #F59E0B; outline-offset: 2px; }
        @media (prefers-reduced-motion: reduce) {
            [id$="Modal"] > div, .page-section.active { animation: none; }
        }
    </style>
    <!-- ===== AKHIR TEMA ===== -->
</head>
<body class="bg-[#F8F9FA] text-gray-800 font-sans antialiased pb-10">

    <!-- SCRIPT PENJAGA: Cek Token & Isolasi Role Mitra -->
    <script>
        const token = localStorage.getItem('access_token');
        if (!token) {
            alert("Silakan login terlebih dahulu!");
            window.location.href = "/";
        }

        const jenisAkunId = localStorage.getItem('jenis_akun_id');
        if (jenisAkunId === '2') {
            alert("Akses ditolak! Halaman ini khusus untuk akun Mitra.");
            window.location.href = "/dashboard/user";
        }
    </script>

    <!-- 1. HEADER & NAVBAR UTAMA MITRA -->
    <nav class="bg-blue-600 text-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            <!-- Kiri: Logo -->
            <div class="flex items-center space-x-2 flex-shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo NongkrongID" class="h-9 w-9 object-contain flex-shrink-0">
                <span class="font-bold text-base sm:text-lg tracking-wide">NongkrongID</span>
            </div>

            <!-- Kanan: Search Bar, GPS, & Kategori -->
            <div class="flex-1 max-w-xl flex items-center gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" id="searchInput" placeholder="Cari kafe, data toko, atau analitik..." class="w-full pl-9 pr-4 py-2 rounded-full text-xs sm:text-sm text-gray-700 focus:outline-none shadow-inner">
                </div>

                <!-- Tombol GPS -->
                <button id="gpsBtn" onclick="toggleGps()" class="bg-blue-700 hover:bg-blue-800 text-white px-3.5 py-2 rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow-sm border border-blue-500/50 flex-shrink-0" title="Aktifkan/Nonaktifkan Lokasi">
                    <i class="fa-solid fa-location-dot"></i>
                    <span class="hidden sm:inline">GPS</span>
                </button>

                <!-- Tombol Kategori -->
                <button onclick="openCategoryModal()" class="bg-blue-700 hover:bg-blue-800 text-white px-3.5 py-2 rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow-sm border border-blue-500/50 flex-shrink-0">
                    <i class="fa-solid fa-list"></i>
                    <span class="hidden sm:inline">Kategori</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Baris Bawah (Sub-Navigasi): 5 Item Navigasi -->
        <div class="bg-blue-700/80 border-t border-blue-500/40">
            <div class="max-w-4xl mx-auto px-4 flex items-center justify-center space-x-6 sm:space-x-10 py-2.5 text-xs sm:text-sm font-medium overflow-x-auto">
                <a href="#dashboard" onclick="switchSection('dashboard', event)" class="nav-link flex items-center gap-1.5 hover:text-blue-200 transition font-bold border-b-2 border-white pb-0.5 whitespace-nowrap" data-target="section-dashboard">
                    <i class="fa-solid fa-house"></i> <span>Dashboard</span>
                </a>
                <a href="#toko" onclick="switchSection('toko', event)" class="nav-link flex items-center gap-1.5 hover:text-blue-200 transition opacity-80 hover:opacity-100 whitespace-nowrap" data-target="section-toko">
                    <i class="fa-solid fa-shop"></i> <span>Data Toko</span>
                </a>
                <a href="#analitik" onclick="switchSection('analitik', event)" class="nav-link flex items-center gap-1.5 hover:text-blue-200 transition opacity-80 hover:opacity-100 whitespace-nowrap" data-target="section-analitik">
                    <i class="fa-solid fa-chart-line"></i> <span>Analitik</span>
                </a>
                <a href="#iklan" onclick="switchSection('iklan', event)" class="nav-link flex items-center gap-1.5 hover:text-blue-200 transition opacity-80 hover:opacity-100 whitespace-nowrap" data-target="section-iklan">
                    <i class="fa-solid fa-rectangle-ad"></i> <span>Ajukan Iklan</span>
                </a>
                <a href="#akun" onclick="switchSection('akun', event)" class="nav-link flex items-center gap-1.5 hover:text-blue-200 transition opacity-80 hover:opacity-100 whitespace-nowrap" data-target="section-akun">
                    <i class="fa-solid fa-user"></i> <span>Akun</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTAINER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-4">

        <!-- ================= SECTION 1: DASHBOARD ================= -->
        <div id="section-dashboard" class="page-section active space-y-4">
            <section class="space-y-2">
                <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-1.5">
                    Informasi & Promo Mitra <span class="text-sm">✨</span>
                </h2>
                <div id="iklanBanner" class="w-full bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 p-3">
                    <div class="w-full h-32 sm:h-48 rounded-xl bg-gray-50 border border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 gap-1">
                        <i class="fa-regular fa-image text-2xl text-gray-300"></i>
                        <p class="text-xs italic">(Area slide banner promo / informasi khusus mitra - dikosongkan)</p>
                    </div>
                </div>
            </section>

            <section class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200 pb-3">
                    <div>
                        <h3 id="filterTitle" class="text-lg sm:text-xl font-extrabold text-gray-900 flex items-center gap-1.5"></h3>
                        <p id="filterSubtitle" class="text-xs sm:text-sm text-gray-500 mt-0.5"></p>
                    </div>
                    <div id="filterButtons" class="flex items-center space-x-2 pt-1 sm:pt-0"></div>
                </div>
                
                <div id="catalogContainer" class="bg-white rounded-2xl p-6 sm:p-10 text-center border border-gray-100 shadow-sm min-h-[150px] flex items-center justify-center">
                    <p class="text-xs sm:text-sm text-gray-400 italic">(Area katalog daftar tempat / kafe Anda - dikosongkan sementara)</p>
                </div>
            </section>
        </div>

        <!-- ================= SECTION 2: DATA TOKO ================= -->
        <div id="section-toko" class="page-section space-y-4">
            @include('dashboard.toko')
        </div>

        <!-- ================= SECTION 3: ANALITIK ================= -->
        <div id="section-analitik" class="page-section space-y-4">
            <div id="analitikContent"></div>
        </div>

        <!-- ================= SECTION 4: AJUKAN IKLAN ================= -->
        <div id="section-iklan" class="page-section space-y-4">
            <div id="iklanContent"></div>
        </div>

        <!-- ================= SECTION 5: AKUN ================= -->
        <div id="section-akun" class="page-section space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 max-w-5xl mx-auto">
                
                <!-- Header Section & Tombol Aksi Utama -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-user-gear text-blue-600"></i> Pengaturan Akun Mitra
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Kelola informasi profil dan status kemitraan Anda.</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button onclick="openPremiumModal()" class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-1.5">
                            <i class="fa-solid fa-crown text-yellow-300"></i> Pembelian Premium
                        </button>
                        
                        <button onclick="handleLogout()" class="bg-red-500 hover:bg-red-600 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-1.5">
                            <i class="fa-solid fa-right-from-bracket"></i> Keluar
                        </button>
                    </div>
                </div>

                <!-- Wadah Pesan Notifikasi / Error -->
                <div id="accountAlertContainer" class="mt-3 hidden"></div>

                <!-- Kotak Konfirmasi Keluar (Inline Message) -->
                <div id="logoutConfirmationBox" class="hidden mt-3 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 space-y-2">
                    <p class="text-xs font-bold flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-question text-amber-600"></i> Apakah anda yakin untuk keluar dari akun anda?
                    </p>
                    <div class="flex items-center gap-2 pt-1">
                        <button onclick="executeLogout()" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                            Ya, Keluar
                        </button>
                        <button onclick="cancelLogout()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                            Batal
                        </button>
                    </div>
                </div>

                <!-- Form Profil Mitra Layout Kesamping (Grid 2 Kolom) -->
                <form id="profileForm" onsubmit="handleProfileSubmit(event)" class="mt-4">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                        
                        <!-- KOLOM KIRI: Foto Profil & Status Akun -->
                        <div class="lg:col-span-4 bg-gray-50/70 p-3.5 rounded-2xl border border-gray-100 flex flex-col items-center text-center space-y-2.5">
                            <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-full overflow-hidden border-4 border-white shadow-md flex-shrink-0 group cursor-pointer" onclick="triggerImageUpload()" title="Klik untuk mengubah foto profil">
                                <img id="profilePreview" src="/images/profile/default.png" alt="Foto Profil" class="w-full h-full object-cover transition group-hover:opacity-75">
                                <div id="photoOverlay" class="absolute inset-0 bg-black/40 flex items-center justify-center text-white opacity-0 transition group-hover:opacity-100 hidden">
                                    <i class="fa-solid fa-camera text-base"></i>
                                </div>
                            </div>
                            <div class="space-y-1 w-full">
                                <label class="block text-xs font-bold text-gray-700">Foto Profil</label>
                                <p class="text-[10px] text-gray-500">Klik foto untuk mengganti saat mode perbarui aktif.</p>
                                <input type="file" id="inputGambar" accept="image/jpeg,image/png,image/jpg" class="hidden" onchange="previewSelectedImage(this)">
                                <span id="fileNameLabel" class="text-[10px] text-blue-600 font-medium block truncate"></span>
                            </div>

                            <!-- Info Status Akun Compact -->
                            <div class="w-full pt-2 border-t border-gray-200/60 text-left space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 font-medium">Status:</span>
                                    <span id="textJenisAkun" class="font-bold text-blue-700">Memuat...</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 font-medium">Masa Berakhir:</span>
                                    <span id="badgeMasaAktif" class="font-semibold text-gray-700">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM KANAN: Data Form Input -->
                        <div class="lg:col-span-8 space-y-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Nama Lengkap -->
                                <div class="space-y-1">
                                    <label class="block text-[11px] font-bold text-gray-700">Nama Lengkap <span class="text-gray-400 font-normal">(Min. 3 huruf)</span></label>
                                    <input type="text" id="inputNama" disabled required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs bg-gray-50 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-80">
                                </div>

                                <!-- Email -->
                                <div class="space-y-1">
                                    <label class="block text-[11px] font-bold text-gray-700">Email <span class="text-[10px] font-normal text-red-500">(Terkunci)</span></label>
                                    <input type="email" id="inputEmail" disabled readonly class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs bg-gray-100 text-gray-500 cursor-not-allowed">
                                </div>

                                <!-- No Telepon -->
                                <div class="space-y-1">
                                    <label class="block text-[11px] font-bold text-gray-700">Nomor Telepon <span class="text-gray-400 font-normal">(10-13 digit)</span></label>
                                    <input type="text" id="inputTelepon" disabled required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs bg-gray-50 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-80">
                                </div>

                                <!-- Password -->
                                <div class="space-y-1">
                                    <label class="block text-[11px] font-bold text-gray-700">Password</label>
                                    <div class="relative">
                                        <input type="password" id="inputPassword" disabled placeholder="••••••••" class="w-full px-3 py-2 pr-9 rounded-xl border border-gray-200 text-xs bg-gray-50 text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-80">
                                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer">
                                            <i id="eyeIcon" class="fa-solid fa-eye-slash text-xs"></i>
                                        </button>
                                    </div>
                                    <p id="passwordHint" class="text-[10px] text-gray-400 hidden">Kosongkan jika tidak ingin ganti (Min. 8 karakter).</p>
                                </div>
                            </div>

                            <!-- Kotak Konfirmasi Persetujuan -->
                            <div id="confirmationBox" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 space-y-1">
                                <p class="text-xs font-bold flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-question text-amber-600"></i> Apakah Anda menyetujui perubahan data anda?
                                </p>
                                <p class="text-[11px] text-amber-700">Periksa kembali sebelum menekan tombol setuju.</p>
                            </div>

                            <!-- Tombol Aksi Form -->
                            <div class="flex flex-wrap justify-end gap-2 pt-1">
                                <button type="button" id="btnCancelEdit" onclick="cancelEditMode()" class="hidden bg-gray-100 hover:bg-gray-200 text-gray-700 px-3.5 py-2 rounded-xl text-xs font-bold transition">
                                    Batal
                                </button>

                                <button type="button" id="btnEditMode" onclick="enableEditMode()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                    <i class="fa-solid fa-pen-to-square"></i> Perbarui Profil
                                </button>

                                <button type="submit" id="btnSaveProfile" class="hidden bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                    <i class="fa-solid fa-check"></i> Setuju & Update
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    </main>

    <!-- MODAL POPUP KATEGORI -->
    <div id="categoryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-blue-600"></i> Pilih Kategori Tempat
                </h3>
                <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="space-y-2 max-h-60 overflow-y-auto text-xs font-medium pr-1" id="categoryList"><p class="text-gray-400 italic">Memuat kategori...</p></div>
            <div class="pt-2 flex gap-2">
                <button onclick="closeCategoryModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 rounded-xl text-xs transition">Batal</button>
                <button onclick="applyCategories()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl text-xs transition shadow-md">Terapkan Filter</button>
            </div>
        </div>
    </div>

    <!-- SCRIPT UTAMA -->
    <!-- SCRIPT UTAMA -->
    <script>window.KATALOG_OPTIONS = { container: 'catalogContainer', showRating: false };</script>
    <script>document.addEventListener('DOMContentLoaded', () => { if (window.Katalog && Katalog.bindSearch) Katalog.bindSearch({ inputs: ['searchInput'], withCategory: true, withGps: true }); else console.error('katalog.js tidak termuat atau masih versi lama (Katalog.bindSearch tidak ditemukan)'); });</script>
    <script src="{{ asset('js/katalog.js') }}?v={{ @filemtime(public_path('js/katalog.js')) }}"></script>
    <script src="{{ asset('js/toko-detail.js') }}?v={{ @filemtime(public_path('js/toko-detail.js')) }}"></script>
    <script src="{{ asset('js/iklan-banner.js') }}?v={{ @filemtime(public_path('js/iklan-banner.js')) }}"></script>
    <script src="{{ asset('js/iklan-pengajuan.js') }}?v={{ @filemtime(public_path('js/iklan-pengajuan.js')) }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <script>

        // ===== ANALITIK (khusus Mitra Premium: jenis akun 4, 5, 6) =====

        function analitikLockedHtml() {

            return `

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm min-h-[380px] flex flex-col items-center justify-center text-center p-8">

                <div class="w-20 h-20 rounded-full bg-gray-100 grid place-items-center mb-5"><i class="fa-solid fa-lock text-3xl text-gray-400"></i></div>

                <h3 class="text-lg font-extrabold text-gray-800">Analitik Khusus Mitra Premium</h3>

                <p class="text-sm text-gray-500 mt-2 max-w-sm">Teruntuk akses lebih luas anda dapat bergabung ke Premium</p>

            </div>`;

        }



        async function loadAnalitik() {

            const box = document.getElementById('analitikContent');

            if (!box) return;

            const jenis = Number(localStorage.getItem('jenis_akun_id'));

            if (![4, 5, 6].includes(jenis)) { box.innerHTML = analitikLockedHtml(); return; }



            box.innerHTML = '<div class="bg-white rounded-2xl p-10 text-center border border-gray-100 text-xs text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat analitik...</div>';

            try {

                const res = await fetch('/api/mitra/analitik', { headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json' } });

                const json = await res.json().catch(() => ({}));

                if (res.status === 403 && json.locked) { box.innerHTML = analitikLockedHtml(); return; }

                if (!res.ok) throw new Error(json.message || 'Gagal memuat analitik.');

                renderAnalitik(box, json.data);

            } catch (e) {

                box.innerHTML = `<div class="bg-white rounded-2xl p-8 text-center border border-red-100 text-xs text-red-500">${String(e.message).replace(/[<>&]/g, '')} <button class="underline font-bold" onclick="loadAnalitik()">Coba lagi</button></div>`;

            }

        }



        function renderAnalitik(box, d) {

            const nf = n => new Intl.NumberFormat('id-ID').format(n);

            const growthBadge = (g, future, klik) => {

                if (future) return '<span class="text-gray-300">-</span>';

                if (g === null) return klik > 0 ? '<span class="text-blue-600 font-bold">Baru</span>' : '<span class="text-gray-300">-</span>';

                if (g > 0) return `<span class="text-green-600 font-bold"><i class="fa-solid fa-arrow-up text-[10px]"></i> ${g}%</span>`;

                if (g < 0) return `<span class="text-red-500 font-bold"><i class="fa-solid fa-arrow-down text-[10px]"></i> ${Math.abs(g)}%</span>`;

                return '<span class="text-gray-400 font-bold">0%</span>';

            };

            const totalTahunIni = d.tahunan[0].total_klik;

            const yoy = d.pertumbuhan_tahunan_persen;

            const yoyText = yoy === null ? 'Baru' : (yoy > 0 ? '+' : '') + yoy + '%';

            const yoyColor = yoy === null || yoy > 0 ? 'text-green-600' : (yoy < 0 ? 'text-red-500' : 'text-gray-500');

            const card = (icon, color, label, value, sub = '') => `

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">

                    <div class="flex items-center gap-2 text-xs font-bold text-gray-500"><span class="w-7 h-7 rounded-lg ${color} grid place-items-center"><i class="fa-solid ${icon} text-xs"></i></span>${label}</div>

                    <div class="text-2xl font-extrabold text-gray-900 mt-2">${value}</div>

                    ${sub ? `<div class="text-[11px] text-gray-400 mt-0.5">${sub}</div>` : ''}

                </div>`;

            const maxTahun = Math.max(1, ...d.tahunan.map(t => t.total_klik));



            box.innerHTML = `

            <div class="space-y-4 max-w-5xl">

                <div>

                    <h3 class="text-lg sm:text-xl font-extrabold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-chart-line text-blue-600"></i> Analitik Kinerja Toko</h3>

                    <p class="text-xs text-gray-500 mt-0.5">${d.nama_toko ? d.nama_toko.replace(/[<>&]/g, '') + ' &middot; ' : ''}Data tahun ${d.tahun}</p>

                </div>



                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

                    ${card('fa-hand-pointer', 'bg-blue-100 text-blue-600', 'Klik Tahun Ini', nf(totalTahunIni), 'dari akun lain')}

                    ${card('fa-star', 'bg-amber-100 text-amber-500', 'Rata-rata Bintang', Number(d.rating_rata_rata).toFixed(1), nf(d.jumlah_rating) + ' penilaian')}

                    ${card('fa-heart', 'bg-red-100 text-red-500', 'Disimpan Orang Lain', nf(d.total_disimpan), 'masuk daftar favorit')}

                    ${card('fa-arrow-trend-up', 'bg-green-100 text-green-600', 'Pertumbuhan Tahunan', `<span class="${yoyColor}">${yoyText}</span>`, 'Jan–bulan ini vs periode sama tahun lalu')}

                </div>



                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">

                    <div class="text-sm font-extrabold text-gray-800 mb-1">Diagram Pertumbuhan Pemasaran</div>

                    <p class="text-[11px] text-gray-400 mb-3">Batang: total klik per bulan. Garis: pertumbuhan dibanding bulan sebelumnya (%).</p>

                    <div class="relative h-64"><canvas id="chartAnalitik"></canvas></div>

                </div>



                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">

                        <div class="text-sm font-extrabold text-gray-800 mb-3">Total Klik per Bulan (${d.tahun})</div>

                        <table class="w-full text-sm">

                            <thead><tr class="text-[11px] uppercase text-gray-400 text-left"><th class="pb-2">Bulan</th><th class="pb-2 text-right">Klik</th><th class="pb-2 text-right">Pertumbuhan</th></tr></thead>

                            <tbody>${d.bulanan.map(b => `

                                <tr class="border-t border-gray-100 ${b.akan_datang ? 'text-gray-300' : 'text-gray-700'}">

                                    <td class="py-1.5 font-semibold">${b.bulan}</td>

                                    <td class="py-1.5 text-right font-bold">${b.akan_datang ? '-' : nf(b.total_klik)}</td>

                                    <td class="py-1.5 text-right text-xs">${growthBadge(b.pertumbuhan_persen, b.akan_datang, b.total_klik)}</td>

                                </tr>`).join('')}</tbody>

                        </table>

                    </div>



                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">

                        <div class="text-sm font-extrabold text-gray-800 mb-3">Klik Tahunan</div>

                        <div class="space-y-4">${d.tahunan.map((t, i) => `

                            <div>

                                <div class="flex justify-between text-sm"><span class="font-semibold text-gray-700">${t.tahun}${i === 0 ? ' <span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full ml-1">Tahun ini</span>' : ''}</span><span class="font-extrabold text-gray-900">${nf(t.total_klik)}</span></div>

                                <div class="h-2.5 bg-gray-100 rounded-full mt-1.5 overflow-hidden"><div class="h-full rounded-full ${i === 0 ? 'bg-blue-600' : 'bg-blue-300'}" style="width:${Math.round(t.total_klik / maxTahun * 100)}%"></div></div>

                            </div>`).join('')}

                        </div>

                        <p class="text-[11px] text-gray-400 mt-5">Rumus pertumbuhan = (nilai sekarang − nilai sebelumnya) ÷ nilai sebelumnya × 100. "Baru" berarti periode sebelumnya belum ada klik.</p>

                    </div>

                </div>

            </div>`;



            if (window._chartAnalitik) { window._chartAnalitik.destroy(); window._chartAnalitik = null; }

            const canvas = document.getElementById('chartAnalitik');

            if (!window.Chart) { canvas.parentElement.innerHTML = '<p class="text-xs text-gray-400 italic text-center pt-20">Diagram tidak dapat dimuat (Chart.js tidak tersedia).</p>'; return; }

            window._chartAnalitik = new Chart(canvas, {

                data: {

                    labels: d.bulanan.map(b => b.bulan.slice(0, 3)),

                    datasets: [

                        { type: 'bar', label: 'Total klik', data: d.bulanan.map(b => b.akan_datang ? null : b.total_klik), backgroundColor: 'rgba(37,99,235,.75)', borderRadius: 6, yAxisID: 'y' },

                        { type: 'line', label: 'Pertumbuhan (%)', data: d.bulanan.map(b => b.akan_datang ? null : b.pertumbuhan_persen), borderColor: '#f59e0b', backgroundColor: '#f59e0b', tension: .3, pointRadius: 3, spanGaps: false, yAxisID: 'y1' }

                    ]

                },

                options: {

                    responsive: true, maintainAspectRatio: false,

                    interaction: { mode: 'index', intersect: false },

                    scales: {

                        y:  { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Klik' } },

                        y1: { position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: '%' } }

                    }

                }

            });

        }

    </script>
    <script>
        function switchSection(targetId, event) {
            if (event) event.preventDefault();

            // Cek apakah toko terkunci (untuk mitra baru yang belum isi toko)
            const jenisAkunId = Number(localStorage.getItem('jenis_akun_id'));
            if ([3, 4, 5, 6].includes(jenisAkunId) && window.isTokoLocked && targetId !== 'toko') {
                alert("Akses dibatasi! Anda wajib mengisi dan menyimpan Data Toko terlebih dahulu.");
                return;
            }

            document.querySelectorAll('.page-section').forEach(sec => {
                sec.classList.remove('active');
            });

            const targetSection = document.getElementById('section-' + targetId);
            if (targetSection) {
                targetSection.classList.add('active');
            }

            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('font-bold', 'border-b-2', 'border-white', 'pb-0.5');
                link.classList.add('opacity-80');
            });

            if (event && event.currentTarget) {
                event.currentTarget.classList.add('font-bold', 'border-b-2', 'border-white', 'pb-0.5');
                event.currentTarget.classList.remove('opacity-80');
            }

            if (targetId === 'analitik' && window.loadAnalitik) loadAnalitik();
            if (targetId === 'iklan' && window.loadIklan) loadIklan();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // GPS, kategori & pencarian ditangani oleh public/js/katalog.js
        function toggleGps() { Katalog.toggleGps(); }
        function openCategoryModal() { Katalog.openCategories(); }
        function closeCategoryModal() { Katalog.closeCategories(); }
        function applyCategories() { Katalog.applyCategories(); }

        const filterConfigs = {
            rekomendasi: {
                title: "Tempat Rekomendasi 👋",
                subtitle: "Kelola dan pantau tempat terbaikmu",
                buttons: [
                    { id: 'terdekat', label: 'Terdekat 🔵', style: 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm' },
                    { id: 'general', label: 'General 🔵', style: 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 shadow-sm' }
                ]
            },
            terdekat: {
                title: "Tempat Terdekat 🗾",
                subtitle: "Pantau lokasi tempat terdekatmu",
                buttons: [
                    { id: 'rekomendasi', label: 'Rekomendasi 🔵', style: 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm' },
                    { id: 'general', label: 'General 🔵', style: 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 shadow-sm' }
                ]
            },
            general: {
                title: "Menjelajah Tempat 👋",
                subtitle: "Kelola berbagai macam tempat usaha",
                buttons: [
                    { id: 'rekomendasi', label: 'Rekomendasi 🔵', style: 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm' },
                    { id: 'terdekat', label: 'Terdekat 🔵', style: 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 shadow-sm' }
                ]
            }
        };

        function setFilter(type) {
            const config = filterConfigs[type];
            if (!config) return;
            document.getElementById('filterTitle').innerText = config.title;
            document.getElementById('filterSubtitle').innerText = config.subtitle;
            const btnContainer = document.getElementById('filterButtons');
            btnContainer.innerHTML = '';
            config.buttons.forEach(btn => {
                const buttonEl = document.createElement('button');
                buttonEl.className = `${btn.style} text-xs px-3.5 py-1.5 rounded-xl font-medium transition`;
                buttonEl.innerText = btn.label;
                buttonEl.onclick = () => setFilter(btn.id);
                btnContainer.appendChild(buttonEl);
            });
            if (window.Katalog) Katalog.load(type);
        }

        // Status Kunci Toko Global
        window.isTokoLocked = false;

        document.addEventListener('DOMContentLoaded', () => {
            setFilter('rekomendasi');
            loadProfileData();
            
            // Default selalu buka dashboard dulu di awal agar tidak blank
            switchSection('dashboard');

            // Cek status toko & kunci navigasi jika belum diisi untuk akun Mitra
            const jenisAkunId = Number(localStorage.getItem('jenis_akun_id'));
            if ([3, 4, 5, 6].includes(jenisAkunId)) {
                checkAndLockForMitra();
            } else {
                if (typeof initTokoModule === 'function') initTokoModule();
            }
        });

        // Fungsi Pengecekan & Penguncian Otomatis yang Disinkronkan
        function checkAndLockForMitra() {
            const token = localStorage.getItem('access_token');
            
            if (typeof initTokoModule === 'function') {
                initTokoModule();
            }

            const apiUrl = window.location.origin + '/api/mitra/toko';

            fetch(apiUrl, {
                method: 'GET',
                headers: { 
                    'Authorization': 'Bearer ' + token, 
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(async res => {
                // 1. Jika status HTTP bukan 200 (misal 404 Not Found karena toko belum dibuat), langsung kunci!
                if (!res.ok) {
                    triggerTokoLockMode();
                    return;
                }

                const data = await res.json();
                
                // 2. Pastikan data toko benar-benar ada dan valid
                if (data && data.success && data.data) {
                    window.isTokoLocked = false;
                    const badge = document.getElementById('tokoStatusBadge');
                    if(badge) {
                        badge.className = "text-xs font-bold px-3 py-1.5 rounded-xl bg-green-50 text-green-700 border border-green-200 inline-flex items-center gap-1.5";
                        badge.innerHTML = '<i class="fa-solid fa-circle-check"></i> Data Toko Aktif (Dapat Diperbarui)';
                    }
                    const btnSub = document.getElementById('btnTokoSubmit');
                    if(btnSub) btnSub.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Perbarui Data Toko';

                    setTimeout(() => {
                        if (typeof fillTokoForm === 'function') {
                            fillTokoForm(data.data);
                        }
                    }, 200);
                } else {
                    // 3. Jika respons sukses tapi data kosong/false, kunci paksa
                    triggerTokoLockMode();
                }
            })
            .catch(err => {
                console.warn("Koneksi gagal atau toko belum ada:", err.message);
                // 4. Jika fetch error / gagal total, pastikan tetap dikunci
                triggerTokoLockMode();
            });
        }
        
        function triggerTokoLockMode() {
            window.isTokoLocked = true;
            
            // 1. Sembunyikan semua section terlebih dahulu
            document.querySelectorAll('.page-section').forEach(sec => {
                sec.classList.remove('active');
            });

            // 2. Tampilkan secara paksa section data toko agar tidak putih kosong
            const tokoSection = document.getElementById('section-toko');
            if (tokoSection) {
                tokoSection.classList.add('active');
            }

            // 3. Sesuaikan tampilan menu navigasi (Kunci menu lain, aktifkan menu Data Toko)
            document.querySelectorAll('.nav-link').forEach(link => {
                const target = link.getAttribute('data-target');
                if (target === 'section-toko') {
                    link.classList.add('font-bold', 'border-b-2', 'border-white', 'pb-0.5');
                    link.classList.remove('opacity-80');
                } else {
                    link.style.opacity = '0.3';
                    link.style.pointerEvents = 'none';
                    link.title = "Anda wajib melengkapi Data Toko terlebih dahulu!";
                }
            });
        }

        let currentProfileData = null;

        function loadProfileData() {
            const token = localStorage.getItem('access_token');
            if (!token) return;

            fetch('/api/akun/profile', {
                method: 'GET',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(res => {
                if (res.data) {
                    currentProfileData = res.data;
                    renderProfileView(res.data);
                }
            })
            .catch(err => console.error("Gagal memuat profil:", err));
        }

        function renderProfileView(data) {
            const elNama = document.getElementById('inputNama');
            const elEmail = document.getElementById('inputEmail');
            const elTelepon = document.getElementById('inputTelepon');
            if(elNama) elNama.value = data.nama_lengkap || '';
            if(elEmail) elEmail.value = data.email || '';
            if(elTelepon) elTelepon.value = data.no_telefon || '';
            
            const preview = document.getElementById('profilePreview');
            if(preview) {
                preview.src = data.path_gambar ? `/images/profile/${data.path_gambar}` : '/images/profile/default.png';
            }

            let jenisAkunNama = 'Mitra';
            const jenisId = Number(data.jenis_akun_id_jenis_akun);
            
            if ([4, 5, 6].includes(jenisId)) {
                jenisAkunNama = 'Mitra Premium';
            } else if (data.jenis_akun && data.jenis_akun.jenis_akun) {
                jenisAkunNama = data.jenis_akun.jenis_akun;
            }
            
            const elJenis = document.getElementById('textJenisAkun');
            if(elJenis) elJenis.innerText = jenisAkunNama;

            const elMasa = document.getElementById('badgeMasaAktif');
            if(elMasa) {
                elMasa.innerText = data.masa_berakhir ? data.masa_berakhir.split(' ')[0] : 'Tidak terbatas';
            }
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('inputPassword');
            const eyeIcon = document.getElementById('eyeIcon');
            if(!passwordInput) return;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'fa-solid fa-eye text-xs';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'fa-solid fa-eye-slash text-xs';
            }
        }

        function triggerImageUpload() {
            const inputNama = document.getElementById('inputNama');
            const isEditing = inputNama && !inputNama.disabled;
            if (isEditing) {
                document.getElementById('inputGambar').click();
            } else {
                showAccountAlert('error', 'Silakan klik tombol "Perbarui Profil" terlebih dahulu untuk mengubah foto.');
            }
        }

        function previewSelectedImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
                document.getElementById('fileNameLabel').innerText = input.files[0].name;
            }
        }

        function enableEditMode() {
            clearAccountAlert();
            const inputNama = document.getElementById('inputNama');
            const nama = inputNama ? inputNama.value.trim() : '';
            const telepon = document.getElementById('inputTelepon').value.trim();

            if (!inputNama.disabled) {
                const password = document.getElementById('inputPassword').value;
                if (nama.length < 3) {
                    showAccountAlert('error', 'Nama lengkap minimal harus 3 huruf.');
                    return;
                }
                if (telepon.length < 10 || telepon.length > 13) {
                    showAccountAlert('error', 'Nomor telepon harus terdiri dari 10 hingga 13 digit.');
                    return;
                }
                if (password && password.length < 8) {
                    showAccountAlert('error', 'Password minimal harus 8 karakter.');
                    return;
                }

                document.getElementById('confirmationBox').classList.remove('hidden');
                document.getElementById('btnEditMode').classList.add('hidden');
                document.getElementById('btnSaveProfile').classList.remove('hidden');
                showAccountAlert('success', 'Silakan periksa kembali data Anda dan klik "Setuju & Update" untuk menyimpan.');
                return;
            }

            inputNama.disabled = false;
            document.getElementById('inputTelepon').disabled = false;
            document.getElementById('inputPassword').disabled = false;
            
            inputNama.classList.remove('bg-gray-50');
            document.getElementById('inputTelepon').classList.remove('bg-gray-50');
            document.getElementById('inputPassword').classList.remove('bg-gray-50');

            document.getElementById('photoOverlay').classList.remove('hidden');
            document.getElementById('passwordHint').classList.remove('hidden');

            const btnEdit = document.getElementById('btnEditMode');
            btnEdit.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Konfirmasi Perubahan';
            document.getElementById('btnCancelEdit').classList.remove('hidden');
        }

        function cancelEditMode() {
            document.getElementById('inputNama').disabled = true;
            document.getElementById('inputTelepon').disabled = true;
            document.getElementById('inputPassword').disabled = true;

            document.getElementById('inputNama').classList.add('bg-gray-50');
            document.getElementById('inputTelepon').classList.add('bg-gray-50');
            document.getElementById('inputPassword').classList.add('bg-gray-50');

            document.getElementById('photoOverlay').classList.add('hidden');
            document.getElementById('passwordHint').classList.add('hidden');
            document.getElementById('confirmationBox').classList.add('hidden');
            document.getElementById('fileNameLabel').innerText = '';

            const btnEdit = document.getElementById('btnEditMode');
            btnEdit.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Perbarui Profil';
            btnEdit.classList.remove('hidden');

            document.getElementById('btnCancelEdit').classList.add('hidden');
            document.getElementById('btnSaveProfile').classList.add('hidden');

            if (currentProfileData) {
                renderProfileView(currentProfileData);
            }
            clearAccountAlert();
        }

        function handleProfileSubmit(event) {
            event.preventDefault();
            clearAccountAlert();

            const token = localStorage.getItem('access_token');
            const nama = document.getElementById('inputNama').value.trim();
            const telepon = document.getElementById('inputTelepon').value.trim();
            const password = document.getElementById('inputPassword').value;
            const fileGambar = document.getElementById('inputGambar').files[0];

            const formData = new FormData();
            formData.append('nama_lengkap', nama);
            formData.append('no_telefon', telepon);
            if (password) formData.append('password', password);
            if (fileGambar) formData.append('path_gambar', fileGambar);

            fetch('/api/akun/update', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    let errorMsg = data.message || 'Gagal memperbarui profil.';
                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        errorMsg = data.errors[firstKey][0];
                    }
                    throw new Error(errorMsg);
                }
                return data;
            })
            .then(data => {
                currentProfileData = data.data;
                cancelEditMode();
                showAccountAlert('success', data.message || 'Profil berhasil diperbarui!');
            })
            .catch(err => {
                showAccountAlert('error', err.message);
            });
        }

        function showAccountAlert(type, message) {
            const alertBox = document.getElementById('accountAlertContainer');
            alertBox.classList.remove('hidden');
            if (type === 'success') {
                alertBox.innerHTML = `<div class="p-2.5 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-semibold flex items-center gap-2"><i class="fa-solid fa-circle-check text-green-500 text-sm flex-shrink-0"></i><span>${message}</span></div>`;
            } else {
                alertBox.innerHTML = `<div class="p-2.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-2"><i class="fa-solid fa-circle-exclamation text-red-500 text-sm flex-shrink-0"></i><span>${message}</span></div>`;
            }
        }

        function clearAccountAlert() {
            const alertBox = document.getElementById('accountAlertContainer');
            alertBox.classList.add('hidden');
            alertBox.innerHTML = '';
        }

        // ===== PEMBELIAN PREMIUM (khusus Mitra) =====
        function openPremiumModal() { PremiumShop.open(); }

        const PremiumShop = (function () {
            const KEUNTUNGAN = [
                'Akses Upload Gambar lebih luas',
                'Akses Pemilihan Kategori Lebih Luas',
                'Verified Badge Toko',
                'Perekomendasian teratas Toko',
                'Penghapusan Rating & Ulasan',
                'Dashboard Analisis Kunjungan mendalam',
                'Pengajuan Iklan'
            ];
            const rp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(n) || 0);
            const escP = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const tglP = s => { try { return new Date(String(s).replace(' ', 'T')).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }); } catch (e) { return s; } };
            const hdr = () => ({ 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json', 'Content-Type': 'application/json' });

            let root, info = null, selected = null;

            function ensure() {
                if (root) return;
                root = document.createElement('div');
                root.id = 'premiumModal';
                root.className = 'fixed inset-0 bg-black/60 backdrop-blur-sm z-[90] hidden items-center justify-center p-4';
                root.addEventListener('click', e => { if (e.target === root) close(); });
                document.body.appendChild(root);
            }
            function show(html) { root.innerHTML = `<div class="bg-white w-full max-w-lg max-h-[92vh] overflow-y-auto rounded-3xl shadow-2xl relative">${html}</div>`; root.classList.remove('hidden'); root.classList.add('flex'); }
            function close() { if (root) { root.classList.add('hidden'); root.classList.remove('flex'); } }
            const closeX = '<button type="button" onclick="PremiumShop.close()" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 grid place-items-center"><i class="fa-solid fa-xmark"></i></button>';

            async function open() {
                ensure(); selected = null;
                show(closeX + '<div class="p-12 text-center text-xs text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat paket...</div>');
                try {
                    const res = await fetch('/api/mitra/premium/paket', { headers: hdr() });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(json.message || 'Gagal memuat paket.');
                    info = json.data;
                    renderPilih();
                } catch (e) {
                    show(closeX + `<div class="p-10 pt-14 text-center text-xs text-red-600">${escP(e.message)}<div><button type="button" onclick="PremiumShop.close()" class="mt-4 px-5 py-2 rounded-xl bg-gray-100 text-gray-700 font-bold">Tutup</button></div></div>`);
                }
            }

            function renderPilih() {
                const aktifInfo = info.premium_aktif
                    ? `<div class="rounded-xl bg-green-50 border border-green-200 text-green-700 text-[11px] font-semibold p-3 flex gap-2"><i class="fa-solid fa-circle-check mt-0.5"></i><span>Premium Anda aktif sampai ${escP(tglP(info.masa_berakhir))}. Membeli lagi akan menambah masa aktif Anda.</span></div>` : '';

                const kartu = info.paket.map(p => {
                    const habis = !p.tersedia;
                    const sel = selected === p.key;
                    return `
                    <label class="block ${habis ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer'}">
                        <input type="radio" name="paketPremium" value="${p.key}" class="sr-only" ${habis ? 'disabled' : ''} ${sel ? 'checked' : ''} onchange="PremiumShop.pilih('${p.key}')">
                        <div class="flex items-center justify-between gap-3 rounded-2xl border-2 p-3.5 transition ${sel ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:border-amber-300'}">
                            <div>
                                <div class="text-sm font-extrabold text-gray-900">${escP(p.nama)}</div>
                                ${habis ? '<span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-200 text-gray-600">SOLD OUT / HABIS</span>'
                                        : `<span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600">Hemat ${p.hemat_persen}%</span>`}
                            </div>
                            <div class="text-right">
                                <div class="text-[11px] text-gray-400 line-through">${rp(p.harga_normal)}</div>
                                <div class="text-base font-extrabold ${habis ? 'text-gray-400' : 'text-amber-600'}">${habis ? rp(p.harga) : rp(p.harga)}</div>
                            </div>
                        </div>
                    </label>`;
                }).join('');

                show(closeX + `
                <div class="p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 grid place-items-center"><i class="fa-solid fa-crown text-white text-lg"></i></span>
                        <div><h3 class="text-lg font-extrabold text-gray-900">Berlangganan Premium</h3><p class="text-xs text-gray-500">Tingkatkan toko Anda ke level berikutnya</p></div>
                    </div>
                    ${aktifInfo}
                    <div class="rounded-2xl bg-amber-50 border border-amber-100 p-4">
                        <div class="text-xs font-extrabold text-amber-800 mb-2">Keuntungan Premium</div>
                        <ul class="space-y-1.5">${KEUNTUNGAN.map(k => `<li class="flex items-start gap-2 text-xs text-gray-700"><i class="fa-solid fa-circle-check text-amber-500 mt-0.5"></i>${escP(k)}</li>`).join('')}</ul>
                    </div>
                    <div class="space-y-2.5">${kartu}</div>
                    <div id="premiumMsg" class="hidden text-xs text-red-600"></div>
                    <div class="flex gap-2 pt-1">
                        <button type="button" onclick="PremiumShop.close()" class="flex-1 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold">Batal</button>
                        <button type="button" onclick="PremiumShop.lanjut()" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold">Lanjut ke Pembayaran</button>
                    </div>
                </div>`);
            }

            function pilih(key) { selected = key; renderPilih(); }

            function lanjut() {
                if (!selected) { const m = document.getElementById('premiumMsg'); m.textContent = 'Silakan pilih salah satu paket terlebih dahulu.'; m.classList.remove('hidden'); return; }
                const p = info.paket.find(x => x.key === selected);
                show(closeX + `
                <div class="p-5 sm:p-6 space-y-4">
                    <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2"><i class="fa-solid fa-receipt text-amber-500"></i> Ringkasan Pembayaran</h3>
                    <div class="rounded-2xl border border-gray-200 divide-y divide-gray-100 text-sm">
                        <div class="flex justify-between p-3.5"><span class="text-gray-500">Paket</span><span class="font-bold text-gray-900">${escP(p.nama)}</span></div>
                        <div class="flex justify-between p-3.5"><span class="text-gray-500">Harga normal</span><span class="text-gray-400 line-through">${rp(p.harga_normal)}</span></div>
                        <div class="flex justify-between p-3.5"><span class="text-gray-500">Total bayar</span><span class="font-extrabold text-amber-600 text-base">${rp(p.harga)}</span></div>
                    </div>
                    <div id="premiumMsg" class="hidden text-xs text-red-600"></div>
                    <div class="flex gap-2">
                        <button type="button" onclick="PremiumShop.kembali()" class="flex-1 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold">Kembali</button>
                        <button type="button" id="btnBayarPremium" onclick="PremiumShop.bayar()" class="flex-1 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white text-xs font-bold">Bayar Sekarang</button>
                    </div>
                </div>`);
            }
            function kembali() { renderPilih(); }

            async function bayar() {
                const btn = document.getElementById('btnBayarPremium');
                if (btn) { btn.disabled = true; btn.textContent = 'Memproses...'; }
                try {
                    const res = await fetch('/api/mitra/premium/beli', { method: 'POST', headers: hdr(), body: JSON.stringify({ paket: selected }) });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error((json.errors ? Object.values(json.errors).flat().join(' ') : '') || json.message || 'Pembayaran gagal.');

                    // Perbarui status akun di browser (dipakai untuk membuka fitur premium)
                    localStorage.setItem('jenis_akun_id', String(json.data.jenis_akun_id));
                    if (typeof loadProfileData === 'function') loadProfileData();

                    show(closeX + `
                    <div class="p-8 pt-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-green-100 grid place-items-center mx-auto mb-4"><i class="fa-solid fa-check text-2xl text-green-600"></i></div>
                        <h3 class="text-lg font-extrabold text-gray-900">${escP(json.message)}</h3>
                        <p class="text-xs text-gray-500 mt-2">${escP(json.data.paket)} aktif sampai <b>${escP(tglP(json.data.masa_berakhir))}</b>.</p>
                        <button type="button" onclick="PremiumShop.close()" class="mt-6 px-8 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Selesai</button>
                    </div>`);
                } catch (e) {
                    const m = document.getElementById('premiumMsg');
                    if (m) { m.textContent = e.message; m.classList.remove('hidden'); }
                    if (btn) { btn.disabled = false; btn.textContent = 'Bayar Sekarang'; }
                }
            }

            return { open, close, pilih, lanjut, kembali, bayar };
        })();

        function handleLogout() {
            clearAccountAlert();
            document.getElementById('logoutConfirmationBox').classList.remove('hidden');
        }

        function cancelLogout() {
            document.getElementById('logoutConfirmationBox').classList.add('hidden');
        }

        function executeLogout() {
            const token = localStorage.getItem('access_token');

            fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                localStorage.removeItem('access_token');
                localStorage.removeItem('jenis_akun_id');
                window.location.href = "/";
            })
            .catch(err => {
                localStorage.removeItem('access_token');
                localStorage.removeItem('jenis_akun_id');
                window.location.href = "/";
            });
        }
    </script>
</body>
</html>