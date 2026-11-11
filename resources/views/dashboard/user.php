<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tempat Rekomendasi | NongkrongID</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#FCFCFD] text-gray-800 font-sans antialiased pb-20 md:pb-10">

    <!-- 1. NAVBAR UTAMA (RESPONSIF: DESKTOP & MOBILE) -->
    <nav class="bg-blue-600 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-mug-hot text-2xl"></i>
                <span class="font-bold text-xl tracking-wide">NongkrongID</span>
            </div>

            <!-- Search bar di Navbar (Desktop) -->
            <div class="hidden md:flex flex-1 max-w-md mx-8">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" placeholder="Mencari tempat menarik ..." class="w-full pl-10 pr-4 py-2 rounded-full text-sm text-gray-700 focus:outline-none shadow-inner">
                </div>
            </div>

            <!-- Navigasi Menu Atas (Desktop) & Tombol Keluar -->
            <div class="hidden md:flex items-center space-x-6 text-sm font-medium">
                <a href="#dashboard" class="hover:text-blue-200 transition font-bold border-b-2 border-white pb-1">Dashboard</a>
                <a href="#favorit" class="hover:text-blue-200 transition">Daftar Favorit</a>
                <a href="#akun" class="hover:text-blue-200 transition">Akun</a>
                <button onclick="handleLogout()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm">Keluar</button>
            </div>

            <!-- Tombol Keluar Mobile -->
            <div class="md:hidden">
                <button onclick="handleLogout()" class="bg-red-500 text-white px-3 py-1.5 rounded-xl text-xs font-bold">Keluar</button>
            </div>
        </div>
    </nav>

    <!-- Search Bar khusus Mobile (Persis seperti gambar referensi) -->
    <div class="md:hidden bg-blue-600 px-4 pb-4 pt-2">
        <div class="relative flex items-center gap-2">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" placeholder="Mencari tempat menarik ..." class="w-full pl-10 pr-4 py-2.5 rounded-full text-sm text-gray-700 focus:outline-none shadow-inner">
            </div>
            <button class="bg-white text-blue-600 px-4 py-2.5 rounded-2xl text-xs font-bold shadow-sm flex items-center gap-1">
                <i class="fa-solid fa-location-dot text-blue-600"></i> Lokasi
            </button>
            <button class="bg-white text-blue-600 px-4 py-2.5 rounded-2xl text-xs font-bold shadow-sm">
                Kategori
            </button>
        </div>
    </div>

    <!-- BOTTOM NAV BAR (KHUSUS MOBILE - SESUAI GAMBAR) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-blue-600 text-white shadow-lg z-50 flex justify-around py-3 border-t border-blue-500">
        <a href="#dashboard" class="flex flex-col items-center text-xs font-bold">
            <i class="fa-solid fa-house text-lg mb-0.5"></i>
            <span>dashboard</span>
        </a>
        <a href="#favorit" class="flex flex-col items-center text-xs opacity-80 hover:opacity-100">
            <i class="fa-solid fa-heart text-lg mb-0.5"></i>
            <span>daftar favorit</span>
        </a>
        <a href="#akun" class="flex flex-col items-center text-xs opacity-80 hover:opacity-100">
            <i class="fa-solid fa-user text-lg mb-0.5"></i>
            <span>akun</span>
        </a>
    </div>

    <!-- MAIN CONTAINER -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- 1. INFORMASI MENARIK (BANNER PROMO) -->
        <section class="space-y-3">
            <h2 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                Informasi Menarik ✨
            </h2>
            <div class="w-full bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 p-2 sm:p-4 flex justify-center">
                <!-- Gunakan asset gambar promo atau placeholder -->
                <div class="w-full h-44 sm:h-72 rounded-2xl overflow-hidden relative shadow-inner bg-amber-900">
                    <img src="{{ asset('images/Poster.jpg') }}" onerror="this.src='https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=1000&q=80'" class="w-full h-full object-cover" alt="Promo Spesial">
                </div>
            </div>
        </section>

        <!-- 2. TEMPAT REKOMENDASI & FILTER -->
        <section class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 pb-3">
                <div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 flex items-center gap-2">
                        Tempat Rekomendasi 👋
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-500">Ayo cari tempat terbaikmu</p>
                </div>
                <div class="flex space-x-2">
                    <button class="bg-blue-600 text-white text-xs sm:text-sm px-4 py-2 rounded-xl font-medium shadow hover:bg-blue-700 transition">Terdekat 🔵</button>
                    <button class="bg-white text-gray-700 border border-gray-300 text-xs sm:text-sm px-4 py-2 rounded-xl font-medium shadow-sm hover:bg-gray-50 transition">General 🔵</button>
                </div>
            </div>
            
            <!-- LIST KARTU REKOMENDASI (Sesuai Mockup Gambar) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <!-- Card 1: Cantika Cafe -->
                <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex gap-4 items-center relative hover:shadow-md transition">
                    <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl overflow-hidden flex-shrink-0 bg-gray-100">
                        <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover" alt="Cantika Cafe">
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-gray-900 text-sm sm:text-base flex items-center gap-1">
                                Cantika Cafe <i class="fa-solid fa-circle-check text-blue-500 text-xs"></i>
                            </h4>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 flex items-center gap-1">
                                4,3 <i class="fa-solid fa-star text-black text-xs"></i>
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 line-clamp-1">Cafe Estetik bernuansa Modern Hause pa..</p>
                        <p class="text-[11px] text-blue-600 font-medium">Tempat sangat dekat dengan anda</p>
                        
                        <div class="text-[11px] text-gray-600 pt-1 space-y-0.5">
                            <div><strong class="text-gray-700">Fasilitas :</strong> Indoor, Alam, Minimalis ..</div>
                            <div><strong class="text-gray-700">Operasional :</strong> 09.00 - 23.59</div>
                            <div class="flex justify-between items-center pt-1">
                                <span><strong class="text-gray-700">Status :</strong> <span class="text-green-600 font-bold">Open</span></span>
                                <span class="bg-gradient-to-r from-purple-600 to-pink-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">Ada Promo 🔥</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: ManukKopi Cafe -->
                <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex gap-4 items-center relative hover:shadow-md transition">
                    <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl overflow-hidden flex-shrink-0 bg-gray-100">
                        <img src="https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover" alt="ManukKopi Cafe">
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-gray-900 text-sm sm:text-base flex items-center gap-1">
                                ManukKopi Cafe <i class="fa-solid fa-circle-check text-blue-500 text-xs"></i>
                            </h4>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 flex items-center gap-1">
                                4,9 <i class="fa-solid fa-star text-black text-xs"></i>
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 line-clamp-1">Cafe Malam tempat untuk mahasiswa d..</p>
                        <p class="text-[11px] text-blue-600 font-medium">Tempat dekat dengan anda</p>
                        
                        <div class="text-[11px] text-gray-600 pt-1 space-y-0.5">
                            <div><strong class="text-gray-700">Fasilitas :</strong> Indoor, Outdoor, AC, Luas, ..</div>
                            <div><strong class="text-gray-700">Operasional :</strong> 11.00 - 23.59</div>
                            <div class="flex justify-between items-center pt-1">
                                <span><strong class="text-gray-700">Status :</strong> <span class="text-red-500 font-bold">Close</span></span>
                                <span class="bg-gradient-to-r from-purple-600 to-pink-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">Ada Promo 🔥</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Alaskar Cafe -->
                <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex gap-4 items-center relative hover:shadow-md transition">
                    <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl overflow-hidden flex-shrink-0 bg-gray-100">
                        <img src="https://images.unsplash.com/photo-1442512595331-e89e73853f31?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover" alt="Alaskar Cafe">
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-gray-900 text-sm sm:text-base flex items-center gap-1">
                                Alaskar Cafe <i class="fa-solid fa-circle-check text-blue-500 text-xs"></i>
                            </h4>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 flex items-center gap-1">
                                4,6 <i class="fa-solid fa-star text-black text-xs"></i>
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 line-clamp-1">Cafe bertempat di Jl. Ahmad Yani ..</p>
                        <p class="text-[11px] text-blue-600 font-medium">Tempat sangat dekat dengan anda</p>
                        
                        <div class="text-[11px] text-gray-600 pt-1 space-y-0.5">
                            <div><strong class="text-gray-700">Fasilitas :</strong> TV, Luas, Indoor, Outdoor ..</div>
                            <div><strong class="text-gray-700">Operasional :</strong> 10.00 - 23.00</div>
                            <div class="flex justify-between items-center pt-1">
                                <span><strong class="text-gray-700">Status :</strong> <span class="text-green-600 font-bold">Open</span></span>
                                <span class="bg-gradient-to-r from-purple-600 to-pink-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">Ada Promo 🔥</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </main>

    <script>
        function handleLogout() {
            localStorage.removeItem('auth_token');
            window.location.href = '/';
        }
    </script>
</body>
</html>