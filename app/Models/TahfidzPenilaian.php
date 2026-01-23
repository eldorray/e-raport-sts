<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk entitas Penilaian Tahfidz Al-Qur'an.
 *
 * @property int $id
 * @property int $siswa_id
 * @property int $tahun_ajaran_id
 * @property string $semester
 * @property int|null $pembimbing_id
 * @property string|null $predikat_adab
 * @property string|null $deskripsi_adab
 * @property string|null $predikat_tajwid
 * @property string|null $deskripsi_tajwid
 * @property string|null $predikat_makhorijul
 * @property string|null $deskripsi_makhorijul
 * @property array|null $surah_hafalan
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Siswa $siswa
 * @property-read TahunAjaran $tahunAjaran
 * @property-read Guru|null $pembimbing
 * @property-read string $deskripsi
 */
class TahfidzPenilaian extends Model
{
    use HasFactory;

    /**
     * Daftar 38 Surah Juz 'Amma (Juz 30)
     */
    public const SURAH_LIST = [
        'al-fatihah' => 'Q.S. Al-Fatihah',
        'an-nas' => 'Q.S. An-Nas',
        'al-falaq' => 'Q.S. Al-Falaq',
        'al-ikhlas' => 'Q.S. Al-Ikhlas',
        'al-lahab' => 'Q.S. Al-Lahab',
        'an-nashr' => 'Q.S An-Nashr',
        'al-kafirun' => 'Q.S. Al-Kafirun',
        'al-kautsar' => 'Q.S. Al-Kautsar',
        'al-maun' => 'Q.S. Al-Ma\'un',
        'al-quraisy' => 'Q.S. Al-Quraisy',
        'al-fil' => 'Q.S. Al-Fil',
        'al-humazah' => 'Q.S Al-Humazah',
        'al-ashr' => 'Q.S. Al-Ashr',
        'at-takatsur' => 'Q.S. At-Takatsur',
        'al-qariah' => 'Q.S. Al-Qari\'ah',
        'al-adiyat' => 'Q.S. Al-Adiyat',
        'al-zalzalah' => 'Q.S. Al-Zalzalah',
        'al-bayyinah' => 'Q.S. Al-Bayyinah',
        'al-qadr' => 'Q.S. Al-Qadr',
        'al-alaq' => 'Q.S. Al-Alaq',
        'at-tin' => 'Q.S. At-Tin',
        'al-insyirah' => 'Q.S. Al-Insyirah',
        'ad-dhuha' => 'Q.S. Ad-Dhuhaa',
        'al-lail' => 'Q.S. Al-Lail',
        'asy-syams' => 'Q.S. Asy-Syams',
        'al-balad' => 'Q.S. Al-Balad',
        'al-fajr' => 'Q.S. Al-Fajr',
        'al-ghasyiyah' => 'Q.S. Al-Ghasyiyah',
        'al-ala' => 'Q.S. Al-A\'laa',
        'ath-thariq' => 'Q.S. Ath-Thariq',
        'al-buruj' => 'Q.S. Al-Buruj',
        'al-insyiqaq' => 'Q.S. Al-Insyiqaaq',
        'al-muthaffifin' => 'Q.S. Al-Muthaffifiin',
        'al-infithar' => 'Q.S. Al-Infithaar',
        'at-takwir' => 'Q.S. At-Takwir',
        'abasa' => 'Q.S. Abasa',
        'an-naziat' => 'Q.S. An-Naazi\'aat',
        'an-naba' => 'Q.S. An-Naba',
    ];

    /**
     * Mapping predikat ke deskripsi
     */
    public const PREDIKAT_MAP = [
        'A' => 'Sangat Baik',
        'B' => 'Baik',
        'C' => 'Cukup',
        'D' => 'Kurang',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'siswa_id',
        'tahun_ajaran_id',
        'semester',
        'pembimbing_id',
        'predikat_adab',
        'deskripsi_adab',
        'predikat_tajwid',
        'deskripsi_tajwid',
        'predikat_makhorijul',
        'deskripsi_makhorijul',
        'surah_hafalan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'surah_hafalan' => 'array',
    ];

    /**
     * Mendapatkan siswa yang dinilai.
     *
     * @return BelongsTo<Siswa, TahfidzPenilaian>
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Mendapatkan tahun ajaran.
     *
     * @return BelongsTo<TahunAjaran, TahfidzPenilaian>
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Mendapatkan guru pembimbing tahfidz.
     *
     * @return BelongsTo<Guru, TahfidzPenilaian>
     */
    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'pembimbing_id');
    }

    /**
     * Mendapatkan deskripsi dinamis berdasarkan jumlah surah yang dihafal.
     *
     * @return string
     */
    public function getDeskripsiAttribute(): string
    {
        $count = count($this->surah_hafalan ?? []);

        return "Alhamdulillah saat ini sebanyak {$count} Surah di Juz 30 sudah Ananda hafal. Tingkatkan terus semangat menghafalnya. Untuk beberapa surah yang diceklis masih perlu diperbaiki kelancaran dan fahohahdnya. Seringlah muroja'ah hafalannya dengan disimak orang tua supaya bacaannya lebih baik. Semoga Allah mudahkan. Aamiin.";
    }

    /**
     * Mendapatkan jumlah surah yang dihafal.
     *
     * @return int
     */
    public function getJumlahSurahAttribute(): int
    {
        return count($this->surah_hafalan ?? []);
    }

    /**
     * Mengecek apakah surah tertentu sudah dihafal.
     *
     * @param string $surahKey
     * @return bool
     */
    public function hasSurah(string $surahKey): bool
    {
        return in_array($surahKey, $this->surah_hafalan ?? []);
    }
}
