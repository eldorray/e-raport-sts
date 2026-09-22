/*
 * Service worker aplikasi guru e-Raport.
 *
 * Aturan penting:
 * - Hanya permintaan GET yang ditangani; simpan/ubah nilai selalu online.
 * - Halaman HTML tidak disimpan di cache supaya nilai yang tampil tidak pernah
 *   basi; saat tidak ada koneksi, pengguna diarahkan ke halaman offline.
 * - Aset statis (hasil build Vite, gambar, font) disajikan dari cache lebih dulu
 *   karena namanya sudah ber-hash.
 */
const VERSI_CACHE = 'eraport-guru-v1';

const ASET_DASAR = [
    '/offline.html',
    '/manifest.webmanifest',
    '/images/pwa-192.png',
    '/images/pwa-512.png',
];

const POLA_ASET_STATIS = [
    /^\/build\//,
    /^\/images\//,
    /^\/storage\//,
    /\.(?:css|js|mjs|png|jpg|jpeg|svg|webp|gif|ico|woff|woff2|ttf)$/,
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(VERSI_CACHE)
            .then((cache) => cache.addAll(ASET_DASAR))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((nama) => Promise.all(nama.filter((kunci) => kunci !== VERSI_CACHE).map((kunci) => caches.delete(kunci))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('message', (event) => {
    if (event.data === 'lewati-tunggu') {
        self.skipWaiting();
    }
});

self.addEventListener('fetch', (event) => {
    const permintaan = event.request;

    if (permintaan.method !== 'GET') {
        return;
    }

    const alamat = new URL(permintaan.url);

    if (alamat.origin !== self.location.origin) {
        return;
    }

    if (permintaan.mode === 'navigate') {
        event.respondWith(
            fetch(permintaan)
                .then((respons) => {
                    if (respons && respons.status === 200) {
                        return respons;
                    }

                    return caches.match('/offline.html');
                })
                .catch(() => caches.match(permintaan).then((tersimpan) => tersimpan || caches.match('/offline.html'))),
        );

        return;
    }

    if (POLA_ASET_STATIS.some((pola) => pola.test(alamat.pathname))) {
        event.respondWith(
            caches.match(permintaan).then(
                (tersimpan) =>
                    tersimpan ||
                    fetch(permintaan).then((respons) => {
                        if (respons && respons.status === 200 && respons.type === 'basic') {
                            const salinan = respons.clone();
                            caches.open(VERSI_CACHE).then((cache) => cache.put(permintaan, salinan));
                        }

                        return respons;
                    }),
            ),
        );
    }
});
