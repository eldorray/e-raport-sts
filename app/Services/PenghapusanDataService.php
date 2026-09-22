<?php

namespace App\Services;

use App\Models\Ekskul;
use App\Models\EkskulPenilaian;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\RaporMetadata;
use App\Models\Siswa;
use App\Models\TahfidzPenilaian;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Penjaga penghapusan data master agar nilai rapor tidak hilang tanpa disadari.
 *
 * Data nilai (rapor, tahfidz, ekskul, dan metadata rapor) terhubung ke siswa,
 * kelas, mata pelajaran, guru, jadwal mengajar, dan tahun ajaran. Service ini:
 * 1. memblokir penghapusan data induk yang masih punya nilai,
 * 2. menghitung jumlah data yang akan ikut terhapus untuk konfirmasi, dan
 * 3. menghapus data nilai secara eksplisit ketika siswa memang dihapus.
 */
class PenghapusanDataService
{
    /** @var int Jumlah siswa per chunk saat penghapusan massal */
    private const CHUNK_SIZE = 200;

    /** @var string Disk storage untuk foto siswa */
    private const PHOTO_DISK = 'public';

    /**
     * Alasan kelas tidak boleh dihapus (null bila aman dihapus).
     *
     * @param  Kelas  $kelas  Instance kelas
     * @return string|null Pesan penolakan atau null
     */
    public function alasanKelasTidakBisaDihapus(Kelas $kelas): ?string
    {
        $rincian = $this->rangkuman([
            'nilai' => Penilaian::where('kelas_id', $kelas->id)->count(),
            'rapor' => RaporMetadata::where('kelas_id', $kelas->id)->count(),
        ]);

        return $rincian === null ? null : $this->pesanTolak(__('kelas :nama', ['nama' => $kelas->nama]), $rincian);
    }

    /**
     * Alasan mata pelajaran tidak boleh dihapus (null bila aman dihapus).
     *
     * @param  MataPelajaran  $mataPelajaran  Instance mata pelajaran
     * @return string|null Pesan penolakan atau null
     */
    public function alasanMataPelajaranTidakBisaDihapus(MataPelajaran $mataPelajaran): ?string
    {
        $rincian = $this->rangkuman([
            'nilai' => Penilaian::where('mata_pelajaran_id', $mataPelajaran->id)->count(),
        ]);

        return $rincian === null ? null : $this->pesanTolak(__('mata pelajaran :nama', ['nama' => $mataPelajaran->nama_mapel]), $rincian);
    }

    /**
     * Alasan guru tidak boleh dihapus (null bila aman dihapus).
     *
     * @param  Guru  $guru  Instance guru
     * @return string|null Pesan penolakan atau null
     */
    public function alasanGuruTidakBisaDihapus(Guru $guru): ?string
    {
        $rincian = $this->rangkuman([
            'nilai' => Penilaian::where('guru_id', $guru->id)->count(),
        ]);

        if ($rincian === null) {
            return null;
        }

        return $this->pesanTolak(__('guru :nama', ['nama' => $guru->nama]), $rincian)
            .' '.__('Nonaktifkan guru ini bila sudah tidak mengajar.');
    }

    /**
     * Alasan jadwal mengajar tidak boleh dihapus (null bila aman dihapus).
     *
     * @param  Mengajar  $mengajar  Instance jadwal mengajar
     * @return string|null Pesan penolakan atau null
     */
    public function alasanMengajarTidakBisaDihapus(Mengajar $mengajar): ?string
    {
        $rincian = $this->rangkuman([
            'nilai' => Penilaian::where('mengajar_id', $mengajar->id)->count(),
        ]);

        if ($rincian === null) {
            return null;
        }

        return $this->pesanTolak(__('jadwal mengajar ini'), $rincian)
            .' '.__('Gunakan tombol reset nilai pada halaman penilaian bila memang ingin mengosongkan nilai.');
    }

    /**
     * Alasan ekskul tidak boleh dihapus (null bila aman dihapus).
     *
     * @param  Ekskul  $ekskul  Instance ekskul
     * @return string|null Pesan penolakan atau null
     */
    public function alasanEkskulTidakBisaDihapus(Ekskul $ekskul): ?string
    {
        $rincian = $this->rangkuman([
            'nilai ekskul' => EkskulPenilaian::where('ekskul_id', $ekskul->id)->count(),
        ]);

        return $rincian === null ? null : $this->pesanTolak(__('ekskul :nama', ['nama' => $ekskul->nama]), $rincian);
    }

    /**
     * Alasan tahun ajaran tidak boleh dihapus (null bila aman dihapus).
     *
     * @param  TahunAjaran  $tahunAjaran  Instance tahun ajaran
     * @return string|null Pesan penolakan atau null
     */
    public function alasanTahunAjaranTidakBisaDihapus(TahunAjaran $tahunAjaran): ?string
    {
        $rincian = $this->rangkuman([
            'siswa' => Siswa::where('tahun_ajaran_id', $tahunAjaran->id)->count(),
            'kelas' => Kelas::where('tahun_ajaran_id', $tahunAjaran->id)->count(),
            'nilai' => Penilaian::where('tahun_ajaran_id', $tahunAjaran->id)->count(),
            'rapor' => RaporMetadata::where('tahun_ajaran_id', $tahunAjaran->id)->count(),
            'penilaian tahfidz' => TahfidzPenilaian::where('tahun_ajaran_id', $tahunAjaran->id)->count(),
            'jadwal mengajar' => Mengajar::where('tahun_ajaran_id', $tahunAjaran->id)->count(),
        ]);

        if ($rincian === null) {
            return null;
        }

        return $this->pesanTolak(__('tahun ajaran :nama', ['nama' => $tahunAjaran->nama]), $rincian)
            .' '.__('Hapus atau pindahkan data tersebut lebih dulu.');
    }

    /**
     * Alasan seluruh guru tidak boleh dihapus sekaligus (null bila aman).
     *
     * Penghapusan seluruh guru ikut menghapus jadwal mengajar mereka sehingga
     * seluruh nilai di semua tahun ajaran akan ikut terhapus.
     *
     * @return string|null Pesan penolakan atau null
     */
    public function alasanSemuaGuruTidakBisaDihapus(): ?string
    {
        $rincian = $this->rangkuman([
            'nilai' => Penilaian::count(),
        ]);

        if ($rincian === null) {
            return null;
        }

        return $this->pesanTolak(__('semua guru'), $rincian)
            .' '.__('Hapus nilai tersebut lebih dulu atau nonaktifkan guru yang sudah tidak mengajar.');
    }

    /**
     * Menghitung data nilai milik satu tahun ajaran.
     *
     * @param  int  $tahunAjaranId  ID tahun ajaran
     * @return array<string, int> Label data => jumlah
     */
    public function ringkasanNilaiTahunAjaran(int $tahunAjaranId): array
    {
        return [
            'nilai' => Penilaian::where('tahun_ajaran_id', $tahunAjaranId)->count(),
            'rapor' => RaporMetadata::where('tahun_ajaran_id', $tahunAjaranId)->count(),
            'penilaian tahfidz' => TahfidzPenilaian::where('tahun_ajaran_id', $tahunAjaranId)->count(),
            'nilai ekskul' => EkskulPenilaian::where('tahun_ajaran_id', $tahunAjaranId)->count(),
        ];
    }

    /**
     * Menghitung data nilai milik satu siswa.
     *
     * @param  Siswa  $siswa  Instance siswa
     * @return array<string, int> Label data => jumlah
     */
    public function ringkasanNilaiSiswa(Siswa $siswa): array
    {
        return [
            'nilai' => Penilaian::where('siswa_id', $siswa->id)->count(),
            'rapor' => RaporMetadata::where('siswa_id', $siswa->id)->count(),
            'penilaian tahfidz' => TahfidzPenilaian::where('siswa_id', $siswa->id)->count(),
            'nilai ekskul' => EkskulPenilaian::where('siswa_id', $siswa->id)->count(),
        ];
    }

    /**
     * Menyusun kalimat jumlah data, mis. "12 nilai, 1 rapor".
     *
     * @param  array<string, int>  $jumlah  Label data => jumlah
     * @return string|null Kalimat jumlah, atau null bila seluruhnya nol
     */
    public function rangkuman(array $jumlah): ?string
    {
        $bagian = [];

        foreach ($jumlah as $label => $total) {
            if ($total > 0) {
                $bagian[] = $this->formatAngka($total).' '.$label;
            }
        }

        if ($bagian === []) {
            return null;
        }

        return implode(', ', $bagian);
    }

    /**
     * Menghapus siswa beserta seluruh data nilainya.
     *
     * @param  Siswa  $siswa  Instance siswa
     */
    public function hapusSiswa(Siswa $siswa): void
    {
        DB::transaction(function () use ($siswa): void {
            $this->hapusDataNilai([$siswa->id]);
            $this->hapusFoto($siswa->photo_path);
            $siswa->delete();
        });
    }

    /**
     * Menghapus seluruh siswa satu tahun ajaran beserta data nilainya.
     *
     * Hanya siswa pada tahun ajaran yang diberikan yang dihapus sehingga data
     * tahun ajaran lain tidak ikut terpengaruh.
     *
     * @param  int  $tahunAjaranId  ID tahun ajaran
     * @return int Jumlah siswa yang dihapus
     */
    public function hapusSiswaTahunAjaran(int $tahunAjaranId): int
    {
        $total = 0;

        Siswa::query()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->chunkById(self::CHUNK_SIZE, function (Collection $siswas) use (&$total): void {
                DB::transaction(function () use ($siswas, &$total): void {
                    $siswaIds = $siswas->pluck('id')->all();

                    $this->hapusDataNilai($siswaIds);

                    foreach ($siswas as $siswa) {
                        $this->hapusFoto($siswa->photo_path);
                    }

                    Siswa::whereIn('id', $siswaIds)->delete();
                    $total += count($siswaIds);
                });
            });

        return $total;
    }

    /**
     * Menghapus seluruh data nilai milik siswa tertentu.
     *
     * @param  array<int, int>  $siswaIds  Daftar ID siswa
     */
    private function hapusDataNilai(array $siswaIds): void
    {
        Penilaian::whereIn('siswa_id', $siswaIds)->delete();
        RaporMetadata::whereIn('siswa_id', $siswaIds)->delete();
        TahfidzPenilaian::whereIn('siswa_id', $siswaIds)->delete();
        EkskulPenilaian::whereIn('siswa_id', $siswaIds)->delete();
    }

    /**
     * Menghapus foto siswa dari storage bila ada.
     *
     * @param  string|null  $photoPath  Path foto
     */
    private function hapusFoto(?string $photoPath): void
    {
        if ($photoPath) {
            Storage::disk(self::PHOTO_DISK)->delete($photoPath);
        }
    }

    /**
     * Menyusun pesan penolakan penghapusan.
     *
     * @param  string  $subjek  Subjek yang akan dihapus
     * @param  string  $rincian  Rincian data yang masih terhubung
     * @return string Pesan penolakan
     */
    private function pesanTolak(string $subjek, string $rincian): string
    {
        return __(':subjek tidak bisa dihapus karena masih tersimpan :rincian.', [
            'subjek' => $subjek,
            'rincian' => $rincian,
        ]);
    }

    /**
     * Memformat angka dengan pemisah ribuan mengikuti locale aplikasi.
     *
     * @param  int  $angka  Angka yang diformat
     * @return string Angka terformat
     */
    private function formatAngka(int $angka): string
    {
        return number_format($angka, 0, ',', '.');
    }
}
