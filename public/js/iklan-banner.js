/**
 * public/js/iklan-banner.js
 * Banner iklan di Dashboard User & Mitra. Butuh elemen id="iklanBanner".
 * - Urutan: kursi 4 -> 15 (kursi kosong otomatis terlewati oleh server)
 * - Tiap iklan tampil 3 detik, lalu bergeser ke kiri ke iklan berikutnya, dan berulang (loop)
 * - Hanya 1 iklan: diam di tengah, tidak bergerak
 * - Tidak ada iklan: placeholder bawaan dibiarkan
 */
(function () {
    const INTERVAL = 3000, SLIDE_MS = 600;
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let timer = null;

    function slideHtml(ad) {
        const media = ad.tipe === 'video'
            ? `<video src="${esc(ad.media_url)}" class="w-full h-full object-contain" autoplay muted loop playsinline></video>`
            : `<img src="${esc(ad.media_url)}" class="w-full h-full object-contain" alt="Iklan" draggable="false">`;
        const safe = /^https?:\/\//i.test(ad.url || '');
        return safe
            ? `<a href="${esc(ad.url)}" target="_blank" rel="noopener noreferrer" class="block h-full" style="flex:0 0 100%">${media}</a>`
            : `<div class="h-full" style="flex:0 0 100%">${media}</div>`;
    }

    async function mount(id) {
        const host = document.getElementById(id || 'iklanBanner');
        if (!host) return;
        if (timer) { clearInterval(timer); timer = null; }

        let ads = [];
        try {
            const res = await fetch('/api/iklan/tayang', { headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('access_token') } });
            const json = await res.json();
            ads = (res.ok && json.data) ? json.data.filter(a => a.media_url) : [];
        } catch (e) { return; }
        if (!ads.length) return;   // biarkan placeholder

        const n = ads.length;
        const slides = ads.map(slideHtml).join('') + (n > 1 ? slideHtml(ads[0]) : '');   // klon pertama untuk loop mulus

        host.innerHTML = `
          <div class="relative mx-auto w-full max-w-sm">
            <span class="absolute top-2 left-2 z-10 text-[10px] font-bold bg-black/55 text-white px-2 py-0.5 rounded-full">Iklan</span>
            <div class="overflow-hidden rounded-xl bg-gray-100" style="aspect-ratio:297/210">
              <div id="iklanTrack" class="flex h-full" style="transition:transform ${SLIDE_MS}ms ease">${slides}</div>
            </div>
            ${n > 1 ? `<div class="flex justify-center gap-1.5 mt-2">${ads.map((_, i) => `<span data-dot="${i}" class="h-1.5 rounded-full transition-all ${i === 0 ? 'w-5 bg-blue-600' : 'w-1.5 bg-gray-300'}"></span>`).join('')}</div>` : ''}
          </div>`;

        if (n < 2) return;   // 1 iklan: statis di tengah

        const track = host.querySelector('#iklanTrack');
        let idx = 0;
        const dots = () => host.querySelectorAll('[data-dot]').forEach((d, i) => {
            d.className = 'h-1.5 rounded-full transition-all ' + (i === idx % n ? 'w-5 bg-blue-600' : 'w-1.5 bg-gray-300');
        });
        const step = () => {
            idx++;
            track.style.transition = `transform ${SLIDE_MS}ms ease`;
            track.style.transform = `translateX(-${idx * 100}%)`;
            dots();
            if (idx === n) {   // sudah di klon -> lompat balik ke awal tanpa animasi
                setTimeout(() => {
                    track.style.transition = 'none';
                    idx = 0;
                    track.style.transform = 'translateX(0)';
                    void track.offsetWidth;
                    dots();
                }, SLIDE_MS + 30);
            }
        };
        timer = setInterval(() => { if (!document.hidden && document.body.contains(track)) step(); }, INTERVAL);
    }

    window.IklanBanner = { mount };
    document.addEventListener('DOMContentLoaded', () => mount('iklanBanner'));
})();