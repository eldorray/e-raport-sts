<?php

namespace App\Http\Controllers;

use App\Exports\GuruTemplateExport;
use App\Imports\GuruImport;
use App\Models\Guru;
use App\Models\Mengajar;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller untuk mengelola data guru.
 *
 * Menangani operasi CRUD, import/export Excel, dan toggle status guru.
 */
class GuruController extends Controller
{
    /** @var string Role user untuk guru */
    private const USER_ROLE_GURU = 'guru';

    /** @var int Jumlah guru per chunk untuk bulk delete */
    private const BULK_DELETE_CHUNK_SIZE = 100;

    /** @var int Jumlah item per halaman untuk pagination */
    private const PAGINATION_PER_PAGE = 15;

    /** @var string Domain email default untuk guru */
    private const EMAIL_DOMAIN = '@guru.local';

    /** @var int Panjang minimum password */
    private const MIN_PASSWORD_LENGTH = 3;

    /**
     * Menampilkan daftar semua guru dengan JTM mengajar.
     *
     * @return View Halaman index guru
     */
    public function index(): View
    {
        $gurus = Guru::with('user')->orderBy('nama')->get();

        $tahunId = session('selected_tahun_ajaran_id') ?? TahunAjaran::where('is_active', true)->value('id');
        $guruIds = $gurus->pluck('id');

        $jtmMengajar = $this->calculateJtmMengajar($guruIds, $tahunId);

        return view('guru.index', [
            'gurus' => $gurus,
            'jtmMengajar' => $jtmMengajar,
        ]);
    }

    /**
     * Menyimpan guru baru ke database.
     *
     * Membuat user account dan data guru secara bersamaan.
     *
     * @param  Request  $request  HTTP request dengan data guru
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $user = User::create([
            'name' => $data['nama'],
            'email' => $this->buildEmail($data['nip']),
            'password' => Hash::make($data['password']),
            'role' => self::USER_ROLE_GURU,
            'nip' => $data['nip'],
            'nik' => $data['nik'] ?? null,
            'is_active' => $data['is_active'],
        ]);

        Guru::create([
            'user_id' => $user->id,
            'nama' => $data['nama'],
            'nip' => $data['nip'],
            'nik' => $data['nik'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'pendidikan' => $data['pendidikan'] ?? null,
            'wali_kelas' => $data['wali_kelas'] ?? null,
            'jtm' => $data['jtm'] ?? null,
            'initial_password' => $data['password'],
            'is_active' => $data['is_active'],
        ]);

        return back()->with('status', __('Guru berhasil ditambahkan.'));
    }

    /**
     * Memperbarui data guru yang sudah ada.
     *
     * @param  Request  $request  HTTP request dengan data yang diperbarui
     * @param  Guru     $guru     Instance guru dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function update(Request $request, Guru $guru): RedirectResponse
    {
        $data = $this->validatedData($request, $guru->id);

        $updateUser = [
            'name' => $data['nama'],
            'email' => $guru->user->email ?: $this->buildEmail($data['nip']),
            'role' => self::USER_ROLE_GURU,
            'nip' => $data['nip'],
            'nik' => $data['nik'] ?? null,
            'is_active' => $data['is_active'],
        ];

        if (! empty($data['password'])) {
            $updateUser['password'] = Hash::make($data['password']);
        }

        $guru->user->update($updateUser);

        $guru->update([
            'nama' => $data['nama'],
            'nip' => $data['nip'],
            'nik' => $data['nik'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'pendidikan' => $data['pendidikan'] ?? null,
            'wali_kelas' => $data['wali_kelas'] ?? null,
            'jtm' => $data['jtm'] ?? null,
            'initial_password' => $data['password'] ?: $guru->initial_password,
            'is_active' => $data['is_active'],
        ]);

        return back()->with('status', __('Guru berhasil diperbarui.'));
    }

    /**
     * Menghapus guru dan user account terkait.
     *
     * @param  Guru  $guru  Instance guru dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroy(Guru $guru): RedirectResponse
    {
        $guru->user?->delete();
        $guru->delete();

        return back()->with('status', __('Guru dihapus.'));
    }

    /**
     * Menghapus semua data guru beserta data mengajar terkait.
     *
     * Menggunakan chunking untuk efisiensi memory pada data besar.
     *
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroyAll(): RedirectResponse
    {
        Guru::chunkById(self::BULK_DELETE_CHUNK_SIZE, function ($gurus) {
            $guruIds = $gurus->pluck('id');

            Mengajar::whereIn('guru_id', $guruIds)->delete();

            foreach ($gurus as $guru) {
                $guru->user?->delete();
                $guru->delete();
            }
        });

        return back()->with('status', __('Semua guru dihapus.'));
    }

    /**
     * Toggle status aktif/nonaktif guru.
     *
     * @param  Guru  $guru  Instance guru dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function toggleStatus(Guru $guru): RedirectResponse
    {
        $newStatus = ! $guru->is_active;
        $guru->update(['is_active' => $newStatus]);
        $guru->user?->update(['is_active' => $newStatus]);

        $message = $newStatus ? __('Guru diaktifkan.') : __('Guru dinonaktifkan.');

        return back()->with('status', $message);
    }

    /**
     * Import data guru dari file Excel.
     *
     * @param  Request  $request  HTTP request dengan file Excel
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan hasil import
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new GuruImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->withErrors([
                'file' => __('Gagal memproses file: :message', ['message' => $e->getMessage()]),
            ]);
        }

        return $this->buildImportResponse($import);
    }

    /**
     * Download template Excel untuk import guru.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse File template Excel
     */
    public function template()
    {
        return Excel::download(new GuruTemplateExport(), 'template-guru.xlsx');
    }

    /**
     * Sync guru data dari API eksternal.
     *
     * @param  Request  $request  HTTP request dengan source (guru-mi atau guru-smp)
     * @return RedirectResponse Redirect dengan hasil sync
     */
    public function syncFromApi(Request $request): RedirectResponse
    {
        $request->validate([
            'source' => ['required', 'in:guru-mi,guru-smp'],
        ]);

        $source = $request->input('source');
        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        try {
            $page = 1;
            $hasMorePages = true;
            $apiBaseUrl = env('SYNC_API_BASE_URL', 'https://datainduk.ypdhalmadani.sch.id');
            $baseUrl = "{$apiBaseUrl}/api/{$source}/all";

            while ($hasMorePages) {
                $response = Http::timeout(60)->get($baseUrl, ['page' => $page]);

                if (!$response->successful()) {
                    return back()->with('error', 'Gagal mengambil data dari API. Status: ' . $response->status());
                }

                $data = $response->json();
                $gurus = $data['data'] ?? $data;

                if (!is_array($gurus)) {
                    return back()->with('error', 'Format response API tidak valid.');
                }

                foreach ($gurus as $guruData) {
                    try {
                        // Map API fields to local fields
                        $nip = $guruData['nik'] ?? null; // Using NIK as NIP identifier
                        $nik = $guruData['nik'] ?? null;
                        $nama = $guruData['full_name'] ?? $guruData['nama'] ?? null;
                        $gender = $guruData['gender'] ?? 'L';

                        // Normalize gender
                        if (in_array(strtolower($gender), ['laki-laki', 'male', 'l'])) {
                            $gender = 'L';
                        } elseif (in_array(strtolower($gender), ['perempuan', 'female', 'p'])) {
                            $gender = 'P';
                        }

                        if (!$nip || !$nama) {
                            $failed++;
                            $errors[] = "Data tidak lengkap: NIP={$nip}, Nama={$nama}";
                            continue;
                        }

                        // Prepare guru data
                        $syncData = [
                            'nama' => $nama,
                            'nip' => $nip,
                            'nik' => $nik,
                            'jenis_kelamin' => $gender,
                            'tempat_lahir' => $guruData['pob'] ?? null,
                            'tanggal_lahir' => $guruData['dob'] ?? null,
                            'is_active' => $guruData['is_active'] ?? true,
                        ];

                        // Find existing guru by NIP
                        $existingGuru = Guru::where('nip', $nip)->first();

                        if ($existingGuru) {
                            $existingGuru->update($syncData);
                            $existingGuru->user?->update([
                                'name' => $nama,
                                'nip' => $nip,
                                'nik' => $nik,
                                'is_active' => $guruData['is_active'] ?? true,
                            ]);
                            $updated++;
                        } else {
                            // Create user first
                            $user = User::create([
                                'name' => $nama,
                                'email' => Str::slug($nip, '.') . self::EMAIL_DOMAIN,
                                'password' => Hash::make($nip), // Default password is NIP
                                'role' => self::USER_ROLE_GURU,
                                'nip' => $nip,
                                'nik' => $nik,
                                'is_active' => $guruData['is_active'] ?? true,
                            ]);

                            $syncData['user_id'] = $user->id;
                            $syncData['initial_password'] = $nip;
                            Guru::create($syncData);
                            $created++;
                        }
                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = "Error: " . $e->getMessage();
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

            $message = "Sync berhasil: {$created} guru baru, {$updated} diperbarui.";
            if ($failed > 0) {
                $message .= " {$failed} gagal.";
            }

            return back()->with('status', $message);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Sync API Error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke API. Pastikan server API berjalan.');
        } catch (\Exception $e) {
            Log::error('Sync API Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Validasi data request untuk create/update guru.
     *
     * @param  Request   $request       HTTP request dengan data guru
     * @param  int|null  $ignoreGuruId  ID guru yang diabaikan untuk validasi unique
     * @return array Data yang sudah divalidasi
     */
    private function validatedData(Request $request, ?int $ignoreGuruId = null): array
    {
        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:30', Rule::unique('gurus', 'nip')->ignore($ignoreGuruId)],
            'nik' => ['nullable', 'string', 'max:30', Rule::unique('gurus', 'nik')->ignore($ignoreGuruId)],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'pendidikan' => ['nullable', 'string', 'max:100'],
            'wali_kelas' => ['nullable', 'string', 'max:50'],
            'jtm' => ['nullable', 'integer', 'min:0'],
            'password' => [$ignoreGuruId ? 'nullable' : 'required', 'string', 'min:' . self::MIN_PASSWORD_LENGTH],
            'is_active' => ['required', 'boolean'],
        ];

        $data = $request->validate($rules);
        $data['password'] = $request->filled('password') ? $request->string('password') : '';

        return $data;
    }

    /**
     * Membangun email dari NIP guru.
     *
     * @param  string  $nip  Nomor Induk Pegawai
     * @return string Email yang dibuat
     */
    private function buildEmail(string $nip): string
    {
        return Str::slug($nip, '.') . self::EMAIL_DOMAIN;
    }

    /**
     * Menghitung total JTM mengajar per guru.
     *
     * @param  \Illuminate\Support\Collection  $guruIds  Koleksi ID guru
     * @param  int|null                        $tahunId  ID tahun ajaran
     * @return array<int, int> Array dengan key guru_id dan value total JTM
     */
    private function calculateJtmMengajar($guruIds, ?int $tahunId): array
    {
        $jtmMengajar = [];

        if ($guruIds->isEmpty()) {
            return $jtmMengajar;
        }

        $mengajar = Mengajar::with('mataPelajaran')
            ->whereIn('guru_id', $guruIds)
            ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->get();

        foreach ($mengajar as $m) {
            $jam = $m->jtm ?? $m->mataPelajaran?->jumlah_jam ?? 0;
            $jtmMengajar[$m->guru_id] = ($jtmMengajar[$m->guru_id] ?? 0) + (int) $jam;
        }

        return $jtmMengajar;
    }

    /**
     * Membangun response hasil import.
     *
     * @param  GuruImport  $import  Instance import dengan hasil
     * @return RedirectResponse Response dengan pesan hasil import
     */
    private function buildImportResponse(GuruImport $import): RedirectResponse
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
        $statusParts[] = __(':count guru berhasil diimpor.', ['count' => $import->imported]);

        if ($skippedCount > 0) {
            $statusParts[] = __(':count baris dilewati (duplikat/invalid).', ['count' => $skippedCount]);
        }

        if ($failuresCount > 0) {
            $statusParts[] = __(':count baris gagal validasi.', ['count' => $failuresCount]);
        }

        if (! empty($failureMessages)) {
            $statusParts[] = implode(' | ', $failureMessages);
        }

        $response = back()->with('status', implode(' ', $statusParts));

        if (! empty($failureMessages)) {
            return $response->withErrors(['file' => $failureMessages]);
        }

        return $response;
    }
}
