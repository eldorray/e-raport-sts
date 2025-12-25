<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller untuk mengelola data kelas.
 *
 * Menangani operasi CRUD kelas dan sinkronisasi wali kelas.
 */
class KelasController extends Controller
{
    /** @var int Panjang maksimum nama kelas */
    private const MAX_CLASS_NAME_LENGTH = 50;

    /** @var int Panjang maksimum tingkat */
    private const MAX_TINGKAT_LENGTH = 20;

    /** @var int Panjang maksimum jurusan */
    private const MAX_JURUSAN_LENGTH = 50;

    /**
     * Menampilkan daftar semua kelas.
     *
     * @return View|RedirectResponse Halaman index kelas
     */
    public function index(): View|RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id') ?? TahunAjaran::where('is_active', true)->value('id');

        $kelasList = Kelas::with(['guru'])
            ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->withCount('siswas')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $gurus = Guru::orderBy('nama')->get();

        return view('kelas.index', [
            'kelasList' => $kelasList,
            'gurus' => $gurus,
        ]);
    }

    /**
     * Menyimpan kelas baru ke database.
     *
     * @param  Request  $request  HTTP request dengan data kelas
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function store(Request $request): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');

        if (! $tahunId) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        $data = $this->validatedData($request);
        $data['tahun_ajaran_id'] = $tahunId;

        $kelas = Kelas::create($data);

        $this->syncGuruWaliKelas($kelas);

        return back()->with('status', __('Kelas berhasil dibuat.'));
    }

    /**
     * Memperbarui data kelas yang sudah ada.
     *
     * @param  Request  $request  HTTP request dengan data yang diperbarui
     * @param  Kelas    $kelas    Instance kelas dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $this->validatedData($request, $kelas->id);

        $previousGuruId = $kelas->guru_id;

        $kelas->fill($data);
        $kelas->save();

        $this->clearPreviousWaliKelas($previousGuruId, $kelas->guru_id);
        $this->syncGuruWaliKelas($kelas);

        return back()->with('status', __('Kelas berhasil diperbarui.'));
    }

    /**
     * Menghapus kelas dari database.
     *
     * Siswa yang ada di kelas akan di-unassign.
     *
     * @param  Kelas  $kelas  Instance kelas dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroy(Kelas $kelas): RedirectResponse
    {
        Siswa::where('kelas_id', $kelas->id)->update(['kelas_id' => null]);

        if ($kelas->guru_id) {
            Guru::whereKey($kelas->guru_id)->update(['wali_kelas' => null]);
        }

        $kelas->delete();

        return back()->with('status', __('Kelas dihapus.'));
    }

    /**
     * Validasi data request untuk create/update kelas.
     *
     * @param  Request   $request   HTTP request dengan data kelas
     * @param  int|null  $ignoreId  ID kelas yang diabaikan untuk validasi unique
     * @return array Data yang sudah divalidasi
     */
    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $tahunId = session('selected_tahun_ajaran_id');

        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:' . self::MAX_CLASS_NAME_LENGTH,
                Rule::unique('kelas', 'nama')
                    ->where(fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                    ->ignore($ignoreId),
            ],
            'tingkat' => ['required', 'string', 'max:' . self::MAX_TINGKAT_LENGTH],
            'jurusan' => ['nullable', 'string', 'max:' . self::MAX_JURUSAN_LENGTH],
            'jenis' => ['nullable', 'string', 'max:' . self::MAX_JURUSAN_LENGTH],
            'guru_id' => ['nullable', Rule::exists('gurus', 'id')],
        ]);
    }

    /**
     * Sinkronisasi data wali kelas di tabel guru.
     *
     * @param  Kelas  $kelas  Instance kelas
     * @return void
     */
    private function syncGuruWaliKelas(Kelas $kelas): void
    {
        if ($kelas->guru_id) {
            $kelas->guru()->update(['wali_kelas' => $kelas->nama]);
        }
    }

    /**
     * Clear wali kelas dari guru sebelumnya jika berbeda.
     *
     * @param  int|null  $previousGuruId  ID guru sebelumnya
     * @param  int|null  $currentGuruId   ID guru saat ini
     * @return void
     */
    private function clearPreviousWaliKelas(?int $previousGuruId, ?int $currentGuruId): void
    {
        if ($previousGuruId && $previousGuruId !== $currentGuruId) {
            Guru::whereKey($previousGuruId)->update(['wali_kelas' => null]);
        }
    }
}
