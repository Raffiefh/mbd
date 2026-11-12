<style>
    .toko-input { width: 100%; padding: .55rem .85rem; border-radius: .75rem; border: 1px solid #e5e7eb; font-size: .75rem; background: #fff; color: #1f2937; transition: border-color .15s, box-shadow .15s; }
    .toko-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .18); }
    .toko-input[readonly] { background: #f3f4f6; color: #6b7280; cursor: not-allowed; }
    .toko-label { display: block; font-size: .75rem; font-weight: 700; color: #374151; margin-bottom: .25rem; }
    .toko-hint { font-size: 10px; color: #9ca3af; margin-top: .25rem; }
</style>
 
<div class="space-y-4">
    <div class="max-w-4xl mx-auto space-y-4">
 
        <!-- Header & Status -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-extrabold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-shop text-blue-600"></i> Data Toko / Tempat Usaha
                </h2>
                <p id="tokoSubtitle" class="text-xs text-gray-500 mt-0.5">Memuat data toko...</p>
            </div>
            <div id="tokoStatusBadge" class="text-xs font-bold px-3 py-1.5 rounded-xl bg-gray-50 text-gray-600 border border-gray-200 inline-flex items-center gap-1.5 self-start sm:self-auto">
                <i class="fa-solid fa-spinner fa-spin"></i> Memuat...
            </div>
        </div>
 
        <!-- Notifikasi -->
        <div id="tokoAlertContainer" class="hidden"></div>
 
        <!-- ============ PANEL: MEMUAT ============ -->
        <div id="tokoLoadingPanel" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center text-xs text-gray-400">
            <i class="fa-solid fa-spinner fa-spin text-lg text-blue-500 mb-2 block"></i> Memuat data toko Anda...
        </div>
 
        <!-- ============ PANEL: GAGAL MEMUAT ============ -->
        <div id="tokoErrorPanel" class="hidden bg-white rounded-2xl border border-red-100 shadow-sm p-8 text-center space-y-3">
            <i class="fa-solid fa-circle-exclamation text-2xl text-red-400"></i>
            <p id="tokoErrorText" class="text-xs text-gray-600">Data toko gagal dimuat.</p>
            <button type="button" onclick="loadTokoData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition">Coba lagi</button>
        </div>
 
        <!-- ============ PANEL: MODE LIHAT ============ -->
        <div id="tokoViewPanel" class="hidden space-y-4">
            <div id="tokoViewContent" class="space-y-4"></div>
            <div class="flex justify-end">
                <button type="button" onclick="startEditToko()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Perbarui Data Toko
                </button>
            </div>
        </div>
 
        <!-- ============ PANEL: FORM (ISI AWAL / PERBARUI) ============ -->
        <form id="tokoForm" onsubmit="handleTokoSubmit(event)" class="hidden space-y-4" novalidate>
 
            <!-- 1. Informasi dasar -->
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 space-y-4">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-store text-sm"></i></span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Informasi dasar</h3>
                        <p class="text-[11px] text-gray-500">Nama, kontak, dan gambaran singkat tempat Anda.</p>
                    </div>
                </div>
 
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="toko-label" for="tokoNama">Nama tempat / toko <span class="text-red-500">*</span></label>
                        <input type="text" id="tokoNama" maxlength="100" placeholder="Contoh: Kopi Senja Cafe" class="toko-input">
                        <p class="toko-hint">Min. 3 karakter, maks. 100 karakter.</p>
                    </div>
                    <div>
                        <label class="toko-label" for="tokoKontak">Kontak (telepon / WhatsApp) <span class="text-red-500">*</span></label>
                        <input type="text" id="tokoKontak" placeholder="Contoh: 081234567890" class="toko-input">
                        <p class="toko-hint">Nomor yang bisa dihubungi pelanggan.</p>
                    </div>
                </div>
 
                <div>
                    <label class="toko-label" for="tokoDeskripsi">Deskripsi tempat <span class="text-red-500">*</span></label>
                    <textarea id="tokoDeskripsi" rows="3" placeholder="Ceritakan suasana, keunggulan, atau fasilitas yang tersedia..." class="toko-input"></textarea>
                </div>
            </section>
 
            <!-- 2. Lokasi -->
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 space-y-4">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-map-location-dot text-sm"></i></span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Lokasi</h3>
                        <p class="text-[11px] text-gray-500">Tempel link Google Maps, koordinat akan terisi otomatis.</p>
                    </div>
                </div>
 
                <div>
                    <label class="toko-label" for="tokoGmapsUrl">Link Google Maps <span id="gmapsRequiredMark" class="text-red-500">*</span></label>
                    <input type="url" id="tokoGmapsUrl" placeholder="https://www.google.com/maps/place/..." oninput="extractCoordinatesFromGmaps(this.value)" class="toko-input">
                    <p class="toko-hint">Gunakan link panjang dari address bar browser. Link pendek (maps.app.goo.gl) tidak memuat koordinat, buka dulu lalu salin link yang muncul.</p>
                </div>
 
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="toko-label" for="tokoLat">Latitude</label>
                        <input type="text" id="tokoLat" readonly placeholder="Terisi otomatis" class="toko-input">
                    </div>
                    <div>
                        <label class="toko-label" for="tokoLng">Longitude</label>
                        <input type="text" id="tokoLng" readonly placeholder="Terisi otomatis" class="toko-input">
                    </div>
                </div>
 
                <div id="coordStatus" class="hidden text-[11px] rounded-xl p-2.5 border flex items-center gap-2"></div>
            </section>
 
            <!-- 3. Jam operasional -->
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 space-y-4">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0"><i class="fa-regular fa-clock text-sm"></i></span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Operasional & harga</h3>
                        <p class="text-[11px] text-gray-500">Jam buka, hari libur rutin, dan harga paling murah.</p>
                    </div>
                </div>
 
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="toko-label" for="tokoJamBuka">Jam buka <span class="text-red-500">*</span></label>
                        <select id="tokoJamBuka" class="toko-input"></select>
                    </div>
                    <div>
                        <label class="toko-label" for="tokoJamTutup">Jam tutup <span class="text-red-500">*</span></label>
                        <select id="tokoJamTutup" class="toko-input"></select>
                    </div>
                    <div>
                        <label class="toko-label" for="tokoHargaMulai">Harga mulai (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" id="tokoHargaMulai" min="0" placeholder="Contoh: 10000" class="toko-input">
                    </div>
                </div>
 
                <div>
                    <p class="toko-label">Hari tutup</p>
                    <div id="hariTutupContainer" class="flex flex-wrap gap-2"></div>
                    <p class="toko-hint">Lewati jika buka setiap hari.</p>
                </div>
            </section>
 
            <!-- 4. Kategori -->
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-tags text-sm"></i></span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Kategori tempat <span class="text-red-500">*</span></h3>
                            <p class="text-[11px] text-gray-500">Pilih kategori yang paling menggambarkan tempat Anda.</p>
                        </div>
                    </div>
                    <span id="kategoriLimitInfo" class="text-[11px] text-blue-600 font-semibold whitespace-nowrap">0 dipilih</span>
                </div>
                <div id="kategoriContainer" class="flex flex-wrap gap-2">
                    <p class="text-xs text-gray-400">Memuat kategori...</p>
                </div>
            </section>
 
            <!-- 5. Foto -->
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0"><i class="fa-regular fa-images text-sm"></i></span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Foto tempat <span class="text-red-500">*</span></h3>
                            <p class="text-[11px] text-gray-500">Foto pertama menjadi foto utama di daftar tempat.</p>
                        </div>
                    </div>
                    <span id="gambarKuotaInfo" class="text-[11px] text-gray-500 font-semibold whitespace-nowrap">0 foto</span>
                </div>
 
                <div id="gambarGrid" class="flex flex-wrap gap-2.5"></div>
 
                <label for="inputGambarToko" class="flex flex-col items-center justify-center gap-1 w-full rounded-xl border-2 border-dashed border-gray-200 hover:border-blue-400 hover:bg-blue-50/40 cursor-pointer py-5 transition">
                    <i class="fa-solid fa-cloud-arrow-up text-xl text-blue-500"></i>
                    <span class="text-xs font-semibold text-gray-700">Klik untuk menambah foto</span>
                    <span class="text-[10px] text-gray-400">JPG atau PNG, maks. 2MB per foto. Bisa pilih beberapa sekaligus.</span>
                </label>
                <input type="file" id="inputGambarToko" multiple accept="image/jpeg,image/png,image/jpg" onchange="handleGambarTokoChange(this)" class="sr-only">
            </section>
 
            <!-- 6. Promo -->
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 space-y-4">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-percent text-sm"></i></span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Promo <span class="text-gray-400 font-normal">(opsional)</span></h3>
                        <p class="text-[11px] text-gray-500">Jika dikosongkan, promo akan tampil sebagai "belum tersedia".</p>
                    </div>
                </div>
 
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="toko-label" for="promoDeskripsi">Deskripsi promo</label>
                        <textarea id="promoDeskripsi" rows="3" placeholder="Contoh: Diskon 20% untuk pelajar setiap Jumat" class="toko-input"></textarea>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="toko-label" for="promoKeadaan">Status promo</label>
                            <select id="promoKeadaan" class="toko-input">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak aktif</option>
                            </select>
                        </div>
                        <div>
                            <label class="toko-label" for="inputGambarPromo">Banner promo</label>
                            <div id="promoBannerCurrent" class="hidden mb-2"></div>
                            <input type="file" id="inputGambarPromo" accept="image/jpeg,image/png,image/jpg" class="w-full text-[11px] text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700 file:font-semibold hover:file:bg-gray-200">
                            <p id="promoBannerHint" class="toko-hint">JPG atau PNG, maks. 2MB.</p>
                        </div>
                    </div>
                </div>
            </section>
 
            <!-- Konfirmasi -->
            <div id="tokoConfirmationBox" class="hidden p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-1">
                <p id="tokoConfirmTitle" class="text-xs font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-question text-amber-600 text-sm"></i> Apakah data toko sudah benar?
                </p>
                <p id="tokoConfirmText" class="text-[11px] text-amber-700">Periksa kembali sebelum menyetujui.</p>
            </div>
 
            <!-- Tombol aksi -->
            <div class="flex flex-wrap justify-end gap-2">
                <button type="button" id="btnTokoCancel" onclick="cancelEditToko()" class="hidden bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-xs font-bold transition">
                    Batal
                </button>
                <button type="button" id="btnTokoRecheck" onclick="resetTokoConfirmation()" class="hidden bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-xs font-bold transition">
                    Periksa lagi
                </button>
                <button type="button" id="btnTokoSubmit" onclick="askTokoConfirmation()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> <span id="btnTokoSubmitLabel">Simpan & kirim data toko</span>
                </button>
                <button type="submit" id="btnTokoFinalSend" class="hidden bg-green-600 hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> <span id="btnTokoFinalLabel">Setuju & simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>
 
<!-- ================= SCRIPT TOKO ================= -->
<script>
const DEFAULT_PROMO_TEXT = 'Mohon maaf informasi promo saat ini dari Cafe belum tersedia';
const HARI_LIST = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
 
let maxAllowedImages = 4;
let maxAllowedCategories = 8;
let minAllowedCategories = 1;

const PESAN_PREMIUM_GAMBAR   = 'Teruntuk akses gambar yang lebih luas anda dapat bergabung ke premium kami ⭐';
const PESAN_PREMIUM_KATEGORI = 'Teruntuk jumlah kategori yang bervariatif anda dapat bergabung ke premium kami ⭐';
 
let tokoData = null;        // data toko tersimpan (null = belum pernah mengisi)
let tokoMode = 'loading';   // loading | error | view | create | edit
let masterKategori = [];
let existingImages = [];    // [{ id, path }] gambar yang sudah tersimpan di server
let deletedImageIds = [];   // id gambar tersimpan yang ditandai untuk dihapus
let newImages = [];         // File[] gambar baru yang belum dikirim
let isSubmittingToko = false;
 
/* ---------- Helper ---------- */
function $t(id) { return document.getElementById(id); }
 
function esc(value) {
    return String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}
 
function storageUrl(path) {
    if (!path) return '';
    if (/^https?:\/\//i.test(path)) return path;
    return "{{ rtrim(config('filesystems.disks.s3.url'), '/') }}/" + String(path).replace(/^\/+/, '');
}
 
function formatRupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
function timeHM(t) { return t ? String(t).slice(0, 5) : ''; }
 
function tokoHeaders() {
    return { 'Authorization': 'Bearer ' + localStorage.getItem('access_token'), 'Accept': 'application/json' };
}
 
function isPremiumAccount() { return [4, 5, 6].includes(Number(localStorage.getItem('jenis_akun_id'))); }
 
function setTokoLock(locked) {
    window.isTokoLocked = locked;
    if (typeof setTokoLockState === 'function') setTokoLockState(locked);
}
 
/* ---------- Inisialisasi ---------- */
async function initTokoModule() {
    if (isPremiumAccount()) {
        maxAllowedImages = 12;
        maxAllowedCategories = 14;
    } else {
        maxAllowedImages = 4;
        maxAllowedCategories = 8;
    }
    minAllowedCategories = 1;
 
    buildTimeOptions();
    renderHariOptions();
    updateGambarCounter();
    updateKategoriCounter();
 
    await loadMasterKategori();
    await loadTokoData();
}
 
function buildTimeOptions() {
    [['tokoJamBuka', 'Pilih jam buka'], ['tokoJamTutup', 'Pilih jam tutup']].forEach(([id, placeholder]) => {
        let html = `<option value="">${placeholder}</option>`;
        for (let i = 0; i < 24; i++) {
            const t = (i < 10 ? '0' : '') + i + ':00';
            html += `<option value="${t}">${t}</option>`;
        }
        $t(id).innerHTML = html;
    });
}
 
function setSelectValue(el, val) {
    if (!val) { el.value = ''; return; }
    if (![...el.options].some(o => o.value === val)) el.add(new Option(val, val));
    el.value = val;
}
 
function renderHariOptions() {
    $t('hariTutupContainer').innerHTML = HARI_LIST.map(h => `
        <label class="cursor-pointer">
            <input type="checkbox" name="hari_tutup[]" value="${h}" class="peer sr-only">
            <span class="inline-flex items-center px-3.5 py-1.5 rounded-full border border-gray-200 bg-white text-xs font-semibold text-gray-600 transition hover:bg-gray-50 peer-checked:bg-red-50 peer-checked:border-red-300 peer-checked:text-red-700 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-400">${h}</span>
        </label>`).join('');
}
 
function loadMasterKategori() {
    const container = $t('kategoriContainer');
    return fetch('/api/kategori', { headers: tokoHeaders() })
        .then(res => res.json())
        .then(res => {
            masterKategori = (res.data || res || []).map(k => ({
                id: k.id_kategori || k.id,
                nama: k.nama_kategori || k.nama
            }));
 
            if (masterKategori.length === 0) {
                container.innerHTML = '<p class="text-xs text-red-500">Daftar kategori belum tersedia di database.</p>';
                return;
            }
 
            container.innerHTML = masterKategori.map(k => `
                <label class="cursor-pointer">
                    <input type="checkbox" name="kategori_id[]" value="${esc(k.id)}" onchange="handleCategorySelection(this)" class="peer sr-only">
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full border border-gray-200 bg-white text-xs font-semibold text-gray-600 transition hover:bg-gray-50 peer-checked:bg-blue-600 peer-checked:border-blue-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-blue-400">${esc(k.nama)}</span>
                </label>`).join('');
        })
        .catch(err => {
            console.error('Gagal load kategori:', err);
            container.innerHTML = '<p class="text-xs text-red-500">Gagal memuat kategori dari server.</p>';
        });
}
 
/* ---------- Ambil data toko ---------- */
async function loadTokoData() {
    setTokoMode('loading');
    try {
        const res = await fetch('/api/mitra/toko', { headers: tokoHeaders() });
 
        if (res.status === 404) {          // Belum pernah mengisi toko -> wajib isi
            tokoData = null;
            setTokoLock(true);
            startCreateToko();
            return;
        }
 
        const json = await res.json();
        if (!res.ok || !json.success || !json.data) {
            throw new Error(json.message || 'Data toko gagal dimuat.');
        }
 
        tokoData = json.data;
        setTokoLock(false);
        renderTokoView(tokoData);
        setTokoMode('view');
    } catch (err) {
        $t('tokoErrorText').innerText = err.message || 'Data toko gagal dimuat.';
        setTokoMode('error');
    }
}
 
/* ---------- Pergantian mode ---------- */
function setTokoMode(mode) {
    tokoMode = mode;
    $t('tokoLoadingPanel').classList.toggle('hidden', mode !== 'loading');
    $t('tokoErrorPanel').classList.toggle('hidden', mode !== 'error');
    $t('tokoViewPanel').classList.toggle('hidden', mode !== 'view');
    $t('tokoForm').classList.toggle('hidden', !(mode === 'create' || mode === 'edit'));
    $t('btnTokoCancel').classList.toggle('hidden', mode !== 'edit');
    $t('gmapsRequiredMark').classList.toggle('hidden', mode === 'edit');
 
    const badge = $t('tokoStatusBadge');
    const base = 'text-xs font-bold px-3 py-1.5 rounded-xl border inline-flex items-center gap-1.5 self-start sm:self-auto ';
    const subtitle = $t('tokoSubtitle');
 
    if (mode === 'create') {
        badge.className = base + 'bg-amber-50 text-amber-700 border-amber-200';
        badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Belum lengkap (wajib diisi)';
        subtitle.innerText = 'Lengkapi data toko sekali di awal untuk membuka seluruh fitur dashboard.';
        $t('btnTokoSubmitLabel').innerText = 'Simpan & kirim data toko';
    } else if (mode === 'edit') {
        badge.className = base + 'bg-blue-50 text-blue-700 border-blue-200';
        badge.innerHTML = '<i class="fa-solid fa-pen"></i> Mode perbarui';
        subtitle.innerText = 'Ubah data yang perlu diperbarui, lalu kirim perubahan.';
        $t('btnTokoSubmitLabel').innerText = 'Kirim perubahan';
    } else if (mode === 'view') {
        badge.className = base + 'bg-green-50 text-green-700 border-green-200';
        badge.innerHTML = '<i class="fa-solid fa-circle-check"></i> Data toko aktif';
        subtitle.innerText = 'Data toko Anda tersimpan. Klik "Perbarui Data Toko" untuk mengubahnya.';
    } else if (mode === 'error') {
        badge.className = base + 'bg-red-50 text-red-700 border-red-200';
        badge.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Gagal memuat';
        subtitle.innerText = 'Terjadi masalah saat mengambil data toko.';
    }
    resetTokoConfirmation();
}
 
function startCreateToko() {
    clearFormFields();
    setTokoMode('create');
}
 
function startEditToko() {
    clearTokoAlert();
    fillTokoForm(tokoData);
    setTokoMode('edit');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
 
function cancelEditToko() {
    clearTokoAlert();
    clearFormFields();
    setTokoMode('view');
}
 
/* ---------- Form: isi & reset ---------- */
function clearFormFields() {
    ['tokoNama', 'tokoKontak', 'tokoDeskripsi', 'tokoGmapsUrl', 'tokoLat', 'tokoLng', 'tokoHargaMulai', 'promoDeskripsi'].forEach(id => $t(id).value = '');
    $t('tokoJamBuka').value = '';
    $t('tokoJamTutup').value = '';
    $t('promoKeadaan').value = '1';
    $t('inputGambarPromo').value = '';
    $t('inputGambarToko').value = '';
    document.querySelectorAll('input[name="hari_tutup[]"], input[name="kategori_id[]"]').forEach(cb => cb.checked = false);
 
    existingImages = [];
    deletedImageIds = [];
    newImages = [];
    $t('promoBannerCurrent').classList.add('hidden');
    $t('promoBannerCurrent').innerHTML = '';
    $t('promoBannerHint').innerText = 'JPG atau PNG, maks. 2MB.';
    setCoordStatus(null);
    renderGambarGrid();
    updateKategoriCounter();
}
 
function fillTokoForm(d) {
    clearFormFields();
 
    $t('tokoNama').value = d.nama_toko || '';
    $t('tokoKontak').value = d.deskripsi_voucher || '';
    $t('tokoDeskripsi').value = d.deskripsi_toko || '';
    $t('tokoLat').value = d.lattitude ?? '';
    $t('tokoLng').value = d.longtitude ?? '';
    $t('tokoHargaMulai').value = d.harga_mulai ?? '';
    setSelectValue($t('tokoJamBuka'), timeHM(d.jam_buka));
    setSelectValue($t('tokoJamTutup'), timeHM(d.jam_tutup));
 
    if (d.lattitude && d.longtitude) setCoordStatus('saved', d.lattitude, d.longtitude);
 
    // Hari tutup
    const hari = (d.hari_tutup || '').split(',').map(s => s.trim()).filter(Boolean);
    document.querySelectorAll('input[name="hari_tutup[]"]').forEach(cb => cb.checked = hari.includes(cb.value));
 
    // Kategori
    const kategoriIds = (d.kategori || []).map(k => String(k.id_kategori ?? k.id));
    document.querySelectorAll('input[name="kategori_id[]"]').forEach(cb => cb.checked = kategoriIds.includes(String(cb.value)));
    updateKategoriCounter();
 
    // Gambar tersimpan (Laravel mengubah relasi gambarToko menjadi gambar_toko di JSON)
    const imgs = d.gambar_toko || d.gambarToko || [];
    existingImages = imgs.map(g => ({ id: g.id_gambar_toko, path: g.path_gambar }));
    renderGambarGrid();
 
    // Promo
    const promo = d.promo;
    if (promo) {
        $t('promoDeskripsi').value = promo.deskripsi_promo === DEFAULT_PROMO_TEXT ? '' : (promo.deskripsi_promo || '');
        $t('promoKeadaan').value = String(promo.keadaan_promo ?? 1);
        if (promo.path_promo) {
            $t('promoBannerCurrent').innerHTML = `<img src="${esc(storageUrl(promo.path_promo))}" alt="Banner promo saat ini" class="h-20 rounded-lg border border-gray-200 object-cover">`;
            $t('promoBannerCurrent').classList.remove('hidden');
            $t('promoBannerHint').innerText = 'Banner saat ini. Pilih file baru untuk menggantinya.';
        }
    }
}
 
/* ---------- Koordinat Google Maps ---------- */
function setCoordStatus(type, lat, lng) {
    const box = $t('coordStatus');
    if (!type) { box.classList.add('hidden'); box.innerHTML = ''; return; }
 
    const link = `https://www.google.com/maps?q=${encodeURIComponent(lat)},${encodeURIComponent(lng)}`;
    box.classList.remove('hidden');
    if (type === 'extracted') {
        box.className = 'text-[11px] rounded-xl p-2.5 border flex items-center gap-2 bg-green-50 border-green-200 text-green-700';
        box.innerHTML = `<i class="fa-solid fa-circle-check"></i><span>Koordinat berhasil diambil dari link.</span> <a href="${link}" target="_blank" rel="noopener" class="underline font-semibold ml-auto">Cek di peta</a>`;
    } else {
        box.className = 'text-[11px] rounded-xl p-2.5 border flex items-center gap-2 bg-blue-50 border-blue-200 text-blue-700';
        box.innerHTML = `<i class="fa-solid fa-location-dot"></i><span>Koordinat tersimpan. Tempel link baru hanya jika lokasi berubah.</span> <a href="${link}" target="_blank" rel="noopener" class="underline font-semibold ml-auto">Cek di peta</a>`;
    }
}
 
function extractCoordinatesFromGmaps(url) {
    const patterns = [
        /@(-?\d+\.\d+),(-?\d+\.\d+)/,
        /!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/,
        /[?&](?:q|ll)=(-?\d+\.\d+),(-?\d+\.\d+)/
    ];
    for (const re of patterns) {
        const m = url.match(re);
        if (m) {
            $t('tokoLat').value = m[1];
            $t('tokoLng').value = m[2];
            setCoordStatus('extracted', m[1], m[2]);
            return;
        }
    }
    // Tidak ketemu: di mode buat kosongkan koordinat, di mode perbarui pertahankan koordinat lama
    if (tokoMode === 'create') {
        $t('tokoLat').value = '';
        $t('tokoLng').value = '';
        setCoordStatus(null);
    }
}
 
/* ---------- Kategori ---------- */
function updateKategoriCounter() {
    const n = document.querySelectorAll('input[name="kategori_id[]"]:checked').length;
    $t('kategoriLimitInfo').innerText = `${n} dipilih`;
}
 
function handleCategorySelection(checkbox) {
    const n = document.querySelectorAll('input[name="kategori_id[]"]:checked').length;
    if (n > maxAllowedCategories) {
        checkbox.checked = false;
        if (isPremiumAccount()) showTokoAlert('error', 'Jumlah kategori sudah mencapai batas untuk akun Anda.');
        else showPremiumUpsell(PESAN_PREMIUM_KATEGORI);
    } else {
        clearTokoAlert();
    }
    updateKategoriCounter();
}
 
/* ---------- Gambar: kelola tersimpan + baru ---------- */
function activeImageCount() {
    return existingImages.filter(g => !deletedImageIds.includes(g.id)).length + newImages.length;
}
 
function showPremiumUpsell(text) {
    showUlasanDialog({
        icon: 'fa-crown', iconClass: 'bg-amber-50 text-amber-500',
        title: 'Fitur Premium',
        text: esc(text),
        buttons:
            '<button type="button" onclick="closeUlasanDialog()" class="flex-1 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold">Nanti saja</button>' +
            '<button type="button" onclick="closeUlasanDialog(); if (typeof openPremiumModal === \'function\') openPremiumModal();" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white text-xs font-bold">Gabung Premium</button>'
    });
}

function showImageLimit() {
    if (isPremiumAccount()) showTokoAlert('error', 'Jumlah foto sudah mencapai batas untuk akun Anda.');
    else showPremiumUpsell(PESAN_PREMIUM_GAMBAR);
}
 
function updateGambarCounter() {
    $t('gambarKuotaInfo').innerText = `${activeImageCount()} foto`;
}
 
function handleGambarTokoChange(input) {
    const picked = Array.from(input.files);
    input.value = '';
    clearTokoAlert();
 
    for (const f of picked) {
        if (!/^image\/(jpeg|png|jpg)$/.test(f.type)) {
            showTokoAlert('error', `File "${f.name}" bukan JPG atau PNG.`);
            continue;
        }
        if (f.size > 2 * 1024 * 1024) {
            showTokoAlert('error', `File "${f.name}" melebihi ukuran maksimal 2MB.`);
            continue;
        }
        if (activeImageCount() >= maxAllowedImages) {
            showImageLimit();
            break;
        }
        newImages.push(f);
    }
    renderGambarGrid();
}
 
function removeNewImage(index) {
    newImages.splice(index, 1);
    renderGambarGrid();
}
 
function toggleDeleteExisting(id) {
    const i = deletedImageIds.indexOf(id);
    if (i >= 0) {
        if (activeImageCount() >= maxAllowedImages) {
            showImageLimit();
            return;
        }
        deletedImageIds.splice(i, 1);
    } else {
        deletedImageIds.push(id);
    }
    clearTokoAlert();
    renderGambarGrid();
}
 
function renderGambarGrid() {
    const grid = $t('gambarGrid');
    let primaryAssigned = false;
    let html = '';
 
    existingImages.forEach(g => {
        const deleted = deletedImageIds.includes(g.id);
        const isPrimary = !deleted && !primaryAssigned;
        if (isPrimary) primaryAssigned = true;
 
        html += `
        <div class="relative w-24 h-24 rounded-xl overflow-hidden border ${deleted ? 'border-red-200' : 'border-gray-200'} bg-gray-100">
            <img src="${esc(storageUrl(g.path))}" alt="Foto toko" class="w-full h-full object-cover ${deleted ? 'opacity-30 grayscale' : ''}">
            ${isPrimary ? '<span class="absolute bottom-0 inset-x-0 bg-blue-600/90 text-white text-[9px] font-bold text-center py-0.5">Utama</span>' : ''}
            ${deleted ? '<span class="absolute inset-x-0 top-1/2 -translate-y-1/2 text-center text-[10px] font-bold text-red-600">Akan dihapus</span>' : ''}
            <button type="button" onclick="toggleDeleteExisting(${Number(g.id)})" title="${deleted ? 'Batalkan hapus' : 'Hapus foto'}" class="absolute top-1 right-1 w-6 h-6 rounded-full ${deleted ? 'bg-gray-700 hover:bg-gray-800' : 'bg-black/60 hover:bg-red-600'} text-white flex items-center justify-center transition">
                <i class="fa-solid ${deleted ? 'fa-rotate-left' : 'fa-xmark'} text-[10px]"></i>
            </button>
        </div>`;
    });
 
    newImages.forEach((file, i) => {
        const isPrimary = !primaryAssigned;
        if (isPrimary) primaryAssigned = true;
        html += `
        <div class="relative w-24 h-24 rounded-xl overflow-hidden border border-blue-200 bg-gray-100">
            <img src="${URL.createObjectURL(file)}" alt="Foto baru" class="w-full h-full object-cover">
            <span class="absolute top-1 left-1 bg-green-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">Baru</span>
            ${isPrimary ? '<span class="absolute bottom-0 inset-x-0 bg-blue-600/90 text-white text-[9px] font-bold text-center py-0.5">Utama</span>' : ''}
            <button type="button" onclick="removeNewImage(${i})" title="Hapus foto" class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/60 hover:bg-red-600 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-[10px]"></i>
            </button>
        </div>`;
    });
 
    grid.innerHTML = html || '<p class="text-xs text-gray-400 italic">Belum ada foto dipilih.</p>';
    updateGambarCounter();
}
 
/* ---------- Konfirmasi & kirim ---------- */
function resetTokoConfirmation() {
    $t('tokoConfirmationBox').classList.add('hidden');
    $t('btnTokoFinalSend').classList.add('hidden');
    $t('btnTokoRecheck').classList.add('hidden');
    $t('btnTokoSubmit').classList.remove('hidden');
}
 
function askTokoConfirmation() {
    clearTokoAlert();
 
    const nama = $t('tokoNama').value.trim();
    const kontak = $t('tokoKontak').value.trim();
    const deskripsi = $t('tokoDeskripsi').value.trim();
    const lat = $t('tokoLat').value;
    const lng = $t('tokoLng').value;
    const harga = $t('tokoHargaMulai').value;
    const kategoriDipilih = document.querySelectorAll('input[name="kategori_id[]"]:checked').length;
 
    let error = null;
    if (nama.length < 3) error = 'Nama tempat minimal 3 karakter.';
    else if (!kontak) error = 'Kontak wajib diisi.';
    else if (!deskripsi) error = 'Deskripsi tempat wajib diisi.';
    else if (!lat || !lng) error = 'Tempel link Google Maps yang valid sampai Latitude dan Longitude terisi.';
    else if (!$t('tokoJamBuka').value || !$t('tokoJamTutup').value) error = 'Jam buka dan jam tutup wajib dipilih.';
    else if (harga === '' || Number(harga) < 0) error = 'Harga mulai wajib diisi (minimal 0).';
    else if (kategoriDipilih < minAllowedCategories) error = `Pilih minimal ${minAllowedCategories} kategori tempat.`;
    else if (activeImageCount() < 1) error = 'Toko wajib memiliki minimal 1 foto.';
 
    if (error) {
        showTokoAlert('error', error);
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }
 
    const isEdit = tokoMode === 'edit';
    $t('tokoConfirmTitle').innerHTML = '<i class="fa-solid fa-circle-question text-amber-600 text-sm"></i> ' +
        (isEdit ? 'Apakah Anda menyetujui perubahan data toko ini?' : 'Apakah data toko sudah benar dan siap dikirim?');
    $t('tokoConfirmText').innerText = isEdit
        ? 'Setelah disetujui, perubahan akan disimpan dan data kembali ke mode lihat.'
        : 'Setelah disetujui, data tersimpan dan seluruh fitur dashboard mitra terbuka.';
    $t('btnTokoFinalLabel').innerText = 'Setuju & simpan';
 
    $t('tokoConfirmationBox').classList.remove('hidden');
    $t('btnTokoSubmit').classList.add('hidden');
    $t('btnTokoRecheck').classList.remove('hidden');
    $t('btnTokoFinalSend').classList.remove('hidden');
}
 
function setSubmittingToko(state) {
    isSubmittingToko = state;
    const btn = $t('btnTokoFinalSend');
    btn.disabled = state;
    $t('btnTokoFinalLabel').innerText = state ? 'Menyimpan...' : 'Setuju & simpan';
}
 
function handleTokoSubmit(event) {
    event.preventDefault();
    if (isSubmittingToko) return;
    setSubmittingToko(true);
 
    const isUpdate = tokoMode === 'edit';
    const formData = new FormData();
    formData.append('nama_toko', $t('tokoNama').value.trim());
    formData.append('deskripsi_toko', $t('tokoDeskripsi').value.trim());
    formData.append('garis_lintang', $t('tokoLat').value);
    formData.append('garis_bujur', $t('tokoLng').value);
    formData.append('deskripsi_voucher', $t('tokoKontak').value.trim());
    formData.append('jam_buka', $t('tokoJamBuka').value);
    formData.append('jam_tutup', $t('tokoJamTutup').value);
    formData.append('harga_mulai', $t('tokoHargaMulai').value);
 
    document.querySelectorAll('input[name="hari_tutup[]"]:checked').forEach(cb => formData.append('hari_tutup[]', cb.value));
    document.querySelectorAll('input[name="kategori_id[]"]:checked').forEach(cb => formData.append('kategori_id[]', cb.value));
 
    newImages.forEach(file => formData.append('gambar_toko[]', file));
    if (isUpdate) deletedImageIds.forEach(id => formData.append('hapus_gambar_id[]', id));
 
    formData.append('deskripsi_promo', $t('promoDeskripsi').value.trim() || DEFAULT_PROMO_TEXT);
    formData.append('keadaan_promo', $t('promoKeadaan').value);
    const promoFile = $t('inputGambarPromo').files[0];
    if (promoFile) formData.append('jalur_promo', promoFile);
 
    fetch(isUpdate ? '/api/mitra/toko/update' : '/api/mitra/toko', {
        method: 'POST',
        headers: tokoHeaders(),
        body: formData
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            let msg = data.message || 'Gagal menyimpan data toko.';
            if (data.errors) msg = data.errors[Object.keys(data.errors)[0]][0];
            throw new Error(msg);
        }
        return data;
    })
    .then(data => {
        // Tetap di halaman Data Toko, pindah ke mode lihat (tanpa reload / pindah ke dashboard)
        tokoData = data.data;
        clearFormFields();
        setTokoLock(false);
        renderTokoView(tokoData);
        setTokoMode('view');
        showTokoAlert('success', data.message || 'Data toko berhasil disimpan!');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    })
    .catch(err => {
        showTokoAlert('error', err.message);
        resetTokoConfirmation();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    })
    .finally(() => setSubmittingToko(false));
}
 
/* ---------- Tampilan mode lihat ---------- */
function infoRow(icon, label, valueHtml) {
    return `
    <div class="flex items-start gap-3">
        <span class="w-8 h-8 rounded-xl bg-gray-50 text-gray-500 flex items-center justify-center flex-shrink-0"><i class="fa-solid ${icon} text-xs"></i></span>
        <div class="min-w-0">
            <p class="text-[11px] text-gray-500">${label}</p>
            <div class="text-xs font-semibold text-gray-800 break-words">${valueHtml}</div>
        </div>
    </div>`;
}
 
function renderTokoView(d) {
    const imgs = d.gambar_toko || d.gambarToko || [];
    const hari = (d.hari_tutup || '').split(',').map(s => s.trim()).filter(Boolean);
    const kategori = d.kategori || [];
    const promo = d.promo;
    const mapsLink = `https://www.google.com/maps?q=${encodeURIComponent(d.lattitude)},${encodeURIComponent(d.longtitude)}`;
 
    const gallery = imgs.length
        ? `<div class="grid grid-cols-2 sm:grid-cols-4 gap-2">` + imgs.map((g, i) => `
            <div class="relative overflow-hidden rounded-xl bg-gray-100 border border-gray-100 ${i === 0 ? 'col-span-2 sm:row-span-2 aspect-[4/3] sm:aspect-auto sm:min-h-[16rem]' : 'aspect-[4/3]'}">
                <img src="${esc(storageUrl(g.path_gambar))}" alt="Foto ${esc(d.nama_toko)}" class="absolute inset-0 w-full h-full object-cover">
                ${i === 0 ? '<span class="absolute bottom-2 left-2 bg-blue-600/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-lg">Foto utama</span>' : ''}
            </div>`).join('') + `</div>`
        : `<div class="rounded-xl border border-dashed border-gray-200 py-10 text-center text-xs text-gray-400 italic">Belum ada foto.</div>`;
 
    const hariHtml = hari.length
        ? `<div class="flex flex-wrap gap-1.5 mt-0.5">${hari.map(h => `<span class="px-2.5 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200 text-[11px] font-semibold">${esc(h)}</span>`).join('')}</div>`
        : `<span class="text-gray-500 font-medium">Buka setiap hari</span>`;
 
    const kategoriHtml = kategori.length
        ? `<div class="flex flex-wrap gap-1.5 mt-0.5">${kategori.map(k => `<span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold">${esc(k.nama_kategori || k.nama)}</span>`).join('')}</div>`
        : `<span class="text-gray-400 font-medium italic">Belum ada kategori</span>`;
 
    const promoIsDefault = !promo || !promo.deskripsi_promo || promo.deskripsi_promo === DEFAULT_PROMO_TEXT;
    const promoAktif = promo && Number(promo.keadaan_promo) === 1;
 
    $t('tokoViewContent').innerHTML = `
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 space-y-3">
        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fa-regular fa-images text-blue-600"></i> Foto tempat <span class="text-[11px] font-medium text-gray-400">${imgs.length} foto</span></h3>
        ${gallery}
    </section>
 
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 space-y-4">
        <div>
            <h3 class="text-lg font-extrabold text-gray-900 break-words">${esc(d.nama_toko)}</h3>
            <p class="text-xs text-gray-600 mt-1 whitespace-pre-line break-words">${esc(d.deskripsi_toko)}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 pt-4 border-t border-gray-100">
            ${infoRow('fa-phone', 'Kontak', esc(d.deskripsi_voucher || '-'))}
            ${infoRow('fa-coins', 'Harga mulai', esc(formatRupiah(d.harga_mulai)))}
            ${infoRow('fa-clock', 'Jam operasional', `${esc(timeHM(d.jam_buka))} - ${esc(timeHM(d.jam_tutup))}`)}
            ${infoRow('fa-location-dot', 'Lokasi', `${esc(d.lattitude)}, ${esc(d.longtitude)} <a href="${mapsLink}" target="_blank" rel="noopener" class="text-blue-600 hover:underline font-semibold ml-1">Buka di peta</a>`)}
            ${infoRow('fa-calendar-xmark', 'Hari tutup', hariHtml)}
            ${infoRow('fa-tags', 'Kategori', kategoriHtml)}
        </div>
    </section>
 
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fa-solid fa-percent text-amber-500"></i> Promo</h3>
            ${promo ? `<span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border ${promoAktif ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-50 text-gray-500 border-gray-200'}">${promoAktif ? 'Aktif' : 'Tidak aktif'}</span>` : ''}
        </div>
        ${promo && promo.path_promo ? `<img src="${esc(storageUrl(promo.path_promo))}" alt="Banner promo" class="w-full max-h-48 object-cover rounded-xl border border-gray-100">` : ''}
        <p class="text-xs ${promoIsDefault ? 'text-gray-400 italic' : 'text-gray-700'} whitespace-pre-line break-words">${esc(promoIsDefault ? 'Informasi promo belum tersedia.' : promo.deskripsi_promo)}</p>
    </section>

    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 space-y-3">
        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fa-solid fa-comments text-blue-600"></i> Rating &amp; ulasan untuk toko Anda <span id="tokoUlasanCount" class="text-[11px] font-medium text-gray-400"></span></h3>
        <div id="tokoUlasanList" class="space-y-3"><p class="text-xs text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Memuat ulasan...</p></div>
    </section>`;
    loadTokoUlasan();
}
 
/* ---------- Ulasan untuk toko milik mitra (lihat semua; hapus khusus Premium) ---------- */
let tokoUlasanData = [];

function ulasanStars(n) {
    let h = '';
    for (let i = 1; i <= 5; i++) h += `<i class="fa-solid fa-star text-[11px] ${i <= n ? 'text-amber-400' : 'text-gray-300'}"></i>`;
    return h;
}

async function loadTokoUlasan() {
    const list = $t('tokoUlasanList');
    if (!list) return;
    try {
        const res = await fetch('/api/mitra/toko/ulasan', { headers: tokoHeaders() });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(json.message || 'Gagal memuat ulasan.');
        tokoUlasanData = json.data || [];
        renderTokoUlasan();
    } catch (e) {
        list.innerHTML = `<p class="text-xs text-red-500">${esc(e.message)} <button type="button" onclick="loadTokoUlasan()" class="underline font-bold">Coba lagi</button></p>`;
    }
}

function renderTokoUlasan() {
    const list = $t('tokoUlasanList');
    const count = $t('tokoUlasanCount');
    if (!list) return;
    if (count) count.innerText = `(${tokoUlasanData.length})`;
    if (!tokoUlasanData.length) {
        list.innerHTML = '<p class="text-xs text-gray-400 italic">Belum ada rating atau ulasan untuk toko Anda.</p>';
        return;
    }
    list.innerHTML = tokoUlasanData.map(u => {
        let tgl = '';
        try { tgl = new Date(String(u.created_at).replace(' ', 'T')).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }); } catch (e) {}
        return `
        <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-xs font-bold text-gray-800 break-words">${esc(u.nama)}</div>
                    <div class="flex items-center gap-2 mt-0.5">${ulasanStars(Number(u.rating))}<span class="text-[11px] text-gray-400">${esc(tgl)}</span></div>
                </div>
                <button type="button" onclick="askDeleteUlasan(${Number(u.id_rating_ulasan)})" class="text-[11px] font-bold text-red-500 hover:text-red-700 flex items-center gap-1 flex-shrink-0"><i class="fa-solid fa-trash"></i> Hapus</button>
            </div>
            ${u.ulasan ? `<p class="text-xs text-gray-600 mt-1.5 whitespace-pre-line break-words">${esc(u.ulasan)}</p>` : ''}
        </div>`;
    }).join('');
}

/* Dialog (satu elemen dipakai untuk pesan terkunci & konfirmasi hapus) */
function showUlasanDialog({ icon, iconClass, title, text, buttons }) {
    let el = document.getElementById('ulasanDialog');
    if (!el) {
        el = document.createElement('div');
        el.id = 'ulasanDialog';
        el.className = 'fixed inset-0 bg-black/60 backdrop-blur-sm z-[80] hidden items-center justify-center p-4';
        document.body.appendChild(el);
        el.addEventListener('click', e => { if (e.target === el) closeUlasanDialog(); });
    }
    el.innerHTML = `
        <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6 text-center">
            <div class="w-14 h-14 rounded-full ${iconClass} grid place-items-center mx-auto mb-3"><i class="fa-solid ${icon} text-xl"></i></div>
            <h4 class="text-sm font-extrabold text-gray-900">${title}</h4>
            <p class="text-xs text-gray-500 mt-1.5">${text}</p>
            <div class="flex gap-2 mt-5">${buttons}</div>
        </div>`;
    el.classList.remove('hidden'); el.classList.add('flex');
}
function closeUlasanDialog() {
    const el = document.getElementById('ulasanDialog');
    if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
}

function askDeleteUlasan(id) {
    const cancelBtn = '<button type="button" onclick="closeUlasanDialog()" class="flex-1 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold">Batal</button>';

    if (!isPremiumAccount()) {   // Mitra biasa (ID 3): dicegah, hanya bisa batal
        showUlasanDialog({
            icon: 'fa-lock', iconClass: 'bg-gray-100 text-gray-400',
            title: 'Tidak dapat dilakukan',
            text: 'Mohon maaf tidak bisa dilakukan. Untuk akses lebih luas anda dapat bergabung ke Premium.',
            buttons: cancelBtn
        });
        return;
    }
    showUlasanDialog({
        icon: 'fa-trash', iconClass: 'bg-red-50 text-red-500',
        title: 'Hapus rating dan ulasan?',
        text: 'Apakah anda yakin untuk menghapus rating dan atau ulasan ini?',
        buttons: cancelBtn + `<button type="button" id="btnConfirmDelUlasan" onclick="confirmDeleteUlasan(${Number(id)})" class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold">Ya, hapus</button>`
    });
}

async function confirmDeleteUlasan(id) {
    const btn = document.getElementById('btnConfirmDelUlasan');
    if (btn) { btn.disabled = true; btn.innerText = 'Menghapus...'; }
    try {
        const res = await fetch('/api/mitra/toko/ulasan/' + id, { method: 'DELETE', headers: tokoHeaders() });
        const json = await res.json().catch(() => ({}));
        if (res.status === 403 && json.locked) {   // dicegah juga oleh server
            showUlasanDialog({
                icon: 'fa-lock', iconClass: 'bg-gray-100 text-gray-400', title: 'Tidak dapat dilakukan',
                text: esc(json.message),
                buttons: '<button type="button" onclick="closeUlasanDialog()" class="flex-1 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold">Batal</button>'
            });
            return;
        }
        if (!res.ok) throw new Error(json.message || 'Gagal menghapus ulasan.');
        closeUlasanDialog();
        tokoUlasanData = tokoUlasanData.filter(u => Number(u.id_rating_ulasan) !== Number(id));
        renderTokoUlasan();
        showTokoAlert('success', json.message || 'Rating dan ulasan berhasil dihapus.');
    } catch (e) {
        closeUlasanDialog();
        showTokoAlert('error', e.message);
    }
}

/* ---------- Notifikasi ---------- */
function showTokoAlert(type, message) {
    const box = $t('tokoAlertContainer');
    box.classList.remove('hidden');
    const ok = type === 'success';
    box.innerHTML = `<div class="p-3 rounded-xl ${ok ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'} border text-xs font-semibold flex items-center gap-2"><i class="fa-solid ${ok ? 'fa-circle-check text-green-500' : 'fa-circle-exclamation text-red-500'} text-base flex-shrink-0"></i><span>${esc(message)}</span></div>`;
}
 
function clearTokoAlert() {
    const box = $t('tokoAlertContainer');
    box.classList.add('hidden');
    box.innerHTML = '';
}
</script>