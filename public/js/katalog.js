/**
 * Katalog + Pencarian NongkrongID (Landing Page, Dashboard User, Dashboard Mitra)
 *
 * Konfigurasi (opsional, SEBELUM file ini dimuat):
 *   window.KATALOG_OPTIONS = { container: 'catalogContainer', showRating: true };
 *
 * API:
 *   Katalog.load('rekomendasi' | 'terdekat' | 'general')
 *   Katalog.bindSearch({ inputs: ['searchInput'], withCategory: true, withGps: true })
 *   Katalog.toggleGps() / openCategories() / closeCategories() / applyCategories() / reset()
 */
(function () {
    // TODO: ganti dengan rata-rata rating asli saat sistem bintang sudah dibuat
    const RATING_DEFAULT = 5;

    const GPS_ON = 'bg-green-600 hover:bg-green-700 text-white px-3.5 py-2 rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow-md border border-green-400 flex-shrink-0';
    const GPS_OFF = 'bg-blue-700 hover:bg-blue-800 text-white px-3.5 py-2 rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow-sm border border-blue-500/50 flex-shrink-0';

    const state = {
        type: null, page: 1, lastPage: 1, reqId: 0,
        geoPromise: null, coords: null,
        search: { q: '', kategori: [], kategoriNama: [], gps: false },
        bound: { inputs: [], withCategory: false, withGps: false },
    };

    const opts = () => Object.assign(
        { container: 'catalogContainer', showRating: true, endpoint: '/api/katalog' },
        window.KATALOG_OPTIONS || {}
    );
    const $ = (id) => document.getElementById(id);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const jam = (t) => (t ? String(t).slice(0, 5).replace(':', '.') : '-');
    const rupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    const isSearchActive = () => state.search.q.trim() !== '' || state.search.kategori.length > 0;

    /* ---------------- Style & toast ---------------- */
    function injectStyle() {
        if ($('katalog-style')) return;
        const st = document.createElement('style');
        st.id = 'katalog-style';
        st.textContent = `
            .kat-card { transition: transform .2s ease, box-shadow .2s ease; }
            .kat-card:hover { transform: translateY(-2px); }
            .kat-premium {
                border-color: rgba(245, 158, 11, .55) !important;
                box-shadow: 0 0 0 1px rgba(245, 158, 11, .35), 0 10px 28px -8px rgba(245, 158, 11, .55) !important;
                background-image: linear-gradient(135deg, #fffdf5 0%, #ffffff 55%);
            }
            .kat-premium:hover { box-shadow: 0 0 0 1px rgba(245, 158, 11, .5), 0 14px 34px -8px rgba(245, 158, 11, .7) !important; }
            .kat-promo { background: linear-gradient(135deg, #7c3aed, #a855f7); }
            .kat-skel { animation: katPulse 1.2s ease-in-out infinite; }
            @keyframes katPulse { 50% { opacity: .45; } }
        `;
        document.head.appendChild(st);
    }

    let toastTimer;
    function toast(msg, isError) {
        let el = $('kat-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'kat-toast';
            document.body.appendChild(el);
        }
        el.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 z-[60] max-w-[90vw] text-center text-xs font-semibold px-4 py-2.5 rounded-full shadow-lg text-white '
            + (isError ? 'bg-red-600' : 'bg-gray-900');
        el.textContent = msg;
        el.style.display = 'block';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { el.style.display = 'none'; }, 3000);
    }

    /* ---------------- Lokasi ---------------- */
    function getCoords() {
        if (state.geoPromise) return state.geoPromise;
        state.geoPromise = new Promise((resolve) => {
            if (!navigator.geolocation) return resolve(null);
            navigator.geolocation.getCurrentPosition(
                (p) => { state.coords = { lat: p.coords.latitude, lng: p.coords.longitude }; resolve(state.coords); },
                () => { state.geoPromise = null; resolve(null); },
                { timeout: 6000, maximumAge: 300000 }
            );
        });
        return state.geoPromise;
    }
    const withTimeout = (p, ms) => Promise.race([p, new Promise((r) => setTimeout(() => r(null), ms))]);

    /* ---------------- Kartu ---------------- */
    function cardHtml(t, showRating) {
        const img = t.gambar_utama
            ? `<img src="${esc(t.gambar_utama)}" alt="${esc(t.nama_toko)}" loading="lazy" class="w-full h-full object-cover">`
            : `<div class="w-full h-full grid place-items-center text-gray-300"><i class="fa-solid fa-mug-hot text-3xl"></i></div>`;
        const badge = t.is_premium
            ? `<i class="fa-solid fa-circle-check text-blue-500 text-[13px] flex-shrink-0" title="Mitra Terverifikasi"></i>` : '';
        const rating = showRating
            ? `<span class="flex items-center gap-1 text-xs sm:text-sm font-bold text-gray-800 flex-shrink-0"><span data-rating>${t.rating_rata_rata != null ? Number(t.rating_rata_rata).toFixed(1) : RATING_DEFAULT}</span> <i class="fa-solid fa-star text-amber-400"></i></span>` : '';
        const status = t.status_buka
            ? `<span class="font-bold text-green-600">Open</span>` : `<span class="font-bold text-red-500">Close</span>`;
        const jarak = t.jarak_km != null ? `<span class="ml-1 text-gray-400">• ${esc(t.jarak_km)} km</span>` : '';
        const promo = t.ada_promo
            ? `<span class="kat-promo text-white text-[10px] sm:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm">Ada Promo 🔥</span>` : '';

        return `
        <article data-toko-id="${t.id_toko}" class="kat-card cursor-pointer ${t.is_premium ? 'kat-premium' : ''} flex gap-3 bg-white rounded-2xl p-2.5 border border-gray-100 shadow-sm">
            <div class="w-28 h-28 sm:w-40 sm:h-32 flex-shrink-0 rounded-xl overflow-hidden bg-gray-100">${img}</div>
            <div class="flex-1 min-w-0 flex flex-col">
                <div class="flex items-start justify-between gap-2">
                    <h4 class="flex items-center gap-1.5 min-w-0 font-extrabold text-sm sm:text-base text-gray-900 text-left">
                        <span class="truncate">${esc(t.nama_toko)}</span>${badge}
                    </h4>
                    ${rating}
                </div>
                <dl class="mt-1.5 text-[11px] sm:text-xs text-gray-600 space-y-0.5 text-left">
                    <div class="flex gap-1"><dt class="w-20 font-semibold text-gray-800">Harga mulai</dt><dd>: ${rupiah(t.harga_mulai)}</dd></div>
                    <div class="flex gap-1"><dt class="w-20 font-semibold text-gray-800">Operasional</dt><dd>: ${jam(t.jam_buka)} - ${jam(t.jam_tutup)}</dd></div>
                    <div class="flex gap-1"><dt class="w-20 font-semibold text-gray-800">Status</dt><dd>: ${status}${jarak}</dd></div>
                </dl>
                <div class="mt-auto pt-1 flex justify-end">${promo}</div>
            </div>
        </article>`;
    }

    function skeleton() {
        return Array.from({ length: 3 }).map(() => `
            <div class="kat-skel flex gap-3 bg-white rounded-2xl p-2.5 border border-gray-100">
                <div class="w-28 h-28 sm:w-40 sm:h-32 rounded-xl bg-gray-200"></div>
                <div class="flex-1 space-y-2 pt-2"><div class="h-4 w-1/2 bg-gray-200 rounded"></div><div class="h-3 w-2/3 bg-gray-100 rounded"></div><div class="h-3 w-1/2 bg-gray-100 rounded"></div></div>
            </div>`).join('');
    }

    /* ---------------- Header mode pencarian ---------------- */
    function renderSearchHeader(total) {
        const t = $('filterTitle'), s = $('filterSubtitle'), b = $('filterButtons');
        if (t) t.innerText = 'Hasil Pencarian 🔍';
        if (s) {
            const parts = [];
            if (state.search.q.trim()) parts.push('Nama: “' + state.search.q.trim() + '”');
            if (state.search.kategoriNama.length) parts.push('Kategori: ' + state.search.kategoriNama.join(', '));
            if (state.search.gps) parts.push('Diurutkan dari terdekat');
            let txt = parts.join(' • ');
            if (total != null) txt += (txt ? ' — ' : '') + total + ' tempat ditemukan';
            s.innerText = txt;
        }
        if (b) {
            b.innerHTML = '';
            const btn = document.createElement('button');
            btn.className = 'bg-blue-600 text-white hover:bg-blue-700 shadow-sm text-xs sm:text-sm px-4 py-2 rounded-xl font-bold transition';
            btn.innerHTML = '<i class="fa-solid fa-arrow-left mr-1.5"></i>Kembali';
            btn.onclick = reset;
            b.appendChild(btn);
        }
    }

    /* ---------------- Muat katalog ---------------- */
    async function fetchPage(type, page, coords) {
        const p = new URLSearchParams({ page });
        if (isSearchActive()) {
            p.set('filter', 'general');
            if (state.search.q.trim()) p.set('q', state.search.q.trim());
            state.search.kategori.forEach((id) => p.append('kategori[]', id));
            if (state.search.gps && coords) { p.set('lat', coords.lat); p.set('lng', coords.lng); }
        } else {
            p.set('filter', type);
            if (coords) { p.set('lat', coords.lat); p.set('lng', coords.lng); }
        }
        const res = await fetch(`${opts().endpoint}?${p}`, { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    async function load(type, append = false) {
        injectStyle();
        const o = opts();
        const box = $(o.container);
        if (!box) return;

        const searching = isSearchActive();
        const myReq = ++state.reqId;

        if (!append) {
            state.type = type; state.page = 1;
            box.className = 'space-y-3';
            box.innerHTML = skeleton();
            if (searching) renderSearchHeader(null);
        }

        try {
            let coords = state.coords;
            if (!searching && !coords && type !== 'general') {
                coords = await withTimeout(getCoords(), 2500);
            }
            const json = await fetchPage(type, state.page, coords);
            if (myReq !== state.reqId) return;

            state.lastPage = json.meta.last_page;
            const items = json.data || [];
            if (searching && !append) renderSearchHeader(json.meta.total);

            if (!items.length && !append) {
                box.className = 'bg-white rounded-2xl p-8 text-center border border-gray-100 shadow-sm text-xs text-gray-400 italic';
                box.innerHTML = searching
                    ? 'Tidak ada tempat yang cocok dengan pencarianmu. Coba kata kunci atau kategori lain.'
                    : 'Belum ada tempat yang tersedia.';
                return;
            }

            if (append) {
                box.querySelector('[data-kat-more]')?.remove();
                box.insertAdjacentHTML('beforeend', items.map((t) => cardHtml(t, o.showRating)).join(''));
            } else {
                let html = '';
                if (!searching && type !== 'general' && !coords) {
                    html += `<p class="text-[11px] text-amber-600 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2 text-left"><i class="fa-solid fa-location-crosshairs mr-1"></i>Aktifkan izin lokasi di browser agar tempat diurutkan berdasarkan yang terdekat darimu.</p>`;
                }
                box.innerHTML = html + items.map((t) => cardHtml(t, o.showRating)).join('');
            }

            if (!append && !searching && !coords && type !== 'general') {
                getCoords().then((c) => { if (c && myReq === state.reqId) load(type, false); });
            }

            if (state.page < state.lastPage) {
                box.insertAdjacentHTML('beforeend',
                    `<button data-kat-more class="w-full py-2.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-blue-600 hover:bg-blue-50 transition">Muat lebih banyak</button>`);
                box.querySelector('[data-kat-more]').onclick = () => { state.page++; load(state.type, true); };
            }
        } catch (e) {
            if (myReq !== state.reqId) return;
            box.className = 'bg-white rounded-2xl p-8 text-center border border-red-100 text-xs text-red-500';
            box.innerHTML = 'Gagal memuat katalog. <button class="underline font-bold" onclick="Katalog.load(Katalog.current())">Coba lagi</button>';
        }
    }

    /* ---------------- Pencarian ---------------- */
    function focusCatalog() {
        // Dashboard user/mitra: pastikan tab Dashboard yang terbuka
        const sec = $('section-dashboard');
        if (sec && !sec.classList.contains('active')) {
            document.querySelector('[data-target="section-dashboard"]')?.click();
        }
        const el = $('filterTitle') || $(opts().container);
        if (el) {
            el.style.scrollMarginTop = '9rem';
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function exitSearch() {
        if (typeof window.setFilter === 'function') window.setFilter('rekomendasi');
        else load('rekomendasi');
    }

    function afterChange(wasActive, scroll) {
        if (isSearchActive()) {
            if (!wasActive || scroll) focusCatalog();
            load(state.type || 'rekomendasi');
        } else if (wasActive) {
            exitSearch();
        }
    }

    function setQuery(value, scroll) {
        const was = isSearchActive();
        state.search.q = value;
        afterChange(was, scroll);
    }

    function bindSearch(cfg) {
        state.bound = Object.assign(state.bound, cfg || {});
        const ids = state.bound.inputs || [];
        let timer;
        ids.forEach((id) => {
            const el = $(id);
            if (!el) return;
            el.addEventListener('input', () => {
                ids.forEach((other) => { if (other !== id && $(other)) $(other).value = el.value; });
                clearTimeout(timer);
                timer = setTimeout(() => setQuery(el.value, false), 400);
            });
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); clearTimeout(timer); setQuery(el.value, true); }
            });
        });
        if (state.bound.withCategory) loadKategori();
    }

    /* ---------------- Kategori ---------------- */
    function kategoriNama(k) {
        for (const key of ['nama_kategori', 'nama', 'kategori', 'nama_kat', 'name', 'label']) {
            if (typeof k[key] === 'string') return k[key];
        }
        const first = Object.entries(k).find(([key, v]) => typeof v === 'string' && !/id|path|gambar|created|updated/i.test(key));
        return first ? first[1] : 'Kategori ' + (k.id_kategori ?? k.id ?? '');
    }

    async function loadKategori() {
        const box = $('categoryList');
        if (!box) return;
        try {
            const r = await fetch('/api/kategori', { headers: { Accept: 'application/json' } });
            const j = await r.json();
            const list = Array.isArray(j) ? j : (j.data || []);
            box.innerHTML = list.map((k) => {
                const id = k.id_kategori ?? k.id;
                const nama = kategoriNama(k);
                return `<label class="flex items-center gap-3 p-2.5 rounded-xl border border-gray-100 hover:bg-blue-50/60 cursor-pointer transition">
                    <input type="checkbox" value="${esc(id)}" data-nama="${esc(nama)}" class="category-checkbox rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                    <span>${esc(nama)}</span></label>`;
            }).join('') || '<p class="text-gray-400 italic">Belum ada kategori.</p>';
        } catch (e) {
            box.innerHTML = '<p class="text-red-500">Gagal memuat kategori.</p>';
        }
    }

    function openCategories() {
        const chosen = state.search.kategori.map(String);
        document.querySelectorAll('.category-checkbox').forEach((c) => { c.checked = chosen.includes(String(c.value)); });
        $('categoryModal')?.classList.remove('hidden');
    }
    function closeCategories() { $('categoryModal')?.classList.add('hidden'); }

    function applyCategories() {
        const was = isSearchActive();
        const checked = [...document.querySelectorAll('.category-checkbox')].filter((c) => c.checked);
        state.search.kategori = checked.map((c) => c.value);
        state.search.kategoriNama = checked.map((c) => c.dataset.nama);
        closeCategories();
        afterChange(was, true);
    }

    /* ---------------- GPS ---------------- */
    function setGpsButton(on) {
        const b = $('gpsBtn');
        if (b) b.className = on ? GPS_ON : GPS_OFF;
    }

    async function toggleGps() {
        if (state.search.gps) {
            state.search.gps = false;
            setGpsButton(false);
            toast('GPS dinonaktifkan.');
            if (isSearchActive()) load(state.type || 'rekomendasi');
            return;
        }
        toast('Mencari lokasimu...');
        const c = await withTimeout(getCoords(), 10000);
        if (!c) {
            toast('Lokasi tidak bisa diakses. Aktifkan izin lokasi di browser lalu coba lagi.', true);
            return;
        }
        state.search.gps = true;
        setGpsButton(true);
        if (isSearchActive()) {
            toast('GPS aktif. Hasil diurutkan dari yang terdekat.');
            load(state.type || 'rekomendasi');
        } else {
            toast('GPS aktif. Hasil pencarian akan diurutkan dari yang terdekat.');
        }
    }

    /* ---------------- Kembali / reset semua ---------------- */
    function reset() {
        state.search = { q: '', kategori: [], kategoriNama: [], gps: false };
        (state.bound.inputs || []).forEach((id) => { if ($(id)) $(id).value = ''; });
        document.querySelectorAll('.category-checkbox').forEach((c) => { c.checked = false; });
        setGpsButton(false);
        exitSearch();
    }

    window.Katalog = {
        load: (t) => load(t, false),
        current: () => state.type || 'rekomendasi',
        bindSearch, toggleGps, openCategories, closeCategories, applyCategories, reset, cardHtml,
    };
})();