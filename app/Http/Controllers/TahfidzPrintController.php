<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\PrintSetting;
use App\Models\SchoolProfile;
use App\Models\Siswa;
use App\Models\TahfidzPenilaian;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk mencetak raport Tahfidz Al-Qur'an.
 */
class TahfidzPrintController extends Controller
{
    /**
     * Menampilkan halaman cetak raport tahfidz.
     */
    public function show(Request $request, Siswa $siswa): View|\Illuminate\Http\RedirectResponse
    {
        $tahunId = $request->integer('tahun_ajaran_id') ?: session('selected_tahun_ajaran_id');
        $semester = $request->input('semester') ?: session('selected_semester');

        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran dan semester terlebih dahulu.'));
        }

        $kelas = $siswa->kelas;

        if (! $kelas) {
            return redirect()->back()->with('error', __('Siswa belum ditempatkan di kelas.'));
        }

        $school = SchoolProfile::first();
        $printSetting = PrintSetting::first();
        $tahun = TahunAjaran::find($tahunId);

        $penilaian = TahfidzPenilaian::with('pembimbing')
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->first();

        if (! $penilaian) {
            return redirect()->back()->with('error', __('Belum ada penilaian tahfidz untuk siswa ini.'));
        }

        $surahList = TahfidzPenilaian::SURAH_LIST;
        $predikatMap = TahfidzPenilaian::PREDIKAT_MAP;

        $printPlace = $printSetting?->tempat_cetak ?? $school?->city ?? 'Tangerang';
        $raporDate = $printSetting?->tanggal_cetak_rapor ?? now();

        return view('tahfidz.print', compact(
            'school',
            'siswa',
            'kelas',
            'tahun',
            'semester',
            'penilaian',
            'surahList',
            'predikatMap',
            'printPlace',
            'raporDate'
        ));
    }
}
