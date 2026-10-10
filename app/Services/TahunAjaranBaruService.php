<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Kelas;
use App\Models\Mengajar;
use App\Models\MengajarTahfidz;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Memindahkan seluruh sekolah ke semester atau tahun ajaran berikutnya sekaligus.
 *
 * Dua mode yang didukung (ditentukan dari pasangan sumber dan tujuan):
 * - Lanjut semester: tahun ajaran yang sama, Ganjil ke Genap. Kelas (beserta wali),
 *   siswa aktif, dan jadwal mengajar disalin apa adanya.
 * - Kenaikan kelas: Genap ke Ganjil tahun ajaran berikutnya. Siswa naik satu tingkat,
 *   tinggal kelas, atau lulus; wali kelas tidak disalin; jadwal mengajar disalin dari
 *   kelas sumber yang namanya sama dengan kelas tujuan.
 *
 * Tahun ajaran sumber hanya dibaca. Tujuan wajib masih kosong.
 */
final class TahunAjaranBaruService
{
    public const MODE_LANJUT_SEMESTER = 'lanjut_semester';

    public const MODE_KENAIKAN_KELAS = 'kenaikan_kelas';

    public const PILIHAN_NAIK = 'naik';

    public const PILIHAN_TINGGAL = 'tinggal';

    public const PILIHAN_LULUS = 'lulus';

    /** @var list<string> */
    public const PILIHAN_SISWA = [self::PILIHAN_NAIK, self::PILIHAN_TINGGAL, self::PILIHAN_LULUS];

    /** @var int Panjang maksimum nama kelas (mengikuti kolom kelas.nama) */
    public const MAX_NAMA_KELAS = 50;

    /** @var int Panjang maksimum tingkat (mengikuti kolom kelas.tingkat) */
    private const MAX_TINGKAT = 20;

    /** @var array<int, string> */
    private const ROMAWI = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    /**
     * Angka romawi di awal teks. Harus diikuti akhir teks, bukan huruf, atau satu huruf
     * akhiran (mis. "VIIA"), supaya nama seperti "Ibnu Sina" tidak terbaca sebagai "I".
     */
    private const POLA_ROMAWI = '(XII|XI|X|IX|VIII|VII|VI|V|IV|III|II|I)(?=$|[^A-Za-z]|[A-Za-z](?:$|[^A-Za-z]))';

    /** @var string Awalan yang boleh mendahului tingkat pada nama kelas */
    private const POLA_AWALAN = '((?i:kelas|kls|tingkat)?\s*)';

    /**
     * Usulan tahun ajaran tujuan berikutnya dari tahun ajaran sumber.
     *
     * @return array{nama: string, tahun_mulai: int, tahun_selesai: int, semester: string}
     */
    public function usulanTujuan(TahunAjaran $sumber): array
    {
        $mulai = (int) $sumber->tahun_mulai;
        $selesai = (int) $sumber->tahun_selesai;

        if ($sumber->semester === 'Ganjil') {
            return ['nama' => (string) $sumber->nama, 'tahun_mulai' => $mulai, 'tahun_selesai' => $selesai, 'semester' => 'Genap'];
        }

        $nama = preg_match('/^\s*(\d{4})\s*\/\s*(\d{4})\s*$/', (string) $sumber->nama, $m) === 1
            ? ((int) $m[1] + 1).'/'.((int) $m[2] + 1)
            : ($mulai + 1).'/'.($selesai + 1);

        return ['nama' => $nama, 'tahun_mulai' => $mulai + 1, 'tahun_selesai' => $selesai + 1, 'semester' => 'Ganjil'];
    }

    /**
     * Usulan tujuan untuk setiap tahun ajaran, lengkap dengan ID tahun ajaran kosong yang cocok bila sudah ada.
     *
     * @param  Collection<int, TahunAjaran>  $tahunAjarans  Tahun ajaran dengan kelas_count, siswas_count, mengajars_count
     * @return array<int, array{nama: string, tahun_mulai: int, tahun_selesai: int, semester: string, tujuan_id: int|null}>
     */
    public function usulanSemua(Collection $tahunAjarans): array
    {
        $usulan = [];

        foreach ($tahunAjarans as $tahun) {
            $saran = $this->usulanTujuan($tahun);
            $cocok = $tahunAjarans->first(fn (TahunAjaran $t): bool => trim((string) $t->nama) === $saran['nama']
                && $t->semester === $saran['semester']
                && ! $this->berisiData($t));

            $usulan[$tahun->id] = $saran + ['tujuan_id' => $cocok?->id];
        }

        return $usulan;
    }

    /**
     * Menandai tahun ajaran yang sudah berisi kelas, siswa, atau jadwal mengajar (memakai *_count bila tersedia).
     */
    public function berisiData(TahunAjaran $tahun): bool
    {
        $hitungan = [
            $tahun->getAttribute('kelas_count'),
            $tahun->getAttribute('siswas_count'),
            $tahun->getAttribute('mengajars_count'),
        ];

        if (! in_array(null, $hitungan, true)) {
            return array_sum(array_map(intval(...), $hitungan)) > 0;
        }

        return $this->alasanTujuanTidakKosong($tahun) !== null;
    }

    /**
     * Memastikan sumber dan tujuan bisa diproses, lalu mengembalikan mode yang berlaku.
     *
     * @throws ValidationException Bila tujuan duplikat, kombinasi tidak didukung, sumber kosong, atau tujuan berisi data
     */
    public function periksa(TahunAjaran $sumber, TahunAjaran $tujuan): string
    {
        if (! $tujuan->exists && TahunAjaran::where('nama', $tujuan->nama)->where('semester', $tujuan->semester)->exists()) {
            throw ValidationException::withMessages([
                'tujuan_nama' => __('Tahun ajaran :tahun sudah ada. Pilih dari daftar tahun ajaran yang sudah ada.', ['tahun' => $tujuan->label()]),
            ]);
        }

        $mode = $this->tentukanMode($sumber, $tujuan);

        if (! Kelas::where('tahun_ajaran_id', $sumber->id)->exists()) {
            throw ValidationException::withMessages([
                'sumber_id' => __('Tahun ajaran sumber :tahun belum punya kelas, jadi tidak ada yang bisa disalin.', ['tahun' => $sumber->label()]),
            ]);
        }

        $alasan = $this->alasanTujuanTidakKosong($tujuan);

        if ($alasan !== null) {
            throw ValidationException::withMessages(['tujuan' => $alasan]);
        }

        return $mode;
    }

    /**
     * Menentukan mode dari pasangan sumber dan tujuan.
     *
     * @throws ValidationException Bila kombinasi bukan lanjut semester maupun kenaikan kelas
     */
    public function tentukanMode(TahunAjaran $sumber, TahunAjaran $tujuan): string
    {
        if ($tujuan->exists && $tujuan->id === $sumber->id) {
            throw ValidationException::withMessages([
                'tujuan' => __('Tahun ajaran tujuan harus berbeda dari tahun ajaran sumber.'),
            ]);
        }

        $namaSama = trim((string) $sumber->nama) === trim((string) $tujuan->nama);

        if ($namaSama && $sumber->semester === 'Ganjil' && $tujuan->semester === 'Genap') {
            return self::MODE_LANJUT_SEMESTER;
        }

        if (! $namaSama
            && $sumber->semester === 'Genap'
            && $tujuan->semester === 'Ganjil'
            && $this->tahunAwal($tujuan) === $this->tahunAwal($sumber) + 1) {
            return self::MODE_KENAIKAN_KELAS;
        }

        throw ValidationException::withMessages([
            'tujuan' => __('Dari :sumber ke :tujuan tidak didukung. Wizard hanya bisa melanjutkan semester pada tahun ajaran yang sama (Ganjil ke Genap, mis. 2026/2027 Ganjil ke 2026/2027 Genap) atau menaikkan kelas dari semester Genap ke semester Ganjil tahun ajaran berikutnya (mis. 2025/2026 Genap ke 2026/2027 Ganjil).', [
                'sumber' => $sumber->label(),
                'tujuan' => $tujuan->label(),
            ]),
        ]);
    }

    /**
     * Alasan tahun ajaran tujuan tidak bisa diisi wizard, atau null bila masih kosong.
     */
    public function alasanTujuanTidakKosong(TahunAjaran $tujuan): ?string
    {
        if (! $tujuan->exists) {
            return null;
        }

        $jumlah = [
            ':jumlah kelas' => Kelas::where('tahun_ajaran_id', $tujuan->id)->count(),
            ':jumlah siswa' => Siswa::where('tahun_ajaran_id', $tujuan->id)->count(),
            ':jumlah jadwal mengajar' => Mengajar::where('tahun_ajaran_id', $tujuan->id)->count(),
            ':jumlah penugasan tahfidz' => MengajarTahfidz::where('tahun_ajaran_id', $tujuan->id)->count(),
        ];

        $rincian = [];

        foreach ($jumlah as $teks => $banyak) {
            if ($banyak > 0) {
                $rincian[] = __($teks, ['jumlah' => $banyak]);
            }
        }

        if ($rincian === []) {
            return null;
        }

        return __('Tahun ajaran :tahun sudah berisi data (:rincian). Wizard hanya mengisi tahun ajaran yang masih kosong; untuk melengkapi data yang sudah ada gunakan Salin Kelas, Salin Rombel, atau Salin Jadwal Mengajar.', [
            'tahun' => $tujuan->label(),
            'rincian' => implode(', ', $rincian),
        ]);
    }

    /**
     * Ringkasan per kelas untuk pratinjau lanjut semester.
     *
     * @return array{kelas: array<int, array{nama: string, tingkat: string, wali: string|null, siswa_aktif: int, mengajar: int}>, siswa_nonaktif: int, siswa_tanpa_kelas: int}
     */
    public function ringkasanLanjut(TahunAjaran $sumber): array
    {
        $kelas = $this->urutkanKelas(
            Kelas::with('guru')
                ->where('tahun_ajaran_id', $sumber->id)
                ->withCount([
                    'siswas as siswa_aktif_count' => fn ($q) => $q->where('tahun_ajaran_id', $sumber->id)->where('is_active', true),
                    'mengajars as mengajar_sumber_count' => fn ($q) => $q->where('tahun_ajaran_id', $sumber->id)->where('semester', $sumber->semester),
                ])
                ->get()
        );

        return [
            'kelas' => $kelas->map(fn (Kelas $k): array => [
                'nama' => (string) $k->nama,
                'tingkat' => (string) $k->tingkat,
                'wali' => $k->guru?->nama,
                'siswa_aktif' => (int) $k->getAttribute('siswa_aktif_count'),
                'mengajar' => (int) $k->getAttribute('mengajar_sumber_count'),
            ])->values()->all(),
            'siswa_nonaktif' => Siswa::where('tahun_ajaran_id', $sumber->id)->where('is_active', false)->count(),
            'siswa_tanpa_kelas' => $this->jumlahSiswaTanpaKelas($sumber, $kelas),
        ];
    }

    /**
     * Rencana kenaikan kelas: kelas tujuan per kelas sumber dan pilihan tiap siswa aktif.
     *
     * Nama kelas tujuan dan pilihan siswa dari admin menimpa usulan bawaan; kunci yang tidak
     * dikenal (bukan kelas/siswa tahun ajaran sumber) diabaikan.
     *
     * @param  array<int|string, string|null>  $namaKelasTujuan  ID kelas sumber => nama kelas tujuan untuk siswa naik
     * @param  array<int|string, string|null>  $pilihanSiswa  ID siswa sumber => naik|tinggal|lulus
     * @return array{kelas: array<int, array{id: int, nama: string, tingkat: string, wali: string|null, tertinggi: bool, usulan_tujuan: string, tujuan: string, siswa: array<int, array{id: int, nis: string, nama: string, bawaan: string, pilihan: string}>}>, siswa_tanpa_kelas: int}
     */
    public function rencanaKenaikan(TahunAjaran $sumber, array $namaKelasTujuan = [], array $pilihanSiswa = []): array
    {
        return $this->susunRencana($sumber, $this->kelasDenganSiswaAktif($sumber), $namaKelasTujuan, $pilihanSiswa);
    }

    /**
     * @param  Collection<int, Kelas>  $kelasSumber  Kelas sumber beserta siswa aktifnya
     * @param  array<int|string, string|null>  $namaKelasTujuan
     * @param  array<int|string, string|null>  $pilihanSiswa
     * @return array{kelas: array<int, array{id: int, nama: string, tingkat: string, wali: string|null, tertinggi: bool, usulan_tujuan: string, tujuan: string, siswa: array<int, array{id: int, nis: string, nama: string, bawaan: string, pilihan: string}>}>, siswa_tanpa_kelas: int}
     */
    private function susunRencana(TahunAjaran $sumber, Collection $kelasSumber, array $namaKelasTujuan, array $pilihanSiswa): array
    {
        $tertinggi = $this->tingkatTertinggiPerJenis($kelasSumber);

        $kelas = $kelasSumber->map(function (Kelas $k) use ($tertinggi, $namaKelasTujuan, $pilihanSiswa): array {
            $level = $this->levelKelas($k);
            $palingAtas = $level !== null && $level === ($tertinggi[(string) $k->jenis] ?? null);
            $bawaan = $palingAtas ? self::PILIHAN_LULUS : self::PILIHAN_NAIK;
            $usulan = $this->namaNaik((string) $k->nama);

            return [
                'id' => $k->id,
                'nama' => (string) $k->nama,
                'tingkat' => (string) $k->tingkat,
                'wali' => $k->guru?->nama,
                'tertinggi' => $palingAtas,
                'usulan_tujuan' => $usulan,
                'tujuan' => array_key_exists($k->id, $namaKelasTujuan)
                    ? $this->rapikanNama((string) $namaKelasTujuan[$k->id])
                    : $usulan,
                'siswa' => $k->siswas->map(fn (Siswa $s): array => [
                    'id' => $s->id,
                    'nis' => (string) $s->nis,
                    'nama' => (string) $s->nama,
                    'bawaan' => $bawaan,
                    'pilihan' => in_array($pilihanSiswa[$s->id] ?? null, self::PILIHAN_SISWA, true)
                        ? (string) $pilihanSiswa[$s->id]
                        : $bawaan,
                ])->values()->all(),
            ];
        })->values()->all();

        return [
            'kelas' => $kelas,
            'siswa_tanpa_kelas' => $this->jumlahSiswaTanpaKelas($sumber, $kelasSumber),
        ];
    }

    /**
     * Menjalankan wizard dalam satu transaksi database.
     *
     * Tujuan yang belum tersimpan (tahun ajaran baru) dibuat di dalam transaksi yang sama,
     * sehingga kegagalan apa pun tidak meninggalkan data setengah jadi.
     *
     * @param  array<int|string, string|null>  $namaKelasTujuan  Khusus kenaikan kelas: ID kelas sumber => nama kelas tujuan
     * @param  array<int|string, string|null>  $pilihanSiswa  Khusus kenaikan kelas: ID siswa sumber => naik|tinggal|lulus
     * @return array{mode: string, sumber: string, tujuan: string, tujuan_id: int, tujuan_dibuat: bool, kelas_dibuat: int, siswa_disalin: int, siswa_tinggal: int, siswa_lulus: int, siswa_tanpa_kelas: int, mengajar_disalin: int, mengajar_tahfidz_disalin: int, diaktifkan: bool}
     *
     * @throws ValidationException Bila pemeriksaan gagal atau ada siswa naik ke kelas tujuan yang dikosongkan
     */
    public function jalankan(TahunAjaran $sumber, TahunAjaran $tujuan, bool $aktifkan, array $namaKelasTujuan = [], array $pilihanSiswa = []): array
    {
        return DB::transaction(function () use ($sumber, $tujuan, $aktifkan, $namaKelasTujuan, $pilihanSiswa): array {
            $mode = $this->periksa($sumber, $tujuan);
            $tujuanDibuat = ! $tujuan->exists;

            if ($tujuanDibuat) {
                $tujuan->is_active = false;
                $tujuan->save();
            }

            $hasil = $mode === self::MODE_LANJUT_SEMESTER
                ? $this->salinLanjutSemester($sumber, $tujuan)
                : $this->salinKenaikanKelas($sumber, $tujuan, $namaKelasTujuan, $pilihanSiswa);

            if ($aktifkan) {
                TahunAjaran::where('id', '!=', $tujuan->id)->update(['is_active' => false]);
                $tujuan->update(['is_active' => true]);
            }

            return [
                'mode' => $mode,
                'sumber' => $sumber->label(),
                'tujuan' => $tujuan->label(),
                'tujuan_id' => $tujuan->id,
                'tujuan_dibuat' => $tujuanDibuat,
                ...$hasil,
                'diaktifkan' => $aktifkan,
            ];
        });
    }

    /**
     * @return array{kelas_dibuat: int, siswa_disalin: int, siswa_tinggal: int, siswa_lulus: int, siswa_tanpa_kelas: int, mengajar_disalin: int, mengajar_tahfidz_disalin: int}
     */
    private function salinLanjutSemester(TahunAjaran $sumber, TahunAjaran $tujuan): array
    {
        $kelasSumber = $this->kelasDenganSiswaAktif($sumber);

        /** @var array<int, int> $petaKelas ID kelas sumber => ID kelas tujuan */
        $petaKelas = [];
        $siswaDisalin = 0;

        foreach ($kelasSumber as $kelas) {
            $baru = Kelas::create([
                'tahun_ajaran_id' => $tujuan->id,
                'nama' => $kelas->nama,
                'tingkat' => $kelas->tingkat,
                'jurusan' => $kelas->jurusan,
                'jenis' => $kelas->jenis,
                'guru_id' => $kelas->guru_id,
            ]);

            $petaKelas[$kelas->id] = $baru->id;

            foreach ($kelas->siswas as $siswa) {
                $this->salinSiswa($siswa, $tujuan, $baru->id);
                $siswaDisalin++;
            }
        }

        return [
            'kelas_dibuat' => count($petaKelas),
            'siswa_disalin' => $siswaDisalin,
            'siswa_tinggal' => 0,
            'siswa_lulus' => 0,
            'siswa_tanpa_kelas' => $this->jumlahSiswaTanpaKelas($sumber, $kelasSumber),
            'mengajar_disalin' => $this->salinMengajar($sumber, $tujuan, $petaKelas),
            'mengajar_tahfidz_disalin' => $this->salinMengajarTahfidz($sumber, $tujuan, $petaKelas),
        ];
    }

    /**
     * @param  array<int|string, string|null>  $namaKelasTujuan
     * @param  array<int|string, string|null>  $pilihanSiswa
     * @return array{kelas_dibuat: int, siswa_disalin: int, siswa_tinggal: int, siswa_lulus: int, siswa_tanpa_kelas: int, mengajar_disalin: int, mengajar_tahfidz_disalin: int}
     */
    private function salinKenaikanKelas(TahunAjaran $sumber, TahunAjaran $tujuan, array $namaKelasTujuan, array $pilihanSiswa): array
    {
        $kelasSumber = $this->kelasDenganSiswaAktif($sumber);
        $rencana = $this->susunRencana($sumber, $kelasSumber, $namaKelasTujuan, $pilihanSiswa);
        $this->pastikanKelasTujuanTerisi($rencana['kelas']);

        $kelasSumber = $kelasSumber->keyBy('id');

        /** @var array<string, Kelas> $kelasTujuan Kunci nama => kelas tujuan yang sudah dibuat */
        $kelasTujuan = [];
        $hitung = ['disalin' => 0, 'tinggal' => 0, 'lulus' => 0];

        foreach ($rencana['kelas'] as $rencanaKelas) {
            /** @var Kelas $asal */
            $asal = $kelasSumber->get($rencanaKelas['id']);
            $siswaAsal = $asal->siswas->keyBy('id');

            foreach ($rencanaKelas['siswa'] as $rencanaSiswa) {
                if ($rencanaSiswa['pilihan'] === self::PILIHAN_LULUS) {
                    $hitung['lulus']++;

                    continue;
                }

                $naik = $rencanaSiswa['pilihan'] === self::PILIHAN_NAIK;
                $nama = $naik ? $rencanaKelas['tujuan'] : (string) $asal->nama;
                $kunci = $this->kunciNama($nama);

                $kelasTujuan[$kunci] ??= Kelas::create([
                    'tahun_ajaran_id' => $tujuan->id,
                    'nama' => $nama,
                    'tingkat' => $this->tingkatTujuan($asal, $nama, $naik),
                    'jurusan' => $asal->jurusan,
                    'jenis' => $asal->jenis,
                    'guru_id' => null,
                ]);

                /** @var Siswa $siswa */
                $siswa = $siswaAsal->get($rencanaSiswa['id']);
                $this->salinSiswa($siswa, $tujuan, $kelasTujuan[$kunci]->id);
                $hitung['disalin']++;

                if (! $naik) {
                    $hitung['tinggal']++;
                }
            }
        }

        // Guru mapel biasanya tetap di tingkatnya: jadwal kelas tujuan diambil dari kelas sumber bernama sama.
        $sumberPerNama = $kelasSumber->keyBy(fn (Kelas $k): string => $this->kunciNama((string) $k->nama));
        $petaKelas = [];

        foreach ($kelasTujuan as $kunci => $kelas) {
            $asal = $sumberPerNama->get($kunci);

            if ($asal instanceof Kelas) {
                $petaKelas[$asal->id] = $kelas->id;
            }
        }

        return [
            'kelas_dibuat' => count($kelasTujuan),
            'siswa_disalin' => $hitung['disalin'],
            'siswa_tinggal' => $hitung['tinggal'],
            'siswa_lulus' => $hitung['lulus'],
            'siswa_tanpa_kelas' => $rencana['siswa_tanpa_kelas'],
            'mengajar_disalin' => $this->salinMengajar($sumber, $tujuan, $petaKelas),
            'mengajar_tahfidz_disalin' => $this->salinMengajarTahfidz($sumber, $tujuan, $petaKelas),
        ];
    }

    /**
     * @param  array<int, array{id: int, nama: string, tujuan: string, siswa: array<int, array{pilihan: string}>}>  $rencanaKelas
     *
     * @throws ValidationException
     */
    private function pastikanKelasTujuanTerisi(array $rencanaKelas): void
    {
        $pesan = [];

        foreach ($rencanaKelas as $kelas) {
            $adaYangNaik = collect($kelas['siswa'])->contains(fn (array $s): bool => $s['pilihan'] === self::PILIHAN_NAIK);

            if ($adaYangNaik && $kelas['tujuan'] === '') {
                $pesan['kelas_tujuan.'.$kelas['id']] = __('Isi nama kelas tujuan untuk siswa naik dari kelas :kelas.', ['kelas' => $kelas['nama']]);
            } elseif (mb_strlen($kelas['tujuan']) > self::MAX_NAMA_KELAS) {
                $pesan['kelas_tujuan.'.$kelas['id']] = __('Nama kelas tujuan dari kelas :kelas maksimal :max karakter.', ['kelas' => $kelas['nama'], 'max' => self::MAX_NAMA_KELAS]);
            }
        }

        if ($pesan !== []) {
            throw ValidationException::withMessages($pesan);
        }
    }

    /**
     * Menyalin data siswa ke tahun ajaran tujuan (berkas foto dipakai bersama, sama seperti Salin Rombel).
     */
    private function salinSiswa(Siswa $siswa, TahunAjaran $tujuan, int $kelasId): void
    {
        $data = Arr::only($siswa->getAttributes(), $siswa->getFillable());
        $data['tahun_ajaran_id'] = $tujuan->id;
        $data['kelas_id'] = $kelasId;
        $data['is_active'] = true;

        Siswa::create($data);
    }

    /**
     * Menyalin jadwal mengajar kelas sumber ke kelas tujuan pasangannya.
     *
     * @param  array<int, int>  $petaKelas  ID kelas sumber => ID kelas tujuan
     */
    private function salinMengajar(TahunAjaran $sumber, TahunAjaran $tujuan, array $petaKelas): int
    {
        if ($petaKelas === []) {
            return 0;
        }

        $jadwal = Mengajar::where('tahun_ajaran_id', $sumber->id)
            ->where('semester', $sumber->semester)
            ->whereIn('kelas_id', array_keys($petaKelas))
            ->orderBy('id')
            ->get();

        foreach ($jadwal as $record) {
            $atribut = [
                'tahun_ajaran_id' => $tujuan->id,
                'semester' => $tujuan->semester,
                'kelas_id' => $petaKelas[$record->kelas_id],
                'mata_pelajaran_id' => $record->mata_pelajaran_id,
                'guru_id' => $record->guru_id,
                'jtm' => $record->getAttribute('jtm'),
            ];

            // Kolom bobot tidak boleh null (punya nilai bawaan), jadi hanya disalin bila terisi.
            if ($record->bobot_sumatif !== null) {
                $atribut['bobot_sumatif'] = $record->bobot_sumatif;
            }

            if ($record->bobot_sts !== null) {
                $atribut['bobot_sts'] = $record->bobot_sts;
            }

            Mengajar::create($atribut);
        }

        return $jadwal->count();
    }

    /**
     * @param  array<int, int>  $petaKelas  ID kelas sumber => ID kelas tujuan
     */
    private function salinMengajarTahfidz(TahunAjaran $sumber, TahunAjaran $tujuan, array $petaKelas): int
    {
        if ($petaKelas === []) {
            return 0;
        }

        $penugasan = MengajarTahfidz::where('tahun_ajaran_id', $sumber->id)
            ->where('semester', $sumber->semester)
            ->whereIn('kelas_id', array_keys($petaKelas))
            ->orderBy('id')
            ->get();

        foreach ($penugasan as $record) {
            MengajarTahfidz::create([
                'tahun_ajaran_id' => $tujuan->id,
                'semester' => $tujuan->semester,
                'kelas_id' => $petaKelas[$record->kelas_id],
                'guru_id' => $record->guru_id,
            ]);
        }

        return $penugasan->count();
    }

    /**
     * Kelas tahun ajaran sumber beserta siswa aktifnya, urut tingkat lalu nama.
     *
     * @return Collection<int, Kelas>
     */
    private function kelasDenganSiswaAktif(TahunAjaran $sumber): Collection
    {
        return $this->urutkanKelas(
            Kelas::with([
                'guru',
                'siswas' => fn ($q) => $q->where('tahun_ajaran_id', $sumber->id)->where('is_active', true)->orderBy('nama')->orderBy('id'),
            ])
                ->where('tahun_ajaran_id', $sumber->id)
                ->get()
        );
    }

    /**
     * @param  Collection<int, Kelas>  $kelas
     * @return Collection<int, Kelas>
     */
    private function urutkanKelas(Collection $kelas): Collection
    {
        return $kelas
            ->sortBy(fn (Kelas $k): string => sprintf('%03d|%s', $this->levelKelas($k) ?? 999, Str::upper((string) $k->nama)))
            ->values();
    }

    /**
     * Siswa aktif sumber yang tidak berada di kelas mana pun pada tahun ajaran sumber (tidak ikut disalin).
     *
     * @param  Collection<int, Kelas>  $kelasSumber
     */
    private function jumlahSiswaTanpaKelas(TahunAjaran $sumber, Collection $kelasSumber): int
    {
        return Siswa::where('tahun_ajaran_id', $sumber->id)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('kelas_id')->orWhereNotIn('kelas_id', $kelasSumber->modelKeys()))
            ->count();
    }

    /**
     * Tingkat tertinggi per jenis kelas (MI dan SMP dihitung terpisah bila jenisnya diisi).
     *
     * @param  Collection<int, Kelas>  $kelas
     * @return array<string, int>
     */
    private function tingkatTertinggiPerJenis(Collection $kelas): array
    {
        $tertinggi = [];

        foreach ($kelas as $k) {
            $level = $this->levelKelas($k);

            if ($level !== null) {
                $jenis = (string) $k->jenis;
                $tertinggi[$jenis] = max($tertinggi[$jenis] ?? 0, $level);
            }
        }

        return $tertinggi;
    }

    /**
     * Tingkat kelas sebagai angka, dari kolom tingkat ("I", "1", "Kelas 1") atau dari nama kelas ("1A", "VII B").
     */
    private function levelKelas(Kelas $kelas): ?int
    {
        return ($this->uraiTingkat($kelas->tingkat) ?? $this->uraiTingkat($kelas->nama))['level'] ?? null;
    }

    /**
     * @return array{level: int, romawi: bool}|null
     */
    private function uraiTingkat(?string $teks): ?array
    {
        $teks = trim((string) preg_replace('/^'.self::POLA_AWALAN.'/', '', trim((string) $teks)));

        if (preg_match('/^(\d+)/', $teks, $m) === 1) {
            return ['level' => (int) $m[1], 'romawi' => false];
        }

        if (preg_match('/^'.self::POLA_ROMAWI.'/', $teks, $m) === 1) {
            return ['level' => (int) array_search($m[1], self::ROMAWI, true), 'romawi' => true];
        }

        return null;
    }

    /**
     * Usulan nama kelas naik satu tingkat dengan akhiran tetap: "1A" → "2A", "VII B" → "VIII B".
     *
     * Mengembalikan string kosong bila tingkat tidak terbaca dari nama (admin mengisi sendiri).
     */
    private function namaNaik(string $nama): string
    {
        if (preg_match('/^'.self::POLA_AWALAN.'(\d+)(.*)$/su', $nama, $m) === 1) {
            return $this->rapikanNama($m[1].((int) $m[2] + 1).$m[3]);
        }

        if (preg_match('/^'.self::POLA_AWALAN.self::POLA_ROMAWI.'(.*)$/su', $nama, $m) === 1) {
            $level = (int) array_search($m[2], self::ROMAWI, true) + 1;

            return $this->rapikanNama($m[1].(self::ROMAWI[$level] ?? (string) $level).$m[3]);
        }

        return '';
    }

    /**
     * Tingkat kelas tujuan: dibaca dari nama kelas tujuan, ditulis dengan gaya tingkat kelas sumber (romawi/angka).
     */
    private function tingkatTujuan(Kelas $asal, string $namaTujuan, bool $naik): string
    {
        $tingkatAsal = $this->uraiTingkat($asal->tingkat);
        $levelAsal = $this->levelKelas($asal);

        $level = $this->uraiTingkat($namaTujuan)['level']
            ?? ($levelAsal !== null ? $levelAsal + ($naik ? 1 : 0) : null);

        if ($level === null) {
            return (string) $asal->tingkat;
        }

        $romawi = $tingkatAsal['romawi'] ?? true;
        $tingkat = $romawi && isset(self::ROMAWI[$level]) ? self::ROMAWI[$level] : (string) $level;

        return mb_substr($tingkat, 0, self::MAX_TINGKAT);
    }

    /**
     * Tahun awal tahun ajaran: dari nama ("2025/2026" → 2025), cadangannya kolom tahun_mulai.
     */
    private function tahunAwal(TahunAjaran $tahun): int
    {
        return preg_match('/(\d{4})/', (string) $tahun->nama, $m) === 1
            ? (int) $m[1]
            : (int) $tahun->tahun_mulai;
    }

    private function rapikanNama(string $nama): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $nama));
    }

    /**
     * Kunci pencocokan nama kelas antar tahun ajaran ("Kelas 2-A", "2a", dan "2A" dianggap sama).
     */
    private function kunciNama(string $nama): string
    {
        return Str::of($nama)
            ->lower()
            ->replaceMatches('/kelas/', '')
            ->replaceMatches('/[^a-z0-9]/', '')
            ->value();
    }
}
