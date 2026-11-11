/**
 * public/js/iklan-pengajuan.js
 * Halaman "Ajukan Iklan" (Mitra Premium). Butuh <div id="iklanContent"></div>.
 * Panggil loadIklan() saat tab Iklan dibuka.
 */
(function () {
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const rp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(n) || 0);
    const tgl = s => { try { return new Date(String(s).slice(0, 10) + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }); } catch (e) { return s; } };
    const auth = () => ({ 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json' });
    const A4 = 297 / 210;

    const STATUS_STYLE = {
        proses_verifikasi: 'bg-amber-100 text-amber-700',
        disetujui: 'bg-blue-100 text-blue-700',
        penayangan: 'bg-green-100 text-green-700',
        berakhir: 'bg-gray-200 text-gray-600',
        ditolak: 'bg-red-100 text-red-600'
    };

    let S = { step: 1, kursi: null, bulan: 1, url: '', file: null, preview: '', seats: [], saya: null, hasil: null, sending: false, ok: false };
    let pollTimer = null;

    const box = () => document.getElementById('iklanContent');

    function lockedHtml() {
        return `
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm min-h-[380px] flex flex-col items-center justify-center text-center p-8">
            <div class="w-20 h-20 rounded-full bg-gray-100 grid place-items-center mb-5"><i class="fa-solid fa-lock text-3xl text-gray-400"></i></div>
            <h3 class="text-lg font-extrabold text-gray-800">Pengajuan Iklan Khusus Mitra Premium</h3>
            <p class="text-sm text-gray-500 mt-2 max-w-sm">Teruntuk akses lebih luas anda dapat bergabung ke Premium</p>
        </div>`;
    }

    // ================= LOAD =================
    async function load(silent) {
        const el = box(); if (!el) return;
        const jenis = Number(localStorage.getItem('jenis_akun_id'));
        if (![4, 5, 6].includes(jenis)) { el.innerHTML = lockedHtml(); return; }
        if (!silent) el.innerHTML = '<div class="bg-white rounded-2xl p-10 text-center border border-gray-100 text-xs text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat...</div>';
        try {
            const [r1, r2] = await Promise.all([
                fetch('/api/mitra/iklan/saya', { headers: auth() }),
                fetch('/api/mitra/iklan/kursi', { headers: auth() })
            ]);
            const j1 = await r1.json().catch(() => ({})), j2 = await r2.json().catch(() => ({}));
            if ((r1.status === 403 && j1.locked) || (r2.status === 403 && j2.locked)) { el.innerHTML = lockedHtml(); return; }
            if (!r1.ok) throw new Error(j1.message || 'Gagal memuat data iklan.');
            S.saya = j1.data; S.seats = (j2.data || []);
            render();
            schedulePoll();
        } catch (e) {
            el.innerHTML = `<div class="bg-white rounded-2xl p-8 text-center border border-red-100 text-xs text-red-500">${esc(e.message)} <button class="underline font-bold" onclick="loadIklan()">Coba lagi</button></div>`;
        }
    }
    function schedulePoll() {
        clearTimeout(pollTimer);
        if (S.saya && S.saya.aktif && S.saya.aktif.status === 'proses_verifikasi') {
            pollTimer = setTimeout(() => { if (box() && box().offsetParent !== null) load(true); else schedulePoll(); }, 3000);
        }
    }

    // ================= RENDER =================
    function render() {
        const el = box();
        el.innerHTML = `
        <div class="space-y-8 max-w-4xl">
            <section class="space-y-3">
                <h3 class="text-lg sm:text-xl font-extrabold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-rectangle-ad text-blue-600"></i> Pengajuan Iklan</h3>
                <div id="iklanAtas">${S.saya.aktif ? statusCard(S.saya.aktif) : wizard()}</div>
            </section>
            <section class="space-y-3">
                <h3 class="text-base sm:text-lg font-extrabold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-gray-500"></i> Riwayat Pengajuan</h3>
                ${riwayat()}
            </section>
        </div>`;
    }

    function mediaTag(m, cls) {
        if (!m.media_url) return '';
        return m.tipe === 'video'
            ? `<video src="${esc(m.media_url)}" class="${cls}" autoplay muted loop playsinline></video>`
            : `<img src="${esc(m.media_url)}" class="${cls}" alt="Iklan">`;
    }

    function statusCard(a) {
        let aksi = '';
        if (a.status === 'proses_verifikasi') {
            aksi = '<div class="flex items-center gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-3"><i class="fa-solid fa-spinner fa-spin"></i> Pengajuan terkirim dan sedang diverifikasi admin. Mohon tunggu sebentar...</div>';
        } else if (a.status === 'disetujui') {
            aksi = `<div class="space-y-2"><div class="text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-xl p-3"><i class="fa-solid fa-circle-check mr-1"></i> Pengajuan Anda telah disetujui admin. Silakan lakukan pembayaran agar iklan mulai tayang.</div>
                <button type="button" onclick="iklanBayar(${a.id})" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-xs font-bold"><i class="fa-solid fa-wallet mr-1.5"></i>Bayar ${rp(a.jumlah)}</button></div>`;
        } else if (a.status === 'penayangan') {
            aksi = `<div class="text-xs text-green-700 bg-green-50 border border-green-200 rounded-xl p-3"><i class="fa-solid fa-tower-broadcast mr-1"></i> Iklan Anda sedang tayang pada ${tgl(a.tanggal_mulai)} s/d <b>${tgl(a.tanggal_berakhir)}</b>. Anda dapat mengajukan iklan baru setelah masa tayang berakhir.</div>`;
        }
        return `
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 space-y-4">
            ${stepper(a.status === 'proses_verifikasi' ? 4 : 5)}
            <div class="grid sm:grid-cols-5 gap-4">
                <div class="sm:col-span-2 rounded-xl overflow-hidden bg-gray-100" style="aspect-ratio:297/210">${mediaTag(a, 'w-full h-full object-cover')}</div>
                <div class="sm:col-span-3 space-y-2 text-sm">
                    <span class="inline-block text-[11px] font-bold px-2.5 py-1 rounded-full ${STATUS_STYLE[a.status] || ''}">Status: ${esc(a.status_label)}</span>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                        <span class="text-gray-400">Urutan</span><span class="font-bold text-gray-800">${esc(a.kursi_nama)}</span>
                        <span class="text-gray-400">Durasi</span><span class="font-bold text-gray-800">${a.bulan} bulan</span>
                        <span class="text-gray-400">Total</span><span class="font-bold text-gray-800">${rp(a.jumlah)}</span>
                        <span class="text-gray-400">URL iklan</span><span class="font-semibold text-blue-600 break-all">${esc(a.url)}</span>
                    </div>
                </div>
            </div>
            ${aksi}
        </div>`;
    }

    function riwayat() {
        const list = (S.saya.riwayat || []);
        if (!list.length) return '<div class="bg-white rounded-2xl border border-gray-100 p-6 text-center text-xs text-gray-400 italic">Belum ada riwayat pengajuan.</div>';
        return '<div class="space-y-3">' + list.map(r => `
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 flex gap-3">
                <div class="w-28 sm:w-36 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100" style="aspect-ratio:297/210">${mediaTag(r, 'w-full h-full object-cover')}</div>
                <div class="min-w-0 flex-1 text-xs space-y-1">
                    <span class="inline-block text-[11px] font-bold px-2 py-0.5 rounded-full ${STATUS_STYLE[r.status] || ''}">${esc(r.status_label)}</span>
                    <div class="font-bold text-gray-800">${esc(r.kursi_nama)} &middot; ${r.bulan} bulan &middot; ${rp(r.jumlah)}</div>
                    <div class="text-gray-400">${tgl(r.tanggal_mulai)} s/d ${tgl(r.tanggal_berakhir)}</div>
                    ${r.alasan ? `<div class="text-red-500">Alasan: ${esc(r.alasan)}</div>` : ''}
                </div>
            </div>`).join('') + '</div>';
    }

    function stepper(cur) {
        const names = ['Isi Data', 'Pilih Urutan', 'Pastikan Data', 'Ajukan'];
        return '<div class="flex items-center gap-1.5 sm:gap-2">' + names.map((n, i) => {
            const k = i + 1, done = cur > k, on = cur === k;
            return `<div class="flex items-center gap-1.5 ${i ? 'flex-1' : ''}">
                ${i ? `<div class="h-0.5 flex-1 ${cur >= k ? 'bg-blue-600' : 'bg-gray-200'}"></div>` : ''}
                <div class="flex items-center gap-1"><span class="w-6 h-6 rounded-full text-[11px] font-bold grid place-items-center ${done || on ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'}">${done ? '<i class="fa-solid fa-check text-[10px]"></i>' : k}</span><span class="hidden sm:inline text-[11px] font-semibold ${on ? 'text-blue-700' : 'text-gray-400'}">${n}</span></div></div>`;
        }).join('') + '</div>';
    }

    // ================= WIZARD =================
    function ketentuan() {
        return `
        <div class="rounded-2xl bg-blue-50 border border-blue-100 p-4 text-xs text-gray-700 space-y-1.5">
            <div class="font-extrabold text-blue-800 mb-1">Ketentuan &amp; cara kerja iklan</div>
            <p>• Media: <b>1 file saja</b> berformat <b>A4 landscape</b> (rasio 297:210, mis. 1754×1240 px). Boleh gambar (JPG/PNG), GIF, atau video (MP4/WebM) <b>maksimal 3 detik</b>. Ukuran maksimal 15 MB.</p>
            <p>• Iklan dipasang <b>bulanan</b>; total biaya = harga urutan × jumlah bulan.</p>
            <p>• Tersedia <b>12 urutan</b>. Urutan 1 paling mahal, urutan terakhir paling murah.</p>
            <p>• Iklan tampil bergantian di dashboard User &amp; Mitra (3 detik per iklan, bergeser ke kiri, lalu berulang). Urutan yang kosong akan dilewati, sehingga iklan di urutan bawah otomatis maju. Jadi jika urutan di atasnya kosong, pemilik urutan terakhir justru bisa tampil lebih dulu.</p>
            <p>• Jika hanya ada <b>1 iklan</b> yang tayang, iklan tersebut diam di tengah (tidak bergerak).</p>
            <p>• Jika urutan yang Anda pilih <b>masih dalam masa tayang</b> pengiklan lain, pengajuan berstatus <b>Ditolak</b>; Anda baru bisa mengajukan kembali setelah masa tayangnya berakhir.</p>
            <p>• Selama iklan Anda diproses atau tayang, Anda tidak dapat mengajukan iklan baru. Setelah <b>Berakhir</b>, iklan masuk ke Riwayat Pengajuan.</p>
            <p>• Alur: Isi data → Pilih urutan → Pastikan data → Ajukan → Verifikasi admin → Bayar → Penayangan.</p>
        </div>`;
    }

    function wizard() {
        const hasilBox = S.hasil ? `<div class="rounded-xl border ${S.hasil.ditolak ? 'border-red-200 bg-red-50 text-red-700' : 'border-red-200 bg-red-50 text-red-700'} p-3 text-xs">
            ${S.hasil.ditolak ? '<b>Status: Ditolak.</b> ' : ''}${esc(S.hasil.message)}</div>` : '';
        let body = '';
        if (S.step === 1) body = step1();
        if (S.step === 2) body = step2();
        if (S.step === 3) body = step3();
        if (S.step === 4) body = step4();
        return `<div class="space-y-4">${ketentuan()}<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5 space-y-4">${stepper(S.step)}${body}${hasilBox}</div></div>`;
    }

    function step1() {
        return `
        <div class="space-y-4">
            <div>
                <label class="text-xs font-bold text-gray-700">URL tujuan iklan (dibuka saat iklan diklik)</label>
                <input id="ikUrl" type="url" value="${esc(S.url)}" placeholder="https://..." class="mt-1 w-full text-sm rounded-xl border border-gray-200 p-3 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="text-xs font-bold text-gray-700">Durasi pemasangan</label>
                <select id="ikBulan" class="mt-1 w-full text-sm rounded-xl border border-gray-200 p-3 bg-white focus:outline-none focus:border-blue-500">
                    ${Array.from({ length: 12 }, (_, i) => `<option value="${i + 1}" ${S.bulan === i + 1 ? 'selected' : ''}>${i + 1} bulan</option>`).join('')}
                </select>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-700">File iklan (1 file: gambar A4 landscape / GIF / video maks 3 detik)</label>
                <input id="ikFile" type="file" accept=".jpg,.jpeg,.png,.gif,.mp4,.webm,image/*,video/mp4,video/webm" class="mt-1 block w-full text-xs file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-bold">
                <div id="ikPrev" class="mt-2">${S.preview ? previewHtml() : ''}</div>
            </div>
            <div id="ikErr" class="hidden text-xs text-red-600"></div>
            <div class="flex justify-end"><button type="button" onclick="iklanNext1()" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Lanjut</button></div>
        </div>`;
    }
    function previewHtml() {
        const isVid = S.file && S.file.type.startsWith('video');
        return `<div class="max-w-xs rounded-xl overflow-hidden bg-gray-100 border border-gray-200" style="aspect-ratio:297/210">${isVid
            ? `<video src="${S.preview}" class="w-full h-full object-cover" autoplay muted loop playsinline></video>`
            : `<img src="${S.preview}" class="w-full h-full object-cover" alt="Pratinjau">`}</div>`;
    }

    function step2() {
        const total = S.kursi ? (S.seats.find(s => s.kursi === S.kursi)?.harga || 0) * S.bulan : 0;
        return `
        <div class="space-y-3">
            <p class="text-xs text-gray-500">Pilih urutan tayang. Harga per bulan, dikali <b>${S.bulan} bulan</b>.</p>
            <div class="grid sm:grid-cols-2 gap-2">
                ${S.seats.map(s => `
                <button type="button" onclick="iklanPilihKursi(${s.kursi})" class="text-left rounded-xl border-2 p-3 transition ${S.kursi === s.kursi ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-blue-300'}">
                    <div class="flex items-center justify-between gap-2"><span class="text-sm font-extrabold text-gray-900">${esc(s.nama)}</span><span class="text-xs font-bold text-blue-700">${rp(s.harga)}/bln</span></div>
                    ${s.sedang_tayang ? `<div class="text-[11px] text-red-500 mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i>Sedang masa tayang sampai ${tgl(s.tayang_sampai)} (pengajuan akan ditolak)</div>` : '<div class="text-[11px] text-green-600 mt-1"><i class="fa-solid fa-circle-check mr-1"></i>Tersedia</div>'}
                </button>`).join('')}
            </div>
            ${S.kursi ? `<div class="text-sm font-bold text-gray-800">Total: <span class="text-blue-700">${rp(total)}</span></div>` : ''}
            <div id="ikErr" class="hidden text-xs text-red-600"></div>
            <div class="flex gap-2 justify-between"><button type="button" onclick="iklanGo(1)" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold">Kembali</button><button type="button" onclick="iklanNext2()" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Lanjut</button></div>
        </div>`;
    }

    function step3() {
        const s = S.seats.find(x => x.kursi === S.kursi) || {};
        return `
        <div class="space-y-4">
            <p class="text-xs text-gray-500">Periksa kembali data pengajuan Anda.</p>
            <div class="grid sm:grid-cols-5 gap-4">
                <div class="sm:col-span-2">${previewHtml()}</div>
                <div class="sm:col-span-3 grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs content-start">
                    <span class="text-gray-400">Urutan</span><span class="font-bold text-gray-800">${esc(s.nama)}</span>
                    <span class="text-gray-400">Durasi</span><span class="font-bold text-gray-800">${S.bulan} bulan</span>
                    <span class="text-gray-400">Harga/bulan</span><span class="font-bold text-gray-800">${rp(s.harga)}</span>
                    <span class="text-gray-400">Total</span><span class="font-extrabold text-blue-700">${rp((s.harga || 0) * S.bulan)}</span>
                    <span class="text-gray-400">URL iklan</span><span class="font-semibold text-blue-600 break-all">${esc(S.url)}</span>
                    <span class="text-gray-400">File</span><span class="font-semibold text-gray-800 break-all">${esc(S.file ? S.file.name : '-')}</span>
                </div>
            </div>
            <label class="flex items-start gap-2 text-xs text-gray-700"><input id="ikCek" type="checkbox" class="mt-0.5"> Saya sudah memastikan seluruh data di atas benar.</label>
            <div id="ikErr" class="hidden text-xs text-red-600"></div>
            <div class="flex gap-2 justify-between"><button type="button" onclick="iklanGo(2)" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold">Kembali</button><button type="button" onclick="iklanNext3()" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Lanjut</button></div>
        </div>`;
    }

    function step4() {
        return `
        <div class="space-y-4 text-center py-2">
            <div class="w-14 h-14 rounded-full bg-blue-100 grid place-items-center mx-auto"><i class="fa-solid fa-paper-plane text-blue-600 text-xl"></i></div>
            <p class="text-sm font-bold text-gray-800">Kirim pengajuan iklan ke admin?</p>
            <p class="text-xs text-gray-500 max-w-md mx-auto">Setelah dikirim, status menjadi <b>Proses Verifikasi Admin</b>. Jika disetujui, tombol pembayaran akan muncul di sini.</p>
            <div class="flex gap-2 justify-center">
                <button type="button" onclick="iklanGo(3)" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold">Kembali</button>
                <button type="button" id="btnKirimIklan" onclick="iklanKirim()" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-xs font-bold">Kirim Pengajuan</button>
            </div>
        </div>`;
    }

    // ================= ACTIONS =================
    const showErr = t => { const e = document.getElementById('ikErr'); if (e) { e.textContent = t; e.classList.remove('hidden'); } };
    const repaint = () => { const a = document.getElementById('iklanAtas'); if (a) a.innerHTML = wizard(); };

    window.iklanGo = n => { S.step = n; S.hasil = null; repaint(); };

    // --- validasi file di browser ---
    async function cekFile(f) {
        const ext = f.name.split('.').pop().toLowerCase();
        if (!['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'].includes(ext)) return 'Format file harus JPG, PNG, GIF, MP4, atau WebM.';
        if (f.size > 15 * 1024 * 1024) return 'Ukuran file maksimal 15 MB.';
        const url = URL.createObjectURL(f);
        const okR = (w, h) => h > 0 && w / h >= 1.371 && w / h <= 1.457;
        const msgR = 'File wajib berformat A4 landscape (rasio 297:210, mis. 1754×1240 px).';
        try {
            if (f.type.startsWith('video')) {
                const v = document.createElement('video'); v.preload = 'metadata'; v.src = url;
                await new Promise((res, rej) => { v.onloadedmetadata = res; v.onerror = () => rej(new Error('Video tidak dapat dibaca.')); });
                if (v.duration > 3.1) return 'Durasi video maksimal 3 detik.';
                if (!okR(v.videoWidth, v.videoHeight)) return msgR;
            } else {
                const im = new Image(); im.src = url;
                await new Promise((res, rej) => { im.onload = res; im.onerror = () => rej(new Error('Gambar tidak dapat dibaca.')); });
                if (!okR(im.naturalWidth, im.naturalHeight)) return msgR;
                if (ext === 'gif') {
                    const b = new Uint8Array(await f.arrayBuffer()); let cs = 0;
                    for (let i = 0; i < b.length - 6; i++) if (b[i] === 0x21 && b[i + 1] === 0xF9 && b[i + 2] === 0x04) cs += b[i + 4] + 256 * b[i + 5];
                    if (cs > 305) return 'Durasi GIF maksimal 3 detik.';
                }
            }
        } catch (e) { return e.message; } finally { URL.revokeObjectURL(url); }
        return null;
    }

    window.iklanNext1 = async function () {
        S.url = document.getElementById('ikUrl').value.trim();
        S.bulan = Number(document.getElementById('ikBulan').value);
        const f = document.getElementById('ikFile').files[0];
        if (!/^https?:\/\/.+/i.test(S.url)) return showErr('URL iklan wajib diisi dan diawali http:// atau https://');
        if (f) {
            const err = await cekFile(f);
            if (err) { S.file = null; S.preview = ''; return showErr(err); }
            S.file = f; S.preview = URL.createObjectURL(f);
        }
        if (!S.file) return showErr('Silakan unggah 1 file iklan.');
        S.step = 2; S.hasil = null; repaint();
    };
    window.iklanPilihKursi = k => { S.kursi = k; repaint(); };
    window.iklanNext2 = () => { if (!S.kursi) return showErr('Silakan pilih urutan iklan.'); S.step = 3; repaint(); };
    window.iklanNext3 = () => { if (!document.getElementById('ikCek').checked) return showErr('Centang pernyataan terlebih dahulu.'); S.step = 4; repaint(); };

    window.iklanKirim = async function () {
        const btn = document.getElementById('btnKirimIklan');
        if (btn) { btn.disabled = true; btn.textContent = 'Mengirim...'; }
        const fd = new FormData();
        fd.append('kursi', S.kursi); fd.append('bulan', S.bulan); fd.append('url', S.url); fd.append('media', S.file);
        try {
            const res = await fetch('/api/mitra/iklan/ajukan', { method: 'POST', headers: auth(), body: fd });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                const msg = (json.errors ? Object.values(json.errors).flat().join(' ') : '') || json.message || 'Pengajuan gagal.';
                S.hasil = { ditolak: !!json.ditolak, message: msg };
                repaint();
                return;
            }
            S = Object.assign(S, { step: 1, kursi: null, bulan: 1, url: '', file: null, preview: '', hasil: null });
            await load(true);
        } catch (e) { S.hasil = { message: e.message }; repaint(); }
    };

    // --- bayar (dialog konfirmasi) ---
    function dialog(html) {
        let d = document.getElementById('iklanDialog');
        if (!d) {
            d = document.createElement('div'); d.id = 'iklanDialog';
            d.className = 'fixed inset-0 bg-black/60 backdrop-blur-sm z-[90] hidden items-center justify-center p-4';
            d.addEventListener('click', e => { if (e.target === d) closeDlg(); });
            document.body.appendChild(d);
        }
        d.innerHTML = `<div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6 text-center">${html}</div>`;
        d.classList.remove('hidden'); d.classList.add('flex');
    }
    function closeDlg() { const d = document.getElementById('iklanDialog'); if (d) { d.classList.add('hidden'); d.classList.remove('flex'); } }
    window.iklanCloseDlg = closeDlg;

    window.iklanBayar = function (id) {
        const a = S.saya.aktif;
        dialog(`
            <div class="w-14 h-14 rounded-full bg-green-100 text-green-600 grid place-items-center mx-auto mb-3"><i class="fa-solid fa-wallet text-xl"></i></div>
            <h4 class="text-sm font-extrabold text-gray-900">Konfirmasi Pembayaran</h4>
            <p class="text-xs text-gray-500 mt-1.5">Apakah anda setuju melakukan pembayaran iklan <b>${esc(a.kursi_nama)}</b> selama <b>${a.bulan} bulan</b> sebesar <b>${rp(a.jumlah)}</b>?</p>
            <div class="flex gap-2 mt-5">
                <button type="button" onclick="iklanCloseDlg()" class="flex-1 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold">Batal</button>
                <button type="button" id="btnBayarIklan" onclick="iklanBayarYa(${id})" class="flex-1 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white text-xs font-bold">Ya, bayar</button>
            </div>`);
    };
    window.iklanBayarYa = async function (id) {
        const b = document.getElementById('btnBayarIklan'); if (b) { b.disabled = true; b.textContent = 'Memproses...'; }
        try {
            const res = await fetch(`/api/mitra/iklan/${id}/bayar`, { method: 'POST', headers: auth() });
            const json = await res.json().catch(() => ({}));
            const ok = res.ok;
            dialog(`
                <div class="w-14 h-14 rounded-full ${ok ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500'} grid place-items-center mx-auto mb-3"><i class="fa-solid ${ok ? 'fa-check' : 'fa-xmark'} text-xl"></i></div>
                <h4 class="text-sm font-extrabold text-gray-900">${ok ? 'Pembayaran berhasil' : (json.ditolak ? 'Status: Ditolak' : 'Pembayaran gagal')}</h4>
                <p class="text-xs text-gray-500 mt-1.5">${esc(json.message || '')}${ok ? ` Tayang sampai <b>${esc(tgl(json.data.tanggal_berakhir))}</b>.` : ''}</p>
                <button type="button" onclick="iklanCloseDlg()" class="mt-5 w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Tutup</button>`);
            load(true);
        } catch (e) { closeDlg(); alert(e.message); }
    };

    // pratinjau file saat dipilih (di langkah 1)
    document.addEventListener('change', async e => {
        if (e.target && e.target.id === 'ikFile') {
            const f = e.target.files[0]; const p = document.getElementById('ikPrev'); if (!f || !p) return;
            const err = await cekFile(f);
            if (err) { S.file = null; S.preview = ''; p.innerHTML = ''; e.target.value = ''; return showErr(err); }
            S.file = f; S.preview = URL.createObjectURL(f); p.innerHTML = previewHtml();
            const er = document.getElementById('ikErr'); if (er) er.classList.add('hidden');
        }
    });

    window.loadIklan = () => load(false);
})();