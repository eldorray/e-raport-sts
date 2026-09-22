<?php

namespace App\Http\Controllers;

use App\Exports\SiswaTemplateExport;
use App\Imports\SiswaImport;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\KelasResolverService;
use App\Services\PenghapusanDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    /** @var string Disk storage untuk foto siswa */
    private const PHOTO_DISK = 'public';

    /** @var string Folder penyimpanan foto siswa */
    private const PHOTO_FOLDER = 'siswa';

    /**
     * Menampilkan daftar siswa pada tahun ajaran yang sedang dipilih.
     *
     * @return View Halaman index siswa
     */
    public function index(): View
    {
        $tahunId = $this->tahunAjaranAktif();
        $tahunAjaran = $tahunId ? TahunAjaran::find($tahunId) : null;

        $siswas = Siswa::with('kelas')
            ->where('tahun_ajaran_id', $tahunId)
            ->withCount(['penilaians', 'raporMetadatas', 'tahfidzPenilaians', 'ekskulPenilaians'])
            ->orderBy('nama')
            ->get();

        $dampakHapus = $tahunId
            ? (new PenghapusanDataService)->ringkasanNilaiTahunAjaran($tahunId)
            : [];

        return view('siswa.index', compact('siswas', 'tahunAjaran', 'dampakHapus'));
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
     * @param  Siswa  $siswa  Instance siswa dari route model binding
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
     * Menghapus siswa beserta seluruh data nilainya.
     *
     * @param  Siswa  $siswa  Instance siswa dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroy(Siswa $siswa): RedirectResponse
    {
        (new PenghapusanDataService)->hapusSiswa($siswa);

        return back()->with('status', __('Siswa dihapus beserta nilai dan rapornya.'));
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
     * Menghapus semua siswa pada tahun ajaran yang sedang dipilih.
     *
     * Tahun ajaran lain tidak terpengaruh agar nilai tahun ajaran lain aman.
     * Data nilai siswa yang dihapus (rapor, tahfidz, ekskul) ikut dibersihkan
     * secara eksplisit.
     *
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroyAll(): RedirectResponse
    {
        $tahunId = $this->tahunAjaranAktif();

        if (! $tahunId) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        $jumlah = (new PenghapusanDataService)->hapusSiswaTahunAjaran($tahunId);

        return back()->with('status', __(':count siswa tahun ajaran ini dihapus beserta nilai dan rapornya.', [
            'count' => $jumlah,
        ]));
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

        $import = new SiswaImport;

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
        return Excel::download(new SiswaTemplateExport, 'template-siswa.xlsx');
    }

    /**
     * Sync siswa data dari API eksternal.
     *
     * @param  Request  $request  HTTP request dengan source (siswa-mi atau siswa-smp)
     * @return RedirectResponse Redirect dengan hasil sync
     */
    public function syncFromApi(Request $request): RedirectResponse
    {
        $tahunId = $this->tahunAjaranAktif();
        if (! $tahunId) {
            return back()->with('error', 'Pilih tahun ajaran terlebih dahulu.');
        }

        $request->validate([
            'source' => ['required', 'in:siswa-mi,siswa-smp'],
            'assign_kelas' => ['sometimes', 'boolean'],
        ]);

        $source = $request->input('source');
        $assignKelas = $request->boolean('assign_kelas', true);
        $kelasResolver = $assignKelas
            ? new KelasResolverService($tahunId, $source === 'siswa-mi' ? 'MI' : 'SMP')
            : null;
        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        try {
            $page = 1;
            $hasMorePages = true;
            $apiBaseUrl = config('services.data_induk.base_url');
            $baseUrl = "{$apiBaseUrl}/api/{$source}/all";

            while ($hasMorePages) {
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::timeout(60)->get($baseUrl, ['page' => $page]);

                if (! $response->successful()) {
                    return back()->with('error', 'Gagal mengambil data dari API. Status: '.$response->status());
                }

                $data = $response->json();
                $siswas = $data['data'] ?? $data;

                if (! is_array($siswas)) {
                    return back()->with('error', 'Format response API tidak valid.');
                }

                foreach ($siswas as $siswaData) {
                    try {
                        // Map API fields to local fields
                        $nama = $siswaData['nama_lengkap'] ?? $siswaData['nama'] ?? null;
                        $nis = $siswaData['nisn'] ?? $siswaData['nik'] ?? null; // Use NISN or NIK as NIS
                        $nisn = $siswaData['nisn'] ?? null;
                        $gender = $siswaData['jenis_kelamin'] ?? 'L';

                        // Normalize gender
                        if (in_array(strtolower($gender), ['laki-laki', 'male', 'l'])) {
                            $gender = 'L';
                        } elseif (in_array(strtolower($gender), ['perempuan', 'female', 'p'])) {
                            $gender = 'P';
                        }

                        if (! $nama) {
                            $failed++;
                            $errors[] = 'Data tidak lengkap: Nama kosong';

                            continue;
                        }

                        // Generate unique NIS if not available
                        if (! $nis) {
                            $nis = 'TMP-'.time().'-'.rand(1000, 9999);
                        }

                        // Petakan rombel dari API (mis. "Kelas 1 - KELAS 1A") ke kelas lokal
                        $tingkatRombel = $siswaData['tingkat_rombel'] ?? null;
                        $kelasId = null;
                        $namaRombel = null;

                        if ($kelasResolver !== null && $tingkatRombel) {
                            $kelasId = $kelasResolver->resolveId($tingkatRombel);
                            $namaRombel = $kelasResolver->parseRombel($tingkatRombel)['nama'] ?? null;
                        }

                        // Prepare siswa data
                        $syncData = [
                            'tahun_ajaran_id' => $tahunId,
                            'nis' => $nis,
                            'nisn' => $nisn,
                            'kelas_id' => $kelasId,
                            'kelas_diterima' => $namaRombel,
                            'nama' => $nama,
                            'jenis_kelamin' => $gender,
                            'tempat_lahir' => $siswaData['tempat_lahir'] ?? null,
                            'tanggal_lahir' => $siswaData['tanggal_lahir'] ?? null,
                            'alamat' => $siswaData['alamat'] ?? null,
                            'telpon' => $siswaData['no_telepon'] ?? null,
                            'nama_ayah' => $siswaData['nama_ayah_kandung'] ?? null,
                            'nama_ibu' => $siswaData['nama_ibu_kandung'] ?? null,
                            'nama_wali' => $siswaData['nama_wali'] ?? null,
                            'is_active' => ($siswaData['status'] ?? 'Aktif') === 'Aktif',
                        ];

                        // Find existing siswa by NISN or NIS
                        $existingSiswa = null;
                        if ($nisn) {
                            $existingSiswa = Siswa::where('tahun_ajaran_id', $tahunId)
                                ->where('nisn', $nisn)
                                ->first();
                        }
                        if (! $existingSiswa && $nis) {
                            $existingSiswa = Siswa::where('tahun_ajaran_id', $tahunId)
                                ->where('nis', $nis)
                                ->first();
                        }

                        if ($existingSiswa) {
                            // Jangan hapus kelas/kelas diterima lama bila API tidak mengirim rombel
                            if ($kelasId === null) {
                                unset($syncData['kelas_id']);
                            }

                            if ($namaRombel === null || filled($existingSiswa->kelas_diterima)) {
                                unset($syncData['kelas_diterima']);
                            }

                            $existingSiswa->update($syncData);
                            $updated++;
                        } else {
                            Siswa::create($syncData);
                            $created++;
                        }
                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = 'Error: '.$e->getMessage();
                    }
                }

                // Check pagination
                $lastPage = $data['last_page'] ?? 1;
                $currentPage = $data['current_page'] ?? $page;
                $nextPageUrl = $data['next_page_url'] ?? null;

                if ($nextPageUrl || $currentPage < $lastPage) {
                    $page++;
                } else {
                    $hasMorePages = false;
                }

                if ($page > 1000) {
                    $hasMorePages = false;
                }
            }

            $message = "Sync berhasil: {$created} siswa baru, {$updated} diperbarui.";
            if ($kelasResolver?->created > 0) {
                $message .= " {$kelasResolver->created} kelas baru dibuat otomatis.";
            }
            if ($failed > 0) {
                $message .= " {$failed} gagal.";
            }

            return back()->with('status', $message);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Sync API Error: '.$e->getMessage());

            return back()->with('error', 'Tidak dapat terhubung ke API. Pastikan server API berjalan.');
        } catch (\Exception $e) {
            Log::error('Sync API Error: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Validasi data request untuk create/update siswa.
     *
     * @param  Request  $request  HTTP request dengan data siswa
     * @param  int|null  $ignoreId  ID siswa yang diabaikan untuk validasi unique
     * @return array<string, mixed> Data yang sudah divalidasi
     */
    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $tahunId = session('selected_tahun_ajaran_id');

        $rules = [
            'nis' => [
                'required',
                'string',
                'max:'.self::MAX_NIS_LENGTH,
                Rule::unique('siswas', 'nis')
                    ->where(fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                    ->ignore($ignoreId),
            ],
            'nisn' => [
                'nullable',
                'string',
                'max:'.self::MAX_NIS_LENGTH,
                Rule::unique('siswas', 'nisn')
                    ->where(fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                    ->ignore($ignoreId),
            ],
            'nama' => ['required', 'string', 'max:'.self::MAX_NAME_LENGTH],
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
            'photo' => ['nullable', 'image', 'max:'.self::MAX_PHOTO_SIZE_KB],
            'is_active' => ['sometimes', 'boolean'],
        ];

        return $request->validate($rules);
    }

    /**
     * Menentukan tahun ajaran yang dipakai halaman siswa.
     *
     * Memakai tahun ajaran pada sesi, dan jatuh ke tahun ajaran aktif bila sesi
     * belum dipilih — sehingga tidak ada aksi yang berjalan lintas tahun ajaran.
     *
     * @return int|null ID tahun ajaran
     */
    private function tahunAjaranAktif(): ?int
    {
        return session('selected_tahun_ajaran_id') ?? TahunAjaran::where('is_active', true)->value('id');
    }

    /**
     * Menghapus foto siswa jika ada.
     *
     * @param  string|null  $photoPath  Path foto yang akan dihapus
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
     * @param  string  $entity  Nama entity untuk pesan (siswa/guru)
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
        $statusParts[] = __(':count '.$entity.' berhasil diimpor.', ['count' => $import->imported]);

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
