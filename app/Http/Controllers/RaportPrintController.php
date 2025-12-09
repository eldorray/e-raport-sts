<?php

namespace App\Http\Controllers;

use App\Models\EkskulPenilaian;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Mengajar;
use App\Models\PrintSetting;
use App\Models\RaporMetadata;
use App\Models\SchoolProfile;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RaportPrintController extends Controller
{
    public function show(Request $request, Siswa $siswa): View
    {
        $tahunId = $request->integer('tahun_ajaran_id') ?: session('selected_tahun_ajaran_id');
        $semester = $request->input('semester') ?: session('selected_semester');

        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran dan semester terlebih dahulu.'));
        }

        $kelas = $siswa->kelas;
        $wali = $kelas?->guru;

        $user = $request->user();
        $roleValue = $user->role ?? null;
        $roleSlug = $roleValue ? strtolower($roleValue) : null;
        $canCheckRole = method_exists($user, 'hasRole');
        $isAdmin = ($canCheckRole && $user->hasRole('admin')) || $roleSlug === 'admin';
        $isGuru = ($canCheckRole && $user->hasRole('guru')) || $roleSlug === 'guru';

        if ($isAdmin) {
            // admin bypass
        } elseif ($isGuru) {
            $guru = Guru::where('user_id', $user->id)->first();
            if (! $guru) {
                abort(403, __('Akun Anda belum terhubung dengan data guru.'));
            }

            $isWali = $wali && $wali->id === $guru->id;
            $isMengajar = Mengajar::where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahunId)
                ->when($kelas?->id, fn ($q) => $q->where('kelas_id', $kelas->id))
                ->exists();

            if (! $isWali && ! $isMengajar) {
                abort(403, __('Anda tidak memiliki akses ke rapor siswa ini.'));
            }
        }

        $school = SchoolProfile::first();
        $printSetting = PrintSetting::first();
        $tahun = TahunAjaran::find($tahunId);

        $nilai = Penilaian::with(['mataPelajaran', 'mengajar'])
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->get()
            ->map(function ($n) {
                $guruUser = $n->guru?->user;
                $bobotSumatif = $guruUser?->bobot_sumatif ?? (float) config('rapor.bobot_sumatif', 50);
                $bobotSts = $guruUser?->bobot_sts ?? (float) config('rapor.bobot_sts', 50);
                $rapor = null;
                if ($n->nilai_sumatif !== null && $n->nilai_sts !== null && abs(($bobotSumatif + $bobotSts) - 100) <= 0.01) {
                    $rapor = round((($n->nilai_sumatif * $bobotSumatif) + ($n->nilai_sts * $bobotSts)) / 100, 2);
                }

                $materiText = $n->materi_tp ?: __('Materi/TP belum diisi');
                $descriptor = null;

                if ($rapor !== null) {
                    if ($rapor >= 86) {
                        $descriptor = [
                            'predikat' => 'Sangat Baik',
                            'keterangan' => 'Sangat Menguasai',
                            'kalimat' => str_replace(
                                '[Materi/TP]',
                                $materiText,
                                'Peserta didik menunjukkan penguasaan yang sangat baik dalam [Materi/TP].',
                            ),
                        ];
                    } elseif ($rapor >= 76) {
                        $descriptor = [
                            'predikat' => 'Baik',
                            'keterangan' => 'Sudah Mampu',
                            'kalimat' => str_replace(
                                '[Materi/TP]',
                                $materiText,
                                'Peserta didik menunjukkan penguasaan yang baik dalam [Materi/TP].',
                            ),
                        ];
                    } elseif ($rapor >= 61) {
                        $descriptor = [
                            'predikat' => 'Cukup',
                            'keterangan' => 'Mulai Berkembang',
                            'kalimat' => str_replace(
                                ['[Materi/TP]', '[Sub-bagian tertentu]'],
                                [$materiText, 'bagian tertentu'],
                                'Peserta didik cukup mampu dalam [Materi/TP], namun masih perlu bimbingan pada [Sub-bagian tertentu].',
                            ),
                        ];
                    } else {
                        $descriptor = [
                            'predikat' => 'Perlu Bimbingan',
                            'keterangan' => 'Belum Mencapai',
                            'kalimat' => str_replace(
                                '[Materi/TP]',
                                $materiText,
                                'Peserta didik memerlukan bimbingan dalam [Materi/TP].',
                            ),
                        ];
                    }
                }

                return [
                    'mapel' => $n->mataPelajaran,
                    'sumatif' => $n->nilai_sumatif,
                    'sts' => $n->nilai_sts,
                    'rapor' => $rapor,
                    'deskripsi' => $n->materi_tp,
                    'descriptor' => $descriptor,
                    'kelompok' => $n->mataPelajaran?->kelompok,
                    'urutan' => $n->mataPelajaran?->urutan,
                ];
            })
            ->sortBy(function ($row) {
                return sprintf('%s-%03d-%s', $row['kelompok'] ?? 'Z', $row['urutan'] ?? 999, $row['mapel']?->nama_mapel);
            })
            ->values();

        $ekskul = EkskulPenilaian::with('ekskul')
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->get();

        $meta = RaporMetadata::firstOrCreate([
            'tahun_ajaran_id' => $tahunId,
            'semester' => $semester,
            'siswa_id' => $siswa->id,
        ], [
            'kelas_id' => $kelas?->id,
            'wali_guru_id' => $wali?->id,
            'tanggal_rapor' => now(),
            'prestasi' => [],
        ]);

        $prestasi = collect($meta->prestasi ?? [])->values()->take(3);

        // Prefer admin-configured values, then fall back to school/meta defaults
        $printPlace = $printSetting?->tempat_cetak
            ?? $school?->city
            ?? 'Tangerang';

        $raporDate = $printSetting?->tanggal_cetak_rapor
            ?? $meta->tanggal_rapor
            ?? now();

        $watermarkText = null;
        if ($printSetting) {
            $watermarkText = trim((string) $printSetting->watermark);
            if ($watermarkText === '') {
                $watermarkText = null;
            }
        } else {
            $watermarkText = $school?->name ?: 'MI Daarul Hikmah';
        }

        $watermarkDataUrl = null;
        if ($watermarkText) {
            $svg = sprintf(
                "<svg xmlns='http://www.w3.org/2000/svg' width='150' height='100' viewBox='0 0 150 100'><text x='0' y='30' fill='#2e6b3a' font-size='14' font-family='Times New Roman,serif' transform='rotate(30 0 30)'>%s</text></svg>",
                $watermarkText,
            );
            $watermarkDataUrl = 'data:image/svg+xml,'.rawurlencode($svg);
        }

        return view('rapor.print', [
            'school' => $school,
            'siswa' => $siswa,
            'kelas' => $kelas,
            'wali' => $wali,
            'tahunId' => $tahunId,
            'semester' => $semester,
            'tahun' => $tahun,
            'nilai' => $nilai,
            'ekskul' => $ekskul,
            'meta' => $meta,
            'prestasi' => $prestasi,
            'printPlace' => $printPlace,
            'raporDate' => $raporDate,
            'watermarkDataUrl' => $watermarkDataUrl,
        ]);
    }
}
