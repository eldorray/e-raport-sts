<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use App\Services\TahunAjaranBaruService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Wizard "Tahun Ajaran Baru": pindah semester atau naik kelas untuk seluruh sekolah sekaligus.
 *
 * Langkah: pilih sumber & tujuan (create) → pratinjau (preview) → jalankan (store) → hasil.
 */
class TahunAjaranBaruController extends Controller
{
    /** @var string Kunci flash untuk ringkasan hasil wizard */
    private const KUNCI_HASIL = 'hasilTahunAjaranBaru';

    public function __construct(private readonly TahunAjaranBaruService $service) {}

    /**
     * Langkah 1: memilih tahun ajaran sumber dan tujuan.
     */
    public function create(Request $request): View
    {
        $tahunAjarans = TahunAjaran::withCount(['kelas', 'siswas', 'mengajars'])
            ->orderByDesc('tahun_mulai')
            ->orderByDesc('semester')
            ->get();

        // Sumber bawaan: isian sebelumnya (gagal validasi / tombol Kembali), lalu tahun ajaran yang sedang dipilih.
        $cadangan = $tahunAjarans->firstWhere('is_active', true) ?? $tahunAjarans->first();
        $sumberId = (int) ($request->old('sumber_id')
            ?? $request->query('sumber_id')
            ?? $request->session()->get('selected_tahun_ajaran_id')
            ?? $cadangan?->id);

        return view('lembaga.tahun-ajaran-baru', [
            'tahunAjarans' => $tahunAjarans,
            'sumberId' => $sumberId,
            'usulan' => $this->service->usulanSemua($tahunAjarans),
            'berisiData' => $tahunAjarans->mapWithKeys(fn (TahunAjaran $t): array => [$t->id => $this->service->berisiData($t)])->all(),
        ]);
    }

    /**
     * Langkah 2: pratinjau apa yang akan disalin. Belum ada data yang diubah.
     */
    public function preview(Request $request): View
    {
        [$sumber, $tujuan, $isian] = $this->tahunDariRequest($request);
        $mode = $this->service->periksa($sumber, $tujuan);

        return view('lembaga.tahun-ajaran-baru-pratinjau', [
            'sumber' => $sumber,
            'tujuan' => $tujuan,
            'isian' => $isian,
            'mode' => $mode,
            'ringkasan' => $mode === TahunAjaranBaruService::MODE_LANJUT_SEMESTER ? $this->service->ringkasanLanjut($sumber) : null,
            'rencana' => $mode === TahunAjaranBaruService::MODE_KENAIKAN_KELAS ? $this->service->rencanaKenaikan($sumber) : null,
        ]);
    }

    /**
     * Langkah 3: menjalankan wizard dalam satu transaksi, lalu menampilkan ringkasan hasil.
     */
    public function store(Request $request): RedirectResponse
    {
        [$sumber, $tujuan] = $this->tahunDariRequest($request);

        $data = $request->validate([
            'aktifkan' => ['nullable', 'boolean'],
            'kelas_tujuan' => ['nullable', 'array'],
            'kelas_tujuan.*' => ['nullable', 'string', 'max:'.TahunAjaranBaruService::MAX_NAMA_KELAS],
            'pilihan_siswa' => ['nullable', 'array'],
            'pilihan_siswa.*' => ['required', Rule::in(TahunAjaranBaruService::PILIHAN_SISWA)],
        ], [], [
            'kelas_tujuan.*' => __('nama kelas tujuan'),
            'pilihan_siswa.*' => __('pilihan siswa'),
        ]);

        $hasil = $this->service->jalankan(
            $sumber,
            $tujuan,
            $request->boolean('aktifkan'),
            $data['kelas_tujuan'] ?? [],
            $data['pilihan_siswa'] ?? [],
        );

        if ($hasil['diaktifkan']) {
            // Sama seperti mengaktifkan dari menu Tahun Ajaran: tahun yang baru aktif langsung dipilih.
            $request->session()->put([
                'selected_tahun_ajaran_id' => $tujuan->id,
                'selected_semester' => $tujuan->semester,
                'selected_tahun_ajaran_is_active' => true,
            ]);
        }

        return redirect()
            ->route('tahun-ajaran-baru.hasil')
            ->with(self::KUNCI_HASIL, $hasil)
            ->with('status', __('Tahun ajaran :tujuan berhasil disiapkan dari :sumber.', [
                'tujuan' => $hasil['tujuan'],
                'sumber' => $hasil['sumber'],
            ]));
    }

    /**
     * Ringkasan hasil wizard (hanya tersedia tepat setelah wizard dijalankan).
     */
    public function hasil(Request $request): View|RedirectResponse
    {
        $hasil = $request->session()->get(self::KUNCI_HASIL);

        if (! is_array($hasil)) {
            return redirect()->route('tahun-ajaran-baru.create');
        }

        return view('lembaga.tahun-ajaran-baru-hasil', ['hasil' => $hasil]);
    }

    /**
     * Memvalidasi pilihan langkah 1 lalu menyiapkan model sumber dan tujuan.
     *
     * Tujuan baru dikembalikan sebagai model yang belum disimpan; baru dibuat saat wizard dijalankan.
     *
     * @return array{0: TahunAjaran, 1: TahunAjaran, 2: array<string, mixed>}
     */
    private function tahunDariRequest(Request $request): array
    {
        $tahunIni = (int) date('Y');

        $isian = $request->validate([
            'sumber_id' => ['required', 'integer', Rule::exists('tahun_ajarans', 'id')],
            'tujuan_pilihan' => ['required', Rule::in(['ada', 'baru'])],
            'tujuan_id' => ['nullable', 'required_if:tujuan_pilihan,ada', 'integer', Rule::exists('tahun_ajarans', 'id')],
            'tujuan_nama' => ['nullable', 'required_if:tujuan_pilihan,baru', 'string', 'max:50'],
            'tujuan_tahun_mulai' => ['nullable', 'required_if:tujuan_pilihan,baru', 'integer', 'between:'.($tahunIni - 10).','.($tahunIni + 10)],
            'tujuan_tahun_selesai' => ['nullable', 'required_if:tujuan_pilihan,baru', 'integer', 'gte:tujuan_tahun_mulai', 'between:'.($tahunIni - 10).','.($tahunIni + 11)],
            'tujuan_semester' => ['nullable', 'required_if:tujuan_pilihan,baru', Rule::in(['Ganjil', 'Genap'])],
        ], [], [
            'sumber_id' => __('tahun ajaran sumber'),
            'tujuan_id' => __('tahun ajaran tujuan'),
            'tujuan_nama' => __('nama tahun ajaran'),
            'tujuan_tahun_mulai' => __('tahun mulai'),
            'tujuan_tahun_selesai' => __('tahun selesai'),
            'tujuan_semester' => __('semester'),
        ]);

        /** @var TahunAjaran $sumber */
        $sumber = TahunAjaran::findOrFail($isian['sumber_id']);

        if ($isian['tujuan_pilihan'] === 'ada') {
            /** @var TahunAjaran $tujuan */
            $tujuan = TahunAjaran::findOrFail($isian['tujuan_id']);

            return [$sumber, $tujuan, ['sumber_id' => $sumber->id, 'tujuan_pilihan' => 'ada', 'tujuan_id' => $tujuan->id]];
        }

        $tujuan = new TahunAjaran([
            'nama' => trim((string) $isian['tujuan_nama']),
            'tahun_mulai' => (int) $isian['tujuan_tahun_mulai'],
            'tahun_selesai' => (int) $isian['tujuan_tahun_selesai'],
            'semester' => $isian['tujuan_semester'],
            'is_active' => false,
        ]);

        return [$sumber, $tujuan, [
            'sumber_id' => $sumber->id,
            'tujuan_pilihan' => 'baru',
            'tujuan_nama' => $tujuan->nama,
            'tujuan_tahun_mulai' => $tujuan->tahun_mulai,
            'tujuan_tahun_selesai' => $tujuan->tahun_selesai,
            'tujuan_semester' => $tujuan->semester,
        ]];
    }
}
