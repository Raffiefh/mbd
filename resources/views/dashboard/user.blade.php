<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - NongkrongID</title>
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

    <!-- SCRIPT PENJAGA: Cek Token & Isolasi Role User -->
    <script>
        const token = localStorage.getItem('access_token');
        if (!token) {
            alert("Silakan login terlebih dahulu!");
            window.location.href = "/";
        }

        const jenisAkunId = localStorage.getItem('jenis_akun_id');
        // Sesuaikan jika ID 2 atau role user spesifik Anda berbeda
        if (jenisAkunId === '1' || jenisAkunId === '4') { // Contoh jika mitra
            // Jika ingin membatasi khusus user, sesuaikan validasi ini dengan logika backend Anda
        }
    </script>

    <!-- 1. HEADER & NAVBAR UTAMA USER -->
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
                    <input type="text" id="searchInput" placeholder="Cari tempat menarik, kafe, atau restoran..." class="w-full pl-9 pr-4 py-2 rounded-full text-xs sm:text-sm text-gray-700 focus:outline-none shadow-inner">
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

        <!-- Baris Bawah (Sub-Navigasi User): Dashboard, Favorit, Akun -->
        <div class="bg-blue-700/80 border-t border-blue-500/40">
            <div class="max-w-4xl mx-auto px-4 flex items-center justify-center space-x-6 sm:space-x-10 py-2.5 text-xs sm:text-sm font-medium overflow-x-auto">
                <a href="#dashboard" onclick="switchSection('dashboard', event)" class="nav-link flex items-center gap-1.5 hover:text-blue-200 transition font-bold border-b-2 border-white pb-0.5 whitespace-nowrap" data-target="section-dashboard">
                    <i class="fa-solid fa-house"></i> <span>Dashboard</span>
                </a>
                <a href="#favorit" onclick="switchSection('favorit', event)" class="nav-link flex items-center gap-1.5 hover:text-blue-200 transition opacity-80 hover:opacity-100 whitespace-nowrap" data-target="section-favorit">
                    <i class="fa-solid fa-heart"></i> <span>Daftar Favorit</span>
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
                    Informasi Menarik <span class="text-sm">✨</span>
                </h2>
                <div id="iklanBanner" class="w-full bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 p-3">
                    <div class="w-full h-32 sm:h-48 rounded-xl bg-gray-50 border border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 gap-1">
                        <i class="fa-regular fa-image text-2xl text-gray-300"></i>
                        <p class="text-xs italic">(Area slide banner promo - dikosongkan)</p>
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
                    <p class="text-xs sm:text-sm text-gray-400 italic">(Area katalog daftar tempat / kafe - dikosongkan sementara)</p>
                </div>
            </section>
        </div>

        <!-- ================= SECTION 2: DAFTAR FAVORIT ================= -->
        <div id="section-favorit" class="page-section space-y-4">
            <div class="max-w-4xl">
                <h3 class="text-lg sm:text-xl font-extrabold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-heart text-red-500"></i> Berikut merupakan daftar favorit anda</h3>
                <p class="text-xs text-gray-500 mt-0.5">Klik salah satu tempat untuk melihat detailnya.</p>
            </div>
            <div id="favoritContainer" class="space-y-3 max-w-4xl"></div>
        </div>

        <!-- ================= SECTION 3: AKUN (KHUSUS USER) ================= -->
        <div id="section-akun" class="page-section space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 max-w-5xl mx-auto">
                
                <!-- Header Section & Tombol Aksi Utama (Tanpa Tombol Premium) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-user-gear text-blue-600"></i> Pengaturan Akun
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Kelola informasi profil akun Anda.</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
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

                <!-- Form Profil User Layout Kesamping (Grid 2 Kolom) -->
                <form id="profileForm" onsubmit="handleProfileSubmit(event)" class="mt-4">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                        
                        <!-- KOLOM KIRI: Foto Profil & Status Akun (Tanpa Masa Berakhir) -->
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

                            <!-- Info Status Akun Compact (Hanya menampilkan jenis akun user, tanpa masa berakhir) -->
                            <div class="w-full pt-2 border-t border-gray-200/60 text-left space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 font-medium">Status Akun:</span>
                                    <span id="textJenisAkun" class="font-bold text-blue-700">User</span>
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
    <script>window.KATALOG_OPTIONS = { container: 'catalogContainer', showRating: true };</script>
    <script>document.addEventListener('DOMContentLoaded', () => { if (window.Katalog && Katalog.bindSearch) Katalog.bindSearch({ inputs: ['searchInput'], withCategory: true, withGps: true }); else console.error('katalog.js tidak termuat atau masih versi lama (Katalog.bindSearch tidak ditemukan)'); });</script>
    <script src="{{ asset('js/katalog.js') }}?v={{ @filemtime(public_path('js/katalog.js')) }}"></script>
    <script src="{{ asset('js/toko-detail.js') }}?v={{ @filemtime(public_path('js/toko-detail.js')) }}"></script>
    <script src="{{ asset('js/iklan-banner.js') }}?v={{ @filemtime(public_path('js/iklan-banner.js')) }}"></script>
    <script>
        // ===== DAFTAR FAVORIT (khusus User) =====
        async function loadFavorit() {
            const box = document.getElementById('favoritContainer');
            if (!box) return;
            box.innerHTML = '<div class="bg-white rounded-2xl p-8 text-center border border-gray-100 text-xs text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat favorit...</div>';
            try {
                const res = await fetch('/api/favorit', { headers: { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json' } });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Gagal memuat');
                if (!json.data.length) {
                    box.innerHTML = '<div class="bg-white rounded-2xl p-8 text-center border border-gray-100 shadow-sm text-xs text-gray-400 italic">Belum ada tempat favorit. Klik ikon love pada detail tempat untuk menyimpannya di sini.</div>';
                    return;
                }
                box.innerHTML = json.data.map(t => Katalog.cardHtml(t, true)).join('');
            } catch (e) {
                box.innerHTML = '<div class="bg-white rounded-2xl p-8 text-center border border-red-100 text-xs text-red-500">Gagal memuat favorit. <button class="underline font-bold" onclick="loadFavorit()">Coba lagi</button></div>';
            }
        }
        // Love/unlove dari popup -> daftar favorit ikut diperbarui
        document.addEventListener('tokodetail:favorit-changed', () => {
            const sec = document.getElementById('section-favorit');
            if (sec && sec.classList.contains('active')) loadFavorit();
        });
    </script>
    <script>
        function switchSection(targetId, event) {
            if (event) event.preventDefault();

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

            if (targetId === 'favorit' && window.loadFavorit) loadFavorit();
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
                subtitle: "Ayo cari tempat terbaikmu",
                buttons: [
                    { id: 'terdekat', label: 'Terdekat 🔵', style: 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm' },
                    { id: 'general', label: 'General 🔵', style: 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 shadow-sm' }
                ]
            },
            terdekat: {
                title: "Tempat Terdekat 🗾",
                subtitle: "Ayo cari tempat yang sesuai",
                buttons: [
                    { id: 'rekomendasi', label: 'Rekomendasi 🔵', style: 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm' },
                    { id: 'general', label: 'General 🔵', style: 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 shadow-sm' }
                ]
            },
            general: {
                title: "Menjelajah Tempat 👋",
                subtitle: "Ayo jelajahi berbagai macam tempat",
                buttons: [
                    { id: 'rekomendasi', label: 'Rekomendasi 🔵', style: 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm' },
                    { id: 'terdekat', label: 'Terdekat 🔵', style: 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 shadow-sm' }
                ]
            }
        };

        function setFilter(type) {
            const config = filterConfigs[type];
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

        document.addEventListener('DOMContentLoaded', () => {
            setFilter('rekomendasi');
            loadProfileData();
        });

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
            document.getElementById('inputNama').value = data.nama_lengkap || '';
            document.getElementById('inputEmail').value = data.email || '';
            document.getElementById('inputTelepon').value = data.no_telefon || '';
            document.getElementById('inputPassword').value = '';
            document.getElementById('inputPassword').placeholder = '••••••••';
            
            const imgPath = data.path_gambar ? `/images/profile/${data.path_gambar}` : '/images/profile/default.png';
            document.getElementById('profilePreview').src = imgPath;

            let jenisAkunNama = 'User';
            if (data.jenis_akun && data.jenis_akun.jenis_akun) {
                jenisAkunNama = data.jenis_akun.jenis_akun;
            }
            document.getElementById('textJenisAkun').innerText = jenisAkunNama;
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('inputPassword');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'fa-solid fa-eye text-xs';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'fa-solid fa-eye-slash text-xs';
            }
        }

        function triggerImageUpload() {
            const isEditing = !document.getElementById('inputNama').disabled;
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
            const nama = document.getElementById('inputNama').value.trim();
            const telepon = document.getElementById('inputTelepon').value.trim();

            if (!document.getElementById('inputNama').disabled) {
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

            document.getElementById('inputNama').disabled = false;
            document.getElementById('inputTelepon').disabled = false;
            document.getElementById('inputPassword').disabled = false;
            
            document.getElementById('inputNama').classList.remove('bg-gray-50');
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

        // Fungsi Konfirmasi Logout Inline
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
                console.error("Gagal melakukan logout:", err);
                localStorage.removeItem('access_token');
                localStorage.removeItem('jenis_akun_id');
                window.location.href = "/";
            });
        }
    </script>
</body>
</html>