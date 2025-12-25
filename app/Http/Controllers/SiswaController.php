<?php

namespace App\Http\Controllers;

use App\Exports\SiswaTemplateExport;
use App\Imports\SiswaImport;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller untuk mengelola data siswa.
 *
 * Menangani operasi CRUD, import/export Excel, dan toggle status siswa.
 */
class SiswaController extends Controller
{
    /** @var int Ukuran maksimum foto dalam kilobytes */
    private const MAX_PHOTO_SIZE_KB = 2048;

    /** @var int Panjang maksimum NIS */
    private const MAX_NIS_LENGTH = 30;

    /** @var int Panjang maksimum nama */
    private const MAX_NAME_LENGTH = 255;

    /** @var int Jumlah siswa per chunk untuk bulk delete */
    private const BULK_DELETE_CHUNK_SIZE = 200;

    /** @var string Disk storage untuk foto siswa */
    private const PHOTO_DISK = 'public';

    /** @var string Folder penyimpanan foto siswa */
    private const PHOTO_FOLDER = 'siswa';

    /**
     * Menampilkan daftar semua siswa.
     *
     * @return View Halaman index siswa
     */
    public function index(): View
    {
        $siswas = Siswa::with('kelas')
            ->orderBy('nama')
            ->get();

        return view('siswa.index', compact('siswas'));
    }

    /**
     * Menampilkan detail satu siswa.
     *
     * @param  Siswa  $siswa  Instance siswa dari route model binding
     * @return View Halaman detail siswa
     */
    public function show(Siswa $siswa): View
    {
        return view('siswa.show', compact('siswa'));
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

        $data = $this->validatedData($request);
        $data['tahun_ajaran_id'] = $tahunId;
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
     * @param  Siswa  $siswa  Instance siswa dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroy(Siswa $siswa): RedirectResponse
    {
        $this->deletePhotoIfExists($siswa->photo_path);
        $siswa->delete();

        return back()->with('status', __('Siswa dihapus.'));
    }

    /**
     * Toggle status aktif/nonaktif siswa.
     *
     * @param  Siswa  $siswa  Instance siswa dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function toggleStatus(Siswa $siswa): RedirectResponse
    {
        $newStatus = ! $siswa->is_active;
        $siswa->update(['is_active' => $newStatus]);

        $message = $newStatus ? __('Siswa diaktifkan.') : __('Siswa dinonaktifkan.');

        return back()->with('status', $message);
    }

    /**
     * Menghapus semua data siswa.
     *
     * Menggunakan chunking untuk efisiensi memory pada data besar.
     *
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroyAll(): RedirectResponse
    {
        Siswa::chunkById(self::BULK_DELETE_CHUNK_SIZE, function ($siswas) {
            foreach ($siswas as $siswa) {
                $this->deletePhotoIfExists($siswa->photo_path);
                $siswa->delete();
            }
        });

        return back()->with('status', __('Semua siswa dihapus.'));
    }

    /**
     * Import data siswa dari file Excel.
     *
     * @param  Request  $request  HTTP request dengan file Excel
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan hasil import
     */
    public function import(Request $request): RedirectResponse
    {
        if (! session('selected_tahun_ajaran_id')) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new SiswaImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->withErrors([
                'file' => __('Gagal memproses file: :message', ['message' => $e->getMessage()]),
            ]);
        }

        return $this->buildImportResponse($import, 'siswa');
    }

    /**
     * Download template Excel untuk import siswa.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse File template Excel
     */
    public function template()
    {
        return Excel::download(new SiswaTemplateExport(), 'template-siswa.xlsx');
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
        $tahunId = session('selected_tahun_ajaran_id');

        $rules = [
            'nis' => [
                'required',
                'string',
                'max:' . self::MAX_NIS_LENGTH,
                Rule::unique('siswas', 'nis')
                    ->where(fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                    ->ignore($ignoreId),
            ],
            'nisn' => [
                'nullable',
                'string',
                'max:' . self::MAX_NIS_LENGTH,
                Rule::unique('siswas', 'nisn')
                    ->where(fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                    ->ignore($ignoreId),
            ],
            'nama' => ['required', 'string', 'max:' . self::MAX_NAME_LENGTH],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:50'],
            'status_keluarga' => ['nullable', 'string', 'max:50'],
            'anak_ke' => ['nullable', 'integer', 'min:1'],
            'telpon' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
            'sekolah_asal' => ['nullable', 'string', 'max:150'],
            'tanggal_diterima' => ['nullable', 'date'],
            'kelas_diterima' => ['nullable', 'string', 'max:50'],
            'nama_ayah' => ['nullable', 'string', 'max:150'],
            'nama_ibu' => ['nullable', 'string', 'max:150'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:100'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:100'],
            'alamat_orang_tua' => ['nullable', 'string'],
            'nama_wali' => ['nullable', 'string', 'max:150'],
            'pekerjaan_wali' => ['nullable', 'string', 'max:100'],
            'alamat_wali' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:' . self::MAX_PHOTO_SIZE_KB],
            'is_active' => ['sometimes', 'boolean'],
        ];

        return $request->validate($rules);
    }

    /**
     * Menghapus foto siswa jika ada.
     *
     * @param  string|null  $photoPath  Path foto yang akan dihapus
     * @return void
     */
    private function deletePhotoIfExists(?string $photoPath): void
    {
        if ($photoPath) {
            Storage::disk(self::PHOTO_DISK)->delete($photoPath);
        }
    }

    /**
     * Membangun response hasil import.
     *
     * @param  SiswaImport  $import  Instance import dengan hasil
     * @param  string       $entity  Nama entity untuk pesan (siswa/guru)
     * @return RedirectResponse Response dengan pesan hasil import
     */
    private function buildImportResponse(SiswaImport $import, string $entity): RedirectResponse
    {
        $failuresCount = $import->failures()->count();
        $skippedCount = count($import->skipped);
        $failureMessages = [];

        if ($failuresCount > 0) {
            foreach ($import->failures() as $failure) {
                $failureMessages[] = __('Baris :row: :errors', [
                    'row' => $failure->row(),
                    'errors' => implode('; ', $failure->errors()),
                ]);
            }
        }

        $statusParts = [];
        $statusParts[] = __(':count ' . $entity . ' berhasil diimpor.', ['count' => $import->imported]);

        if ($skippedCount > 0) {
            $statusParts[] = __(':count baris dilewati (duplikat/invalid).', ['count' => $skippedCount]);
        }

        if ($failuresCount > 0) {
            $statusParts[] = __(':count baris gagal validasi.', ['count' => $failuresCount]);
            $statusParts[] = implode(' | ', $failureMessages);
        }

        $response = back()->with('status', implode(' ', $statusParts));

        if (! empty($failureMessages)) {
            return $response->withErrors(['file' => $failureMessages]);
        }

        return $response;
    }
}
