/**
 * public/js/toko-detail.js
 * Popup detail toko. Mode otomatis:
 *   guest -> hanya pesan ajakan bergabung (landing page)
 *   user  -> detail + rata-rata bintang + love + beri/hapus ulasan
 *   mitra -> detail + daftar ulasan (tanpa bintang rata-rata, love, atau input ulasan)
 * Dipicu klik pada elemen apa pun yang punya atribut data-toko-id="<id_toko>".
 * Opsional: window.TOKO_DETAIL_OPTIONS = { mode: 'guest' } (dipakai di welcome.blade.php)
 */
(function () {
    const OPT = window.TOKO_DETAIL_OPTIONS || {};
    const token = () => localStorage.getItem('access_token');

    function mode() {
        if (OPT.mode) return OPT.mode;
        if (!token()) return 'guest';
        const j = Number(localStorage.getItem('jenis_akun_id'));
        return (j >= 3 && j <= 6) ? 'mitra' : 'user';
    }

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const rupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(n) || 0);
    const stars = (n, cls = 'text-sm') => {
        n = Math.round(n);
        let h = '';
        for (let i = 1; i <= 5; i++) h += `<i class="fa-solid fa-star ${cls} ${i <= n ? 'text-amber-400' : 'text-gray-300'}"></i>`;
        return h;
    };
    const waLink = no => {
        let d = String(no || '').replace(/\D/g, '');
        if (d.startsWith('0')) d = '62' + d.slice(1);
        return d ? 'https://wa.me/' + d : null;
    };
    const fmtDate = s => { try { return new Date(String(s).replace(' ', 'T')).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }); } catch (e) { return ''; } };

    async function api(url, method = 'GET', body = null) {
        const res = await fetch(url, {
            method,
            headers: Object.assign({ 'Accept': 'application/json', 'Authorization': 'Bearer ' + token() },
                body ? { 'Content-Type': 'application/json' } : {}),
            body: body ? JSON.stringify(body) : null
        });
        let json = {};
        try { json = await res.json(); } catch (e) {}
        return { ok: res.ok, status: res.status, json };
    }

    // ---------- Modal shell ----------
    let root, box, state = { idx: 0, data: null, rating: 0, id: null };

    function ensureRoot() {
        if (root) return;
        root = document.createElement('div');
        root.id = 'tokoDetailModal';
        root.className = 'fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] hidden items-center justify-center p-4';
        root.innerHTML = '<div id="tokoDetailBox" class="bg-white w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl shadow-2xl relative"></div>';
        document.body.appendChild(root);
        box = root.firstChild;
        root.addEventListener('click', e => { if (e.target === root) close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
    }
    function show() { root.classList.remove('hidden'); root.classList.add('flex'); document.body.style.overflow = 'hidden'; }
    function close() { if (!root) return; root.classList.add('hidden'); root.classList.remove('flex'); document.body.style.overflow = ''; }

    const closeBtn = '<button type="button" data-td="close" class="absolute top-3 right-3 z-10 w-9 h-9 rounded-full bg-white/90 hover:bg-white shadow text-gray-500 hover:text-gray-800 grid place-items-center"><i class="fa-solid fa-xmark"></i></button>';

    // ---------- Open ----------
    async function open(id) {
        ensureRoot();
        state = { idx: 0, data: null, rating: 0, id };
        const m = mode();

        if (m === 'guest') {
            box.innerHTML = closeBtn + `
              <div class="p-8 pt-12 text-center">
                <div class="text-5xl mb-4">🤩</div>
                <p class="font-extrabold text-lg text-[#0B1B3A] leading-snug">Detail lengkapnya dapat bergabung pada website kami ⭐ </p>
                <button type="button" data-td="close" class="mt-6 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold">Tutup</button>
              </div>`;
            show();
            return;
        }

        box.innerHTML = closeBtn + '<div class="p-12 text-center text-gray-400 text-sm"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat detail...</div>';
        show();
        const r = await api(`/api/toko/${id}/detail`);
        if (!r.ok) {
            box.innerHTML = closeBtn + `<div class="p-10 pt-14 text-center text-sm text-red-600">${esc(r.json.message || 'Gagal memuat detail toko.')}
              <div><button type="button" data-td="close" class="mt-5 px-5 py-2 rounded-xl bg-gray-100 text-gray-700 font-bold">Tutup</button></div></div>`;
            return;
        }
        state.data = r.json.data;
        render();
    }

    // ---------- Render ----------
    function render() {
        const d = state.data, m = mode();
        const imgs = d.gambar || [];
        const wa = waLink(d.no_kontak);
        const scroll = box.scrollTop;

        const slider = imgs.length ? `
          <div class="relative bg-gray-100 rounded-t-3xl overflow-hidden aspect-[16/10]">
            <img id="tdImg" src="${esc(imgs[state.idx])}" class="w-full h-full object-cover" alt="${esc(d.nama_toko)}">
            ${imgs.length > 1 ? `
              <button type="button" data-td="prev" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/45 hover:bg-black/65 text-white"><i class="fa-solid fa-chevron-left text-xs"></i></button>
              <button type="button" data-td="next" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/45 hover:bg-black/65 text-white"><i class="fa-solid fa-chevron-right text-xs"></i></button>
              <span class="absolute bottom-2 right-3 text-[11px] font-bold bg-black/50 text-white px-2 py-0.5 rounded-full">${state.idx + 1}/${imgs.length}</span>` : ''}
          </div>` : `<div class="aspect-[16/10] bg-gray-100 rounded-t-3xl grid place-items-center text-gray-300"><i class="fa-solid fa-image text-4xl"></i></div>`;

        const userBar = m === 'user' ? `
          <div class="flex items-center gap-3 mt-1">
            <div class="flex items-center gap-1.5">${stars(d.rating_rata_rata)}
              <span class="text-sm font-extrabold text-gray-800 ml-1">${Number(d.rating_rata_rata).toFixed(1)}</span>
              <span class="text-xs text-gray-400">(${d.jumlah_rating})</span></div>
          </div>` : '';

        const actions = `
          <div class="flex items-center gap-2 shrink-0">
            ${m === 'user' ? `<button type="button" data-td="love" title="Favorit" class="w-10 h-10 rounded-full border ${d.is_favorit ? 'bg-red-50 border-red-200 text-red-500' : 'border-gray-200 text-gray-400 hover:text-red-500'} grid place-items-center transition"><i class="fa-${d.is_favorit ? 'solid' : 'regular'} fa-heart"></i></button>` : ''}
            ${wa ? `<a href="${wa}" target="_blank" rel="noopener" title="WhatsApp" class="w-10 h-10 rounded-full bg-green-500 hover:bg-green-600 text-white grid place-items-center transition"><i class="fa-brands fa-whatsapp text-lg"></i></a>` : ''}
          </div>`;

        const kategori = (d.kategori || []).map(k => `<span class="text-[11px] font-bold bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full">${esc(k)}</span>`).join('');

        const gmaps = (d.lat != null && d.lng != null) ? `
          <a href="${esc(d.maps_url)}" target="_blank" rel="noopener" class="block relative rounded-xl overflow-hidden border border-gray-200 group">
            <iframe src="https://maps.google.com/maps?q=${encodeURIComponent(d.lat + ',' + d.lng)}&z=16&output=embed" class="w-full h-40 pointer-events-none" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            <span class="absolute bottom-2 right-2 text-[11px] font-bold bg-white/95 text-blue-700 px-2.5 py-1 rounded-full shadow group-hover:bg-blue-600 group-hover:text-white transition"><i class="fa-solid fa-location-dot mr-1"></i>Buka di Google Maps</span>
          </a>` : '<p class="text-xs text-gray-400 italic">Lokasi belum tersedia.</p>';

        const info = (icon, label, val) => `<div class="flex items-start gap-3 text-sm"><i class="fa-solid ${icon} w-4 mt-0.5 text-blue-500"></i><div><div class="text-[11px] uppercase tracking-wide text-gray-400 font-bold">${label}</div><div class="text-gray-800 font-semibold">${val}</div></div></div>`;

        const promo = d.promo ? `
          <div class="rounded-2xl border ${d.promo.aktif ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-gray-50'} p-4 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm font-extrabold text-gray-800"><i class="fa-solid fa-tags mr-1.5 text-amber-500"></i>Promo</span>
              <span class="text-[11px] font-bold px-2.5 py-1 rounded-full ${d.promo.aktif ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'}">${d.promo.aktif ? 'Promo Aktif' : 'Tidak Aktif'}</span>
            </div>
            ${d.promo.gambar ? `<img src="${esc(d.promo.gambar)}" class="w-full rounded-xl object-cover max-h-52" alt="Promo">` : ''}
            ${d.promo.deskripsi ? `<p class="text-sm text-gray-700 whitespace-pre-line">${esc(d.promo.deskripsi)}</p>` : ''}
          </div>` : '';

        // Form / ulasan saya (khusus user)
        const mine = (d.ulasan || []).find(u => u.is_mine);
        let form = '';
        if (m === 'user') {
            form = mine ? '' : `
              <div class="rounded-2xl border border-gray-200 p-4 space-y-3">
                <div class="text-sm font-extrabold text-gray-800">Beri Rating &amp; Ulasan</div>
                <div id="tdPick" class="flex gap-1">${[1, 2, 3, 4, 5].map(i => `<button type="button" data-td="pick" data-v="${i}" class="text-2xl ${i <= state.rating ? 'text-amber-400' : 'text-gray-300'} hover:text-amber-400"><i class="fa-solid fa-star"></i></button>`).join('')}</div>
                <textarea id="tdUlasan" rows="3" maxlength="255" placeholder="Tulis ulasan (opsional)..." class="w-full text-sm rounded-xl border border-gray-200 p-3 focus:outline-none focus:border-blue-500"></textarea>
                <div id="tdMsg" class="text-xs text-red-600 hidden"></div>
                <button type="button" data-td="submit" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold">Kirim</button>
              </div>`;
        }

        const list = (d.ulasan || []).length ? d.ulasan.map(u => `
            <div class="border-b border-gray-100 pb-3 last:border-0">
              <div class="flex items-center justify-between gap-2">
                <div class="text-sm font-bold text-gray-800">${esc(u.nama)}${u.is_mine ? ' <span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full ml-1">Anda</span>' : ''}</div>
                ${u.is_mine && m === 'user' ? '<button type="button" data-td="del" class="text-xs font-bold text-red-500 hover:text-red-700"><i class="fa-solid fa-trash mr-1"></i>Hapus</button>' : ''}
              </div>
              <div class="flex items-center gap-2 mt-0.5">${stars(u.rating, 'text-xs')}<span class="text-[11px] text-gray-400">${fmtDate(u.created_at)}</span></div>
              ${u.ulasan ? `<p class="text-sm text-gray-600 mt-1 whitespace-pre-line">${esc(u.ulasan)}</p>` : ''}
            </div>`).join('') : '<p class="text-sm text-gray-400 italic">Belum ada ulasan.</p>';

        box.innerHTML = closeBtn + slider + `
          <div class="p-5 sm:p-6 space-y-5">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0"><h3 class="text-xl font-extrabold text-[#0B1B3A] leading-tight">${esc(d.nama_toko)}</h3>${userBar}</div>
              ${actions}
            </div>
            <p class="text-sm text-gray-600 whitespace-pre-line">${esc(d.deskripsi_toko)}</p>
            ${kategori ? `<div class="flex flex-wrap gap-1.5">${kategori}</div>` : ''}
            ${gmaps}
            <div class="grid grid-cols-2 gap-4">
              ${info('fa-door-open', 'Jam Buka', esc(d.jam_buka))}
              ${info('fa-door-closed', 'Jam Tutup', esc(d.jam_tutup))}
              ${info('fa-calendar-xmark', 'Hari Tutup', esc(d.hari_tutup || '-'))}
              ${info('fa-tag', 'Harga Mulai', rupiah(d.harga_mulai))}
            </div>
            ${info('fa-phone', 'Nomor Kontak', esc(d.no_kontak || '-'))}
            ${promo}
            ${form}
            <div>
              <div class="text-sm font-extrabold text-gray-800 mb-3">Rating &amp; Ulasan (${(d.ulasan || []).length})</div>
              <div class="space-y-3">${list}</div>
            </div>
            <button type="button" data-td="close" class="w-full py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold">Tutup</button>
          </div>`;
        box.scrollTop = scroll;
    }

    // ---------- Actions ----------
    async function refresh() {
        const r = await api(`/api/toko/${state.id}/detail`);
        if (r.ok) {
            state.data = r.json.data; state.rating = 0; render();
            // sinkronkan bintang di kartu katalog (di luar popup)
            if (state.data.rating_rata_rata != null) {
                document.querySelectorAll('[data-toko-id="' + state.id + '"] [data-rating]')
                    .forEach(el => { el.textContent = Number(state.data.rating_rata_rata).toFixed(1); });
            }
        }
    }

    function showMsg(t) { const el = document.getElementById('tdMsg'); if (el) { el.textContent = t; el.classList.remove('hidden'); } }

    async function onAction(act, el) {
        const d = state.data;
        if (act === 'close') return close();
        if (act === 'prev' || act === 'next') {
            const n = d.gambar.length;
            state.idx = (state.idx + (act === 'next' ? 1 : -1) + n) % n;
            document.getElementById('tdImg').src = d.gambar[state.idx];
            return render();
        }
        if (act === 'pick') { state.rating = Number(el.dataset.v); return render(); }
        if (act === 'love') {
            const r = await api(`/api/toko/${state.id}/favorit`, 'POST');
            if (!r.ok) { alert(r.json.message || 'Gagal menyimpan favorit (status ' + r.status + ').'); return; }
            if (r.ok) {
                d.is_favorit = r.json.is_favorit; render();
                document.dispatchEvent(new CustomEvent('tokodetail:favorit-changed', { detail: { id: state.id, is_favorit: d.is_favorit } }));
            }
            return;
        }
        if (act === 'submit') {
            if (!state.rating) return showMsg('Silakan pilih rating bintang terlebih dahulu.');
            const ulasan = document.getElementById('tdUlasan').value.trim();
            const r = await api(`/api/toko/${state.id}/ulasan`, 'POST', { rating: state.rating, ulasan: ulasan || null });
            if (r.ok) return refresh();
            const errs = r.json.errors ? Object.values(r.json.errors).flat().join(' ') : '';
            return showMsg(errs || r.json.message || 'Gagal mengirim ulasan.');
        }
        if (act === 'del') {
            if (!confirm('Hapus ulasan Anda pada toko ini?')) return;
            const r = await api(`/api/toko/${state.id}/ulasan`, 'DELETE');
            if (r.ok) return refresh();
            alert(r.json.message || 'Gagal menghapus ulasan.');
        }
    }

    // ---------- Events (delegation) ----------
    document.addEventListener('click', e => {
        const a = e.target.closest('[data-td]');
        if (a && root && root.contains(a)) { e.preventDefault(); onAction(a.dataset.td, a); return; }
        const card = e.target.closest('[data-toko-id]');
        if (card && !e.target.closest('[data-no-detail]')) { e.preventDefault(); open(card.dataset.tokoId); }
    });

    window.TokoDetail = { open, close };
})();