@props([
    'jumlah' => 0,
    'label' => null,
    'class' => 'inline-flex cursor-not-allowed items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-400 dark:bg-gray-800 dark:text-gray-500',
])

{{--
    Tombol hapus yang dinonaktifkan karena data induk masih menyimpan nilai.
    Dipakai di halaman guru, kelas, mata pelajaran, ekskul, dan tahun ajaran.
    Proteksi sebenarnya tetap ada di PenghapusanDataService (server-side).
--}}
<span class="{{ $class }}"
    title="{{ __('Tidak bisa dihapus: masih terhubung ke :jumlah nilai. Hapus nilai tersebut lebih dulu.', ['jumlah' => number_format($jumlah, 0, ',', '.')]) }}">
    <i class="fas fa-lock text-[11px]"></i> {{ $label ?? __('Hapus') }}
</span>
