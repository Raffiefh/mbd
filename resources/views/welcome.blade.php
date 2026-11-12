<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NongkrongID - Temukan Tempat Terbaik Versimu!</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                display: ['"Bricolage Grotesque"', '"Plus Jakarta Sans"', 'ui-sans-serif', 'sans-serif']
            } } }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { background-color: #F5F7FB; }
 
        /* Navbar: gradasi yang sama dengan dashboard */
        .nav-brand { background-image: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); box-shadow: 0 6px 20px -8px rgba(30, 58, 138, .55); }
 
        /* Hero: pola titik halus */
        .hero-dots { background-image: radial-gradient(#c7d2e8 1px, transparent 1px); background-size: 22px 22px; -webkit-mask-image: linear-gradient(to bottom, #000 0%, transparent 85%); mask-image: linear-gradient(to bottom, #000 0%, transparent 85%); }
 
        /* Galeri berjalan (set foto digandakan agar putarannya mulus) */
        @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        .animate-marquee { display: flex; width: max-content; animation: marquee 40s linear infinite; }
        .animate-marquee:hover { animation-play-state: paused; }
 
        /* Modal: muncul halus saat dibuka */
        @keyframes modal-in { from { opacity: 0; transform: translateY(12px) scale(.98); } to { opacity: 1; transform: none; } }
        [id$="Modal"] > div { border-radius: 1.5rem; animation: modal-in .22s ease-out both; }
        [id$="Modal"] h3 { font-family: "Bricolage Grotesque", "Plus Jakarta Sans", sans-serif; letter-spacing: -.01em; }
        [id$="Modal"] input:not([type="checkbox"]), [id$="Modal"] select { background-color: #F8FAFC; border-color: #E2E8F0; }
        [id$="Modal"] input:not([type="checkbox"]):focus, [id$="Modal"] select:focus { background-color: #fff; border-color: #2563eb; }
 
        :focus-visible { outline: 2px solid #F59E0B; outline-offset: 2px; }
 
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .animate-marquee { animation: none; }
            [id$="Modal"] > div { animation: none; }
        }
    </style>
</head>
<body class="text-slate-800 font-sans antialiased selection:bg-blue-600 selection:text-white">
 
    <!-- 1. NAVBAR -->
    <nav class="nav-brand text-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2.5 flex-shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo NongkrongID" class="h-9 w-9 object-contain flex-shrink-0">
                <span class="font-display font-extrabold text-xl tracking-tight">NongkrongID</span>
            </div>
            <!-- Search bar di Navbar (Desktop) -->
            <div class="hidden md:flex flex-1 max-w-md mx-8">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" id="searchInputDesktop" placeholder="Mencari tempat menarik, cafe, atau restoran..." class="w-full pl-11 pr-4 py-2.5 rounded-full text-sm text-slate-700 placeholder:text-slate-400 bg-white focus:outline-none focus:ring-2 focus:ring-amber-300 shadow-inner">
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button onclick="openModal('loginModal')" class="text-white/90 hover:text-white hover:bg-white/10 border border-white/25 px-4 sm:px-5 py-2 rounded-xl text-sm font-bold transition">Masuk</button>
                <button onclick="openModal('registerModal')" class="bg-white text-blue-700 hover:bg-blue-50 px-4 sm:px-5 py-2 rounded-xl text-sm font-bold shadow-sm transition">Mendaftar</button>
            </div>
        </div>
    </nav>
 
    <!-- Search Bar khusus Mobile -->
    <div class="md:hidden nav-brand px-4 pb-4 pt-1">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </span>
            <input type="text" id="searchInputMobile" placeholder="Mencari tempat menarik ..." class="w-full pl-11 pr-4 py-2.5 rounded-full text-sm text-slate-700 placeholder:text-slate-400 bg-white focus:outline-none focus:ring-2 focus:ring-amber-300">
        </div>
    </div>
 
    <!-- MAIN CONTAINER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10 space-y-24">
 
        <!-- ================= BAGIAN 1: HERO SECTION ================= -->
        <section class="relative">
            <div class="hero-dots absolute inset-x-0 -top-0 h-[28rem] -z-10" aria-hidden="true"></div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 lg:gap-16 items-center pt-12 sm:pt-16 pb-4">
                <div class="space-y-7">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white text-blue-700 ring-1 ring-blue-100 shadow-sm px-3.5 py-1.5 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span> Mulai Gratis Sekarang
                    </span>
                    <h1 class="font-display text-5xl sm:text-7xl font-extrabold text-[#0B1B3A] tracking-tight leading-[1.02]">
                        Temukan spot<br class="hidden sm:block"> terbaikmu.
                    </h1>
                    <p class="text-slate-600 text-base sm:text-lg leading-relaxed max-w-lg">
                        Jelajahi tempat nongkrong, kafe, dan restoran favorit dengan rekomendasi terkurasi dan promo menarik setiap hari di genggamanmu.
                    </p>
                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <button onclick="openModal('registerModal')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-7 py-3.5 rounded-xl shadow-lg shadow-blue-600/25 transition text-sm sm:text-base">
                            Try for free
                        </button>
                        <a href="#features" class="bg-white hover:bg-slate-50 text-slate-800 font-bold px-7 py-3.5 rounded-xl border border-slate-200 shadow-sm transition text-sm sm:text-base">
                            See how it works
                        </a>
                    </div>
                    <ul class="flex flex-wrap gap-x-6 gap-y-2 pt-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-blue-600"></i> Tempat terdekat</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-ticket text-blue-600"></i> Informasi Promo &amp; voucher</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-clock text-blue-600"></i> Informasi Penting lainnya</li>
                    </ul>
                </div>
 
                <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                    <div class="absolute inset-0 translate-x-4 translate-y-4 sm:translate-x-5 sm:translate-y-5 rounded-[2rem] bg-blue-200/70" aria-hidden="true"></div>
                    <div class="relative h-[24rem] sm:h-[30rem] rounded-[2rem] overflow-hidden border-4 border-white shadow-2xl shadow-blue-900/15 bg-white">
                        <img src="{{ asset('images/Poster.jpg') }}" class="w-full h-full object-cover transform hover:scale-105 transition duration-700" alt="Poster Utama NongkrongID">
                    </div>
                    <div class="absolute -left-2 sm:-left-6 bottom-8 bg-white rounded-2xl shadow-xl px-4 py-3 flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-green-50 text-green-600 grid place-items-center"><i class="fa-solid fa-door-open"></i></span>
                        <span class="text-sm font-bold text-slate-800 leading-tight">Buka sekarang<br><span class="text-xs font-medium text-slate-500">di dekatmu</span></span>
                    </div>
                    <div class="absolute -right-1 sm:-right-4 top-8 bg-amber-400 text-[#3B2A00] rounded-2xl shadow-xl px-4 py-2.5 text-sm font-extrabold flex items-center gap-2">
                        <i class="fa-solid fa-tags"></i> Promo hari ini
                    </div>
                </div>
            </div>
        </section>
 
        <!-- ================= BAGIAN 3: TEKS SAMBUTAN & SLIDER GALERI FOTO ================= -->
        <section class="bg-[#0B1B3A] text-white rounded-[2rem] p-8 sm:p-14 space-y-12 overflow-hidden">
            <div class="max-w-3xl mx-auto space-y-6 text-center">
                <h2 class="font-display text-3xl sm:text-5xl font-extrabold leading-[1.1] tracking-tight">
                    Selamat Datang di NongkrongID <br class="hidden sm:block"/>
                    Temukan Tempat Terbaik Versimu!
                </h2>
                <p class="text-blue-100/80 text-base sm:text-lg leading-relaxed">
                    NongkrongID hadir untuk memudahkan kamu menemukan spot terbaik untuk berkumpul, makan, beristirahat, hingga bersantai sesuai preferensi pribadi.
                </p>
                <div class="pt-2">
                    <button type="button" onclick="openModal('registerModal')" class="inline-flex items-center gap-3 max-w-xl bg-amber-400 hover:bg-amber-300 text-[#3B2A00] font-extrabold px-7 py-4 rounded-2xl text-sm sm:text-base text-left shadow-lg shadow-black/20 transition">
                        <span>Ayo bergabung pada website kami secara gratis dan nikmati semua penawaran menariknya sekarang!</span>
                        <i class="fa-solid fa-arrow-right flex-shrink-0"></i>
                    </button>
                </div>
            </div>
 
            <!-- BAGIAN SLIDER -->
            <div class="relative w-full overflow-hidden">
                <div class="absolute left-0 inset-y-0 w-16 sm:w-24 bg-gradient-to-r from-[#0B1B3A] to-transparent z-10 pointer-events-none"></div>
                <div class="absolute right-0 inset-y-0 w-16 sm:w-24 bg-gradient-to-l from-[#0B1B3A] to-transparent z-10 pointer-events-none"></div>
 
                <div class="animate-marquee">
                    <div class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f1.png') }}" class="w-full h-full object-cover" alt="Galeri 1"></div>
                    <div class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f2.png') }}" class="w-full h-full object-cover" alt="Galeri 2"></div>
                    <div class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f3.png') }}" class="w-full h-full object-cover" alt="Galeri 3"></div>
                    <div class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f4.png') }}" class="w-full h-full object-cover" alt="Galeri 4"></div>
                    <div class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f5.png') }}" class="w-full h-full object-cover" alt="Galeri 5"></div>
                    <div class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f6.png') }}" class="w-full h-full object-cover" alt="Galeri 6"></div>
                    <div class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f7.png') }}" class="w-full h-full object-cover" alt="Galeri 7"></div>
                    <!-- Set kedua (duplikat untuk loop mulus) -->
                    <div aria-hidden="true" class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f1.png') }}" class="w-full h-full object-cover" alt=""></div>
                    <div aria-hidden="true" class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f2.png') }}" class="w-full h-full object-cover" alt=""></div>
                    <div aria-hidden="true" class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f3.png') }}" class="w-full h-full object-cover" alt=""></div>
                    <div aria-hidden="true" class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f4.png') }}" class="w-full h-full object-cover" alt=""></div>
                    <div aria-hidden="true" class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f5.png') }}" class="w-full h-full object-cover" alt=""></div>
                    <div aria-hidden="true" class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f6.png') }}" class="w-full h-full object-cover" alt=""></div>
                    <div aria-hidden="true" class="w-64 h-40 sm:w-80 sm:h-52 mr-6 rounded-2xl overflow-hidden flex-shrink-0 bg-white/10 ring-1 ring-white/15"><img src="{{ asset('images-place/f7.png') }}" class="w-full h-full object-cover" alt=""></div>
                </div>
            </div>
        </section>
 
        <!-- ================= BAGIAN 4: FITUR ================= -->
        <section id="features" class="space-y-10 scroll-mt-24">
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <h2 class="text-sm font-bold text-blue-600">Fitur Komprehensif</h2>
                <h3 class="font-display text-3xl sm:text-4xl font-extrabold text-[#0B1B3A] tracking-tight">Solusi Lengkap Pengguna &amp; Pemilik Usaha</h3>
                <p class="text-slate-500 text-sm sm:text-base">Dirancang khusus untuk memberikan pengalaman terbaik dalam direktori dan melakukan promosi.</p>
            </div>
 
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                <!-- User -->
                <div class="bg-white p-8 sm:p-10 rounded-[1.75rem] border border-slate-200/80 shadow-[0_1px_2px_rgba(15,23,42,.04),0_16px_40px_-20px_rgba(37,99,235,.25)] space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-xl shadow-lg shadow-blue-600/25">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h4 class="font-display text-xl sm:text-2xl font-extrabold text-[#0B1B3A] leading-tight">Fitur untuk Pengguna (User)</h4>
                    </div>
                    <ul class="space-y-4 text-sm text-slate-600 leading-relaxed">
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-blue-600 mt-1"></i><div><strong class="text-slate-900">Informasi Tempat Rekomendasi:</strong> Daftar pilihan cafe dan restoran menarik.</div></li>
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-blue-600 mt-1"></i><div><strong class="text-slate-900">Searching Kategori:</strong> Pencarian cepat berdasarkan kategori, suasana, dan fasilitas yang diinginkan.</div></li>
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-blue-600 mt-1"></i><div><strong class="text-slate-900">Informasi Promo &amp; Voucher:</strong> Akses informasi promo secara eksklusif dengan penawaran yang hemat setiap hari.</div></li>
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-blue-600 mt-1"></i><div><strong class="text-slate-900">Informasi Tempat Terdekat:</strong> Deteksi lokasi otomatis untuk menemukan spot nongkrong terdekat dari posisimu.</div></li>
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-blue-600 mt-1"></i><div><strong class="text-slate-900">Informasi Mendetail:</strong> Jam operasional, fasilitas, ulasan, hingga status buka/tutup secara real-time.</div></li>
                    </ul>
                </div>
 
                <!-- Penjual -->
                <div class="bg-white p-8 sm:p-10 rounded-[1.75rem] border border-slate-200/80 shadow-[0_1px_2px_rgba(15,23,42,.04),0_16px_40px_-20px_rgba(79,70,229,.25)] space-y-6 self-start">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-xl shadow-lg shadow-indigo-600/25">
                            <i class="fa-solid fa-store"></i>
                        </div>
                        <h4 class="font-display text-xl sm:text-2xl font-extrabold text-[#0B1B3A] leading-tight">Fitur untuk Penjual &amp; Mitra Usaha</h4>
                    </div>
                    <ul class="space-y-4 text-sm text-slate-600 leading-relaxed">
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-indigo-600 mt-1"></i><div><strong class="text-slate-900">Penjualan Media Promosi:</strong> Wadah efektif untuk memasarkan produk, menu spesial, dan event cafe secara luas.</div></li>
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-indigo-600 mt-1"></i><div><strong class="text-slate-900">Analitik Bisnis Mendalam:</strong> Laporan data kunjungan, interaksi, dan performa promosi secara komprehensif.</div></li>
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-indigo-600 mt-1"></i><div><strong class="text-slate-900">Pengajuan &amp; Manajemen Iklan:</strong> Fitur mudah untuk pengajuan sorotan banner iklan agar cafe dapat terlihat lebih menonjol di halaman utama.</div></li>
                    </ul>
                </div>
            </div>
        </section>
 
        <!-- ================= BAGIAN 5: KATALOG TEMPAT ================= -->
        <section class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 pb-5">
                <div>
                    <h3 id="filterTitle" class="font-display text-2xl sm:text-3xl font-extrabold text-[#0B1B3A] tracking-tight flex items-center gap-2"></h3>
                    <p id="filterSubtitle" class="text-sm text-slate-500 mt-1"></p>
                </div>
                <div id="filterButtons" class="flex gap-2"></div>
            </div>

            <div id="catalogContainer" class="space-y-3 max-w-4xl"></div>
        </section>
    </main>
 
    <!-- FOOTER -->
    <footer class="bg-[#0B1B3A] text-blue-100/70 mt-10 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm">
            <div class="flex items-center gap-2.5 text-white">
                <span class="w-8 h-8 rounded-lg bg-white text-blue-700 grid place-items-center"><i class="fa-solid fa-mug-hot text-sm"></i></span>
                <span class="font-display font-extrabold text-lg tracking-tight">NongkrongID</span>
            </div>
            <p class="text-center sm:text-right">&copy; 2026 NongkrongID. All rights reserved. Platform Direktori &amp; Promo Cafe Terbaik.</p>
        </div>
    </footer>
 
    <!-- ================= MODAL 1: FORM REGISTER ================= -->
    <div id="registerModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-xl w-full p-8 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button onclick="closeModal('registerModal')" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            
            <h3 class="text-2xl font-extrabold text-gray-900">Mendaftar Akun Baru</h3>
            <p class="text-xs text-gray-500 mt-1 mb-6">Lengkapi data di bawah ini untuk membuat akun NongkrongID</p>
            
            <div id="registerErrorMsg" class="hidden mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs space-y-1"></div>
 
            <form onsubmit="handleRegisterSubmit(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-gray-700">Nama Lengkap</label>
                        <input type="text" id="reg_nama" required minlength="3" maxlength="60" placeholder="Masukkan nama lengkap anda" class="w-full mt-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-700">Alamat Email</label>
                        <input type="email" id="reg_email" required placeholder="nama@email.com" class="w-full mt-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-700">Nomor Telefon</label>
                        <input type="text" id="reg_telepon" required minlength="10" maxlength="15" placeholder="081234567890" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="w-full mt-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-700">Jenis Akun</label>
                        <select id="reg_jenis_akun" required class="w-full mt-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none bg-white">
                            <option value="2">Pengguna biasa</option>
                            <option value="3">Mitra pemilik usaha</option>
                            <option value="7">Mitra Sponsor</option>
                        </select>
                    </div>
                </div>
 
                <div>
                    <label class="text-xs font-bold text-gray-700">Password</label>
                    <div class="relative mt-1">
                        <input type="password" id="reg_password" required minlength="8" maxlength="20" placeholder="Minimal 8 Karakter" class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <button type="button" onclick="togglePassword('reg_password', 'eyeRegIcon')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i id="eyeRegIcon" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
 
                <button type="submit" id="btnRegSubmit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-md transition text-sm">
                    Daftar Sekarang
                </button>
            </form>
            <p class="text-xs text-center text-gray-500 mt-4">Sudah memiliki akun? <a href="javascript:void(0)" onclick="switchModal('registerModal', 'loginModal')" class="text-blue-600 font-bold hover:underline">Masuk disini</a></p>
        </div>
    </div>
 
    <!-- ================= MODAL 2: VERIFIKASI OTP REGISTER ================= -->
    <div id="otpRegisterModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-8 shadow-2xl relative text-center">
            <button onclick="closeModal('otpRegisterModal')" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                <i class="fa-solid fa-envelope-circle-check"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-900">Verifikasi OTP Registrasi</h3>
            <p class="text-xs text-gray-500 mt-1 mb-4">Masukkan kode OTP yang telah dikirimkan ke email Anda</p>
            
            <div id="otpRegErrorMsg" class="hidden mb-4 p-3 rounded-xl text-xs space-y-1 text-left"></div>
 
            <form onsubmit="handleVerifyRegisterSubmit(event)" class="space-y-4">
                <input type="number" id="reg_otp_code" required placeholder="123456" class="w-full text-center tracking-[0.5em] text-2xl font-bold py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                
                <button type="submit" id="btnOtpRegSubmit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-md transition text-sm disabled:bg-gray-400">
                    Verifikasi & Aktifkan
                </button>
            </form>
            
            <div class="mt-4 text-xs text-gray-500">
                Tidak menerima kode? <button type="button" id="btnResendOtp" onclick="handleResendOtp()" class="text-blue-600 font-bold hover:underline">Kirim Ulang OTP</button>
            </div>
        </div>
    </div>
 
    <!-- ================= MODAL 3: FORM LOGIN TAHAP 1 ================= -->
    <div id="loginModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl relative">
            <button onclick="closeModal('loginModal')" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            
            <h3 class="text-2xl font-extrabold text-gray-900">Masuk ke NongkrongID</h3>
            <p class="text-xs text-gray-500 mt-1 mb-6">Masukkan email dan password terdaftar kamu</p>
            
            <div id="loginErrorMsg" class="hidden mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs space-y-1"></div>
 
            <form onsubmit="handleLoginSubmit(event)" class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-gray-700">Alamat Email</label>
                    <input type="email" id="login_email" required placeholder="nama@email.com" class="w-full mt-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700">Password</label>
                    <div class="relative mt-1">
                        <input type="password" id="login_password" required placeholder="Password Anda" class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <button type="button" onclick="togglePassword('login_password', 'eyeLoginIcon')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i id="eyeLoginIcon" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" id="btnLoginSubmit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-md transition text-sm">
                    Masuk dan lanjutkan
                </button>
            </form>
            <p class="text-xs text-center text-gray-500 mt-4">Belum memiliki akun? <a href="javascript:void(0)" onclick="switchModal('loginModal', 'registerModal')" class="text-blue-600 font-bold hover:underline">Daftar sekarang</a></p>
        </div>
    </div>
 
    <!-- ================= MODAL 4: VERIFIKASI OTP LOGIN TAHAP 2 ================= -->
    <div id="otpLoginModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-8 shadow-2xl relative text-center">
            <button onclick="closeModal('otpLoginModal')" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-900">Verifikasi OTP Login</h3>
            <p class="text-xs text-gray-500 mt-1 mb-4">Masukkan kode OTP yang baru saja dikirim ke email kamu</p>
            
            <div id="otpLoginErrorMsg" class="hidden mb-4 p-3 rounded-xl text-xs space-y-1 text-left"></div>
 
            <form onsubmit="handleVerifyLoginSubmit(event)" class="space-y-4">
                <input type="number" id="login_otp_code" required placeholder="123456" class="w-full text-center tracking-[0.5em] text-2xl font-bold py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                
                <button type="submit" id="btnOtpLoginSubmit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-md transition text-sm disabled:bg-gray-400">
                    Masuk ke Dashboard
                </button>
            </form>
 
            <div class="mt-4 text-xs text-gray-500">
                Tidak menerima kode? <button type="button" id="btnResendLoginOtp" onclick="handleResendLoginOtp()" class="text-blue-600 font-bold hover:underline">Kirim Ulang OTP</button>
            </div>
        </div>
    </div>
 
   <script>
        // Cek Sesi Auto-Login saat halaman dimuat (Sesi 20 Hari & Caching Regular User)
        document.addEventListener("DOMContentLoaded", function() {
            const token = localStorage.getItem('access_token');
            const jenisAkunId = localStorage.getItem('jenis_akun_id');
            if (token && String(jenisAkunId) === '2') {
                window.location.href = '/dashboard/user'; 
            }
        });
 
        function openModal(id) { 
            if(id === 'registerModal') document.getElementById('registerErrorMsg').classList.add('hidden');
            if(id === 'loginModal') document.getElementById('loginErrorMsg').classList.add('hidden');
            if(id === 'otpRegisterModal') document.getElementById('otpRegErrorMsg').classList.add('hidden');
            if(id === 'otpLoginModal') document.getElementById('otpLoginErrorMsg').classList.add('hidden');
            document.getElementById(id).classList.remove('hidden'); 
        }
 
        function closeModal(id) { 
            document.getElementById(id).classList.add('hidden'); 
            
            // Bersihkan form otomatis saat modal ditutup
            if (id === 'registerModal') {
                document.querySelector('#registerModal form').reset();
                document.getElementById('registerErrorMsg').classList.add('hidden');
            }
            if (id === 'loginModal') {
                document.querySelector('#loginModal form').reset();
                document.getElementById('loginErrorMsg').classList.add('hidden');
            }
        }
 
        function switchModal(fromId, toId) { closeModal(fromId); openModal(toId); }
 
        // Fungsi Show/Hide Password
        function togglePassword(inputId, iconId) {
            const inputField = document.getElementById(inputId);
            const iconField = document.getElementById(iconId);
            if (inputField.type === 'password') {
                inputField.type = 'text';
                iconField.classList.remove('fa-eye');
                iconField.classList.add('fa-eye-slash');
            } else {
                inputField.type = 'password';
                iconField.classList.remove('fa-eye-slash');
                iconField.classList.add('fa-eye');
            }
        }
 
        let activeEmail = '';
        let isResending = false;
        let isResendingLogin = false; 
 
        async function apiPost(url, payload) {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
 
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });
            
            const data = await res.json();
            return { status: res.status, ok: res.ok, data };
        }
 
        async function handleResendLoginOtp() {
            if (isResendingLogin) return;
            if (!activeEmail) {
                showPopupMessage('otpLoginErrorMsg', 'Email tidak ditemukan, silakan ulangi proses login.', false);
                return;
            }
 
            isResendingLogin = true;
            const resBtn = document.getElementById('btnResendLoginOtp');
            resBtn.textContent = 'Mengirim ulang...';
            resBtn.disabled = true;
            document.getElementById('otpLoginErrorMsg').classList.add('hidden');
 
            const res = await apiPost('/api/login/resend-otp', { email: activeEmail });
 
            isResendingLogin = false;
            resBtn.textContent = 'Kirim Ulang OTP';
            resBtn.disabled = false;
 
            if (res.ok) {
                showPopupMessage('otpLoginErrorMsg', res.data.message, true);
            } else {
                handleModalError(res, 'otpLoginErrorMsg');
            }
        }
        
        function showPopupMessage(containerId, messages, isSuccess = false) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            
            if (isSuccess) {
                container.className = "mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs space-y-1 text-left";
            } else {
                container.className = "mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs space-y-1 text-left";
            }
 
            const msgArray = Array.isArray(messages) ? messages : [messages];
            msgArray.forEach(msg => {
                const p = document.createElement('p');
                p.textContent = `• ${msg}`;
                container.appendChild(p);
            });
 
            container.classList.remove('hidden');
        }
 
        // Helper untuk menangani error API secara dinamis ke container pop-up
        function handleModalError(res, containerId) {
            let messages = [];
            if (res.status === 422 && res.data.errors) {
                messages = Object.values(res.data.errors).flat();
            } else {
                messages.push(res.data.message || 'Terjadi kesalahan pada sistem!');
            }
            showPopupMessage(containerId, messages, false);
        }
 
        // 1. Register Handler (Dengan validasi nomor telepon & reset form saat sukses)
        async function handleRegisterSubmit(e) {
            e.preventDefault();
            
            const noTelepon = document.getElementById('reg_telepon').value.trim();
            const errorContainer = document.getElementById('registerErrorMsg');
            errorContainer.classList.add('hidden');
            errorContainer.innerHTML = '';
 
            if (noTelepon.length < 10 || noTelepon.length > 13) {
                errorContainer.innerHTML = '• Nomor telepon harus terdiri dari 10 hingga 13 digit.';
                errorContainer.classList.remove('hidden');
                return; 
            }
 
            const btn = document.getElementById('btnRegSubmit');
            btn.disabled = true;
            btn.textContent = 'Memproses...';
 
            activeEmail = document.getElementById('reg_email').value;
            const payload = {
                nama_lengkap: document.getElementById('reg_nama').value,
                email: activeEmail,
                no_telefon: noTelepon,
                password: document.getElementById('reg_password').value,
                jenis_akun_id_jenis_akun: parseInt(document.getElementById('reg_jenis_akun').value)
            };
 
            const res = await apiPost('/api/register', payload);
            
            btn.disabled = false;
            btn.textContent = 'Daftar Sekarang';
 
            if (res.ok) {
                document.querySelector('#registerModal form').reset(); // Bersihkan form
                switchModal('registerModal', 'otpRegisterModal');
                showPopupMessage('otpRegErrorMsg', res.data.message, true);
            } else {
                handleModalError(res, 'registerErrorMsg');
            }
        }
 
        // 2. OTP Register Handler
        async function handleVerifyRegisterSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('btnOtpRegSubmit');
            btn.disabled = true;
            btn.textContent = 'Memverifikasi...';
            document.getElementById('otpRegErrorMsg').classList.add('hidden');
 
            const payload = { email: activeEmail, token: parseInt(document.getElementById('reg_otp_code').value) };
            const res = await apiPost('/api/verify-token', payload);
 
            btn.disabled = false;
            btn.textContent = 'Verifikasi & Aktifkan';
 
            if (res.ok) {
                showPopupMessage('otpRegErrorMsg', res.data.message, true);
                localStorage.setItem('access_token', res.data.access_token);
                if (res.data.data && res.data.data.jenis_akun_id_jenis_akun) {
                    localStorage.setItem('jenis_akun_id', res.data.data.jenis_akun_id_jenis_akun);
                }
                if (res.data.data) {
                    localStorage.setItem('user_cache', JSON.stringify(res.data.data));
                }
                setTimeout(() => {
                    window.location.href = res.data.redirect_to;
                }, 1200); 
            } else {
                handleModalError(res, 'otpRegErrorMsg');
            }
        }
 
        // 3. Resend OTP 
        async function handleResendOtp() {
            if (isResending) return; 
            if (!activeEmail) {
                showPopupMessage('otpRegErrorMsg', 'Email tidak ditemukan, silakan ulangi proses.', false);
                return;
            }
 
            isResending = true;
            const resBtn = document.getElementById('btnResendOtp');
            resBtn.textContent = 'Mengirim ulang...';
            resBtn.disabled = true;
 
            const res = await apiPost('/api/resend-otp', { email: activeEmail });
 
            isResending = false;
            resBtn.textContent = 'Kirim Ulang OTP';
            resBtn.disabled = false;
 
            if (res.ok) {
                showPopupMessage('otpRegErrorMsg', res.data.message, true);
            } else {
                handleModalError(res, 'otpRegErrorMsg');
            }
        }
 
        // 4. Login Tahap 1 Handler (Dengan reset form saat sukses)
        async function handleLoginSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('btnLoginSubmit');
            btn.disabled = true;
            btn.textContent = 'Memeriksa...';
            document.getElementById('loginErrorMsg').classList.add('hidden');
 
            activeEmail = document.getElementById('login_email').value;
            const payload = { email: activeEmail, password: document.getElementById('login_password').value };
            
            const res = await apiPost('/api/login', payload);
 
            btn.disabled = false;
            btn.textContent = 'Masuk dan lanjutkan';
 
            if (res.ok) {
                // Jika regular user (OTP di-bypass, access_token langsung didapat)
                if (res.data.access_token) {
                    localStorage.setItem('access_token', res.data.access_token);
                    if (res.data.data && res.data.data.jenis_akun_id_jenis_akun) {
                        localStorage.setItem('jenis_akun_id', res.data.data.jenis_akun_id_jenis_akun);
                    }
                    if (res.data.data) {
                        localStorage.setItem('user_cache', JSON.stringify(res.data.data));
                    }
                    // Redirect langsung ke dashboard tanpa jeda / modal OTP
                    window.location.href = res.data.redirect_to || '/dashboard/user';
                    return;
                }

                document.querySelector('#loginModal form').reset(); // Bersihkan form
                switchModal('loginModal', 'otpLoginModal');
                showPopupMessage('otpLoginErrorMsg', res.data.message, true);
            } else {
                handleModalError(res, 'loginErrorMsg');
            }
        }
// 5. OTP Login Tahap 2 Handler
        async function handleVerifyLoginSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('btnOtpLoginSubmit');
            btn.disabled = true;
            btn.textContent = 'Memverifikasi...';
            document.getElementById('otpLoginErrorMsg').classList.add('hidden');
 
            const payload = { email: activeEmail, token: parseInt(document.getElementById('login_otp_code').value) };
            const res = await apiPost('/api/login/verify', payload);
 
            btn.disabled = false;
            btn.textContent = 'Masuk ke Dashboard';
 
            if (res.ok) {
                showPopupMessage('otpLoginErrorMsg', res.data.message, true);
                
                // Simpan access token untuk otorisasi API
                localStorage.setItem('access_token', res.data.access_token);
                
                // Simpan ID jenis akun ke localStorage untuk validasi role/isolasi halaman
                if (res.data.data && res.data.data.jenis_akun_id_jenis_akun) {
                    localStorage.setItem('jenis_akun_id', res.data.data.jenis_akun_id_jenis_akun);
                }
                if (res.data.data) {
                    localStorage.setItem('user_cache', JSON.stringify(res.data.data));
                }
 
                setTimeout(() => {
                    window.location.href = res.data.redirect_to; 
                }, 1200); 
            } else {
                handleModalError(res, 'otpLoginErrorMsg');
            }
        }
    </script>

    <!-- KATALOG -->
    <script>window.KATALOG_OPTIONS = { container: 'catalogContainer', showRating: true };</script>
    <script>document.addEventListener('DOMContentLoaded', () => { if (window.Katalog && Katalog.bindSearch) Katalog.bindSearch({ inputs: ['searchInputDesktop', 'searchInputMobile'], withCategory: false, withGps: false }); else console.error('katalog.js tidak termuat atau masih versi lama (Katalog.bindSearch tidak ditemukan)'); });</script>
    <script src="{{ asset('js/katalog.js') }}?v={{ @filemtime(public_path('js/katalog.js')) }}"></script>
    <script>window.TOKO_DETAIL_OPTIONS = { mode: 'guest' };</script>
    <script src="{{ asset('js/toko-detail.js') }}?v={{ @filemtime(public_path('js/toko-detail.js')) }}"></script>
    <script>
        const filterConfigs = {
            rekomendasi: { title: 'Tempat Rekomendasi 👋', subtitle: 'Ayo cari tempat terbaikmu untuk bersantai dan berdiskusi', others: ['terdekat', 'general'] },
            terdekat:    { title: 'Tempat Terdekat 🗾',    subtitle: 'Ayo cari tempat yang sesuai',                              others: ['rekomendasi', 'general'] },
            general:     { title: 'Menjelajah Tempat 👋',  subtitle: 'Ayo jelajahi berbagai macam tempat',                       others: ['rekomendasi', 'terdekat'] }
        };
        const filterLabels = { rekomendasi: 'Rekomendasi 🔵', terdekat: 'Terdekat 🔵', general: 'General 🔵' };

        function setFilter(type) {
            const cfg = filterConfigs[type];
            document.getElementById('filterTitle').innerText = cfg.title;
            document.getElementById('filterSubtitle').innerText = cfg.subtitle;
            const box = document.getElementById('filterButtons');
            box.innerHTML = '';
            cfg.others.forEach((id, i) => {
                const btn = document.createElement('button');
                btn.className = (i === 0
                    ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/25 hover:bg-blue-700'
                    : 'bg-white text-slate-700 border border-slate-200 shadow-sm hover:bg-slate-50')
                    + ' text-sm px-4 py-2 rounded-xl font-bold transition';
                btn.innerText = filterLabels[id];
                btn.onclick = () => setFilter(id);
                box.appendChild(btn);
            });
            Katalog.load(type);
        }
        document.addEventListener('DOMContentLoaded', () => setFilter('rekomendasi'));
    </script>
</body>
</html>