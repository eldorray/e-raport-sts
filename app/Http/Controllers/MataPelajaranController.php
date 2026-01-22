<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $mataPelajaran = MataPelajaran::orderByRaw('COALESCE(NULLIF(urutan, ""), "9999")')
            ->orderBy('urutan')
            ->get();

        return view('lembaga.matapelajaran', compact('mataPelajaran'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:20', 'unique:mata_pelajarans,kode'],
            'nama_mapel' => ['required', 'string', 'max:255'],
            'jumlah_jam' => ['nullable', 'integer', 'min:0'],
            'kelompok' => ['nullable', 'string', 'max:100'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'urutan' => ['nullable', 'string', 'max:50'],
        ]);

        MataPelajaran::create($data);

        return back()->with('status', __('Mata pelajaran berhasil ditambahkan.'));
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $data = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mata_pelajarans', 'kode')->ignore($mataPelajaran->id),
            ],
            'nama_mapel' => ['required', 'string', 'max:255'],
            'jumlah_jam' => ['nullable', 'integer', 'min:0'],
            'kelompok' => ['nullable', 'string', 'max:100'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'urutan' => ['nullable', 'string', 'max:50'],
        ]);

        $mataPelajaran->update($data);

        // Sync JTM to all related Mengajar records
        if (isset($data['jumlah_jam'])) {
            \App\Models\Mengajar::where('mata_pelajaran_id', $mataPelajaran->id)
                ->update(['jtm' => $data['jumlah_jam']]);
        }

        return back()->with('status', __('Mata pelajaran berhasil diperbarui.'));
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->delete();

        return back()->with('status', __('Mata pelajaran berhasil dihapus.'));
    }

    /**
     * Sync mata pelajaran data dari API eksternal.
     *
     * @param  Request  $request  HTTP request dengan source (mapel-mi atau mapel-smp)
     * @return RedirectResponse Redirect dengan hasil sync
     */
    public function syncFromApi(Request $request): RedirectResponse
    {
        $request->validate([
            'source' => ['required', 'in:mapel-mi,mapel-smp'],
        ]);

        $source = $request->input('source');
        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        try {
            $apiBaseUrl = env('SYNC_API_BASE_URL', 'https://datainduk.ypdhalmadani.sch.id');
            $response = Http::timeout(60)->get("{$apiBaseUrl}/api/{$source}/all");

            if (!$response->successful()) {
                return back()->with('error', 'Gagal mengambil data dari API. Status: ' . $response->status());
            }

            $data = $response->json();
            $mapels = $data['data'] ?? $data;

            if (!is_array($mapels)) {
                return back()->with('error', 'Format response API tidak valid.');
            }

            foreach ($mapels as $mapelData) {
                try {
                    // Map API fields to local fields
                    $kode = $mapelData['kode_mapel'] ?? $mapelData['kode'] ?? null;
                    $nama = $mapelData['nama_mapel'] ?? $mapelData['nama'] ?? null;

                    // Trim leading/trailing spaces from nama
                    if ($nama) {
                        $nama = trim($nama);
                    }

                    if (!$kode || !$nama) {
                        $failed++;
                        $errors[] = "Data tidak lengkap: kode atau nama kosong";
                        continue;
                    }

                    // Prepare mapel data
                    $syncData = [
                        'kode' => $kode,
                        'nama_mapel' => $nama,
                        'jumlah_jam' => $mapelData['jam_per_minggu'] ?? $mapelData['jumlah_jam'] ?? null,
                        'kelompok' => $mapelData['kelompok'] ?? null,
                        'jurusan' => $mapelData['jurusan'] ?? null,
                        'urutan' => (string) ($mapelData['sort_order'] ?? $mapelData['urutan'] ?? ''),
                    ];

                    // Find existing mapel by kode
                    $existingMapel = MataPelajaran::where('kode', $kode)->first();

                    if ($existingMapel) {
                        $existingMapel->update($syncData);
                        $updated++;
                    } else {
                        MataPelajaran::create($syncData);
                        $created++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Error: " . $e->getMessage();
                }
            }

            $message = "Sync berhasil: {$created} mapel baru, {$updated} diperbarui.";
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
}
