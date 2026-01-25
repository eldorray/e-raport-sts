<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller untuk wali kelas mengelola data siswa di kelasnya.
 *
 * Menangani operasi CRUD siswa oleh wali kelas.
 */
class WaliKelasSiswaController extends Controller
{
    /** @var int Ukuran maksimum foto dalam kilobytes */
    private const MAX_PHOTO_SIZE_KB = 2048;

    /** @var int Panjang maksimum NIS */
    private const MAX_NIS_LENGTH = 30;

    /** @var int Panjang maksimum nama */
    private const MAX_NAME_LENGTH = 255;

    /** @var string Disk storage untuk foto siswa */
    private const PHOTO_DISK = 'public';

    /** @var string Folder penyimpanan foto siswa */
    private const PHOTO_FOLDER = 'siswa';

    /**
     * Menampilkan daftar siswa di kelas wali kelas.
     *
     * @param  Request  $request  HTTP request
     * @return View|RedirectResponse Halaman index siswa atau redirect jika bukan wali kelas
     */
    public function index(Request $request): View|RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');

        if (! $tahunId) {
            return redirect()->route('dashboard')
                ->with('error', __('Pilih tahun ajaran terlebih dahulu.'));
        }

        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $guru) {
            return redirect()->route('dashboard')
                ->with('error', __('Data guru tidak ditemukan.'));
        }

        // Cari kelas di mana guru ini adalah wali kelas
        $kelas = Kelas::where('guru_id', $guru->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->first();

        if (! $kelas) {
            return redirect()->route('dashboard')
                ->with('error', __('Anda bukan wali kelas pada tahun ajaran ini.'));
        }

        $siswas = Siswa::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->orderBy('nama')
            ->get();

        return view('guru.wali-kelas.siswa.index', [
            'kelas' => $kelas,
            'siswas' => $siswas,
            'guru' => $guru,
        ]);
    }

    /**
     * Menampilkan detail satu siswa.
     *
     * @param  Request  $request  HTTP request
     * @param  Siswa    $siswa    Instance siswa dari route model binding
     * @return View|RedirectResponse Halaman detail siswa
     */
    public function show(Request $request, Siswa $siswa): View|RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $guru) {
            return redirect()->route('dashboard')
                ->with('error', __('Data guru tidak ditemukan.'));
        }

        $kelas = Kelas::where('guru_id', $guru->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->first();

        if (! $kelas || $siswa->kelas_id !== $kelas->id) {
            return redirect()->route('wali-kelas.siswa.index')
                ->with('error', __('Siswa ini bukan dari kelas Anda.'));
        }

        return view('guru.wali-kelas.siswa.show', [
            'siswa' => $siswa,
            'kelas' => $kelas,
        ]);
    }

    /**
     * Menyimpan siswa baru ke database.
     *
     * @param  Request  $request  HTTP request dengan data siswa
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function store(Request $request): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');

        if (! $tahunId) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $guru) {
            return back()->with('error', __('Data guru tidak ditemukan.'));
        }

        $kelas = Kelas::where('guru_id', $guru->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->first();

        if (! $kelas) {
            return back()->with('error', __('Anda bukan wali kelas pada tahun ajaran ini.'));
        }

        $data = $this->validatedData($request);
        $data['tahun_ajaran_id'] = $tahunId;
        $data['kelas_id'] = $kelas->id;
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store(self::PHOTO_FOLDER, self::PHOTO_DISK);
        }

        Siswa::create($data);

        return back()->with('status', __('Siswa berhasil ditambahkan.'));
    }

    /**
     * Memperbarui data siswa yang sudah ada.
     *
     * @param  Request  $request  HTTP request dengan data yang diperbarui
     * @param  Siswa    $siswa    Instance siswa dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $guru) {
            return back()->with('error', __('Data guru tidak ditemukan.'));
        }

        $kelas = Kelas::where('guru_id', $guru->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->first();

        if (! $kelas || $siswa->kelas_id !== $kelas->id) {
            return back()->with('error', __('Siswa ini bukan dari kelas Anda.'));
        }

        $data = $this->validatedData($request, $siswa->id);
        $data['is_active'] = $request->boolean('is_active', $siswa->is_active);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store(self::PHOTO_FOLDER, self::PHOTO_DISK);
            $this->deletePhotoIfExists($siswa->photo_path);
        }

        $siswa->update($data);

        return back()->with('status', __('Siswa berhasil diperbarui.'));
    }

    /**
     * Menghapus siswa dari database.
     *
     * @param  Request  $request  HTTP request
     * @param  Siswa    $siswa    Instance siswa dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroy(Request $request, Siswa $siswa): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $guru) {
            return back()->with('error', __('Data guru tidak ditemukan.'));
        }

        $kelas = Kelas::where('guru_id', $guru->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->first();

        if (! $kelas || $siswa->kelas_id !== $kelas->id) {
            return back()->with('error', __('Siswa ini bukan dari kelas Anda.'));
        }

        $this->deletePhotoIfExists($siswa->photo_path);
        $siswa->delete();

        return back()->with('status', __('Siswa dihapus.'));
    }

    /**
     * Toggle status aktif/nonaktif siswa.
     *
     * @param  Request  $request  HTTP request
     * @param  Siswa    $siswa    Instance siswa dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function toggleStatus(Request $request, Siswa $siswa): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $guru) {
            return back()->with('error', __('Data guru tidak ditemukan.'));
        }

        $kelas = Kelas::where('guru_id', $guru->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->first();

        if (! $kelas || $siswa->kelas_id !== $kelas->id) {
            return back()->with('error', __('Siswa ini bukan dari kelas Anda.'));
        }

        $newStatus = ! $siswa->is_active;
        $siswa->update(['is_active' => $newStatus]);

        $message = $newStatus ? __('Siswa diaktifkan.') : __('Siswa dinonaktifkan.');

        return back()->with('status', $message);
    }

    /**
     * Validasi data request untuk create/update siswa.
     *
     * @param  Request   $request   HTTP request dengan data siswa
     * @param  int|null  $ignoreId  ID siswa yang diabaikan untuk validasi unique
     * @return array Data yang sudah divalidasi
     */
    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nis' => [
                'required',
                'string',
                'max:' . self::MAX_NIS_LENGTH,
                Rule::unique('siswas', 'nis')->ignore($ignoreId),
            ],
            'nisn' => ['nullable', 'string', 'max:' . self::MAX_NIS_LENGTH],
            'nama' => ['required', 'string', 'max:' . self::MAX_NAME_LENGTH],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:50'],
            'status_keluarga' => ['nullable', 'string', 'max:50'],
            'anak_ke' => ['nullable', 'integer', 'min:1'],
            'telpon' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'sekolah_asal' => ['nullable', 'string', 'max:' . self::MAX_NAME_LENGTH],
            'tanggal_diterima' => ['nullable', 'date'],
            'kelas_diterima' => ['nullable', 'string', 'max:50'],
            'nama_ayah' => ['nullable', 'string', 'max:' . self::MAX_NAME_LENGTH],
            'nama_ibu' => ['nullable', 'string', 'max:' . self::MAX_NAME_LENGTH],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:100'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:100'],
            'alamat_orang_tua' => ['nullable', 'string'],
            'nama_wali' => ['nullable', 'string', 'max:' . self::MAX_NAME_LENGTH],
            'pekerjaan_wali' => ['nullable', 'string', 'max:100'],
            'alamat_wali' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:' . self::MAX_PHOTO_SIZE_KB],
        ]);
    }

    /**
     * Hapus file foto jika ada.
     *
     * @param  string|null  $path  Path file foto
     * @return void
     */
    private function deletePhotoIfExists(?string $path): void
    {
        if ($path && Storage::disk(self::PHOTO_DISK)->exists($path)) {
            Storage::disk(self::PHOTO_DISK)->delete($path);
        }
    }
}
