<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk entitas Penilaian.
 *
 * Merepresentasikan data penilaian siswa per mata pelajaran dalam sistem rapor.
 * Menyimpan nilai sumatif, STS, dan materi/TP yang dinilai.
 *
 * @property int $id
 * @property int $tahun_ajaran_id
 * @property string $semester
 * @property int $kelas_id
 * @property int $siswa_id
 * @property int $mata_pelajaran_id
 * @property int $guru_id
 * @property int $mengajar_id
 * @property string|null $materi_tp
 * @property float|null $nilai_sumatif
 * @property float|null $nilai_sts
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read TahunAjaran $tahunAjaran
 * @property-read Kelas $kelas
 * @property-read Siswa $siswa
 * @property-read MataPelajaran $mataPelajaran
 * @property-read Guru $guru
 * @property-read Mengajar $mengajar
 */
class Penilaian extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tahun_ajaran_id',
        'semester',
        'kelas_id',
        'siswa_id',
        'mata_pelajaran_id',
        'guru_id',
        'mengajar_id',
        'materi_tp',
        'nilai_sumatif',
        'nilai_sts',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'nilai_sumatif' => 'float',
        'nilai_sts' => 'float',
    ];

    /**
     * Mendapatkan tahun ajaran penilaian.
     *
     * @return BelongsTo<TahunAjaran, Penilaian>
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Mendapatkan kelas tempat penilaian.
     *
     * @return BelongsTo<Kelas, Penilaian>
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Mendapatkan siswa yang dinilai.
     *
     * @return BelongsTo<Siswa, Penilaian>
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Mendapatkan mata pelajaran penilaian.
     *
     * @return BelongsTo<MataPelajaran, Penilaian>
     */
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    /**
     * Mendapatkan guru penilai.
     *
     * @return BelongsTo<Guru, Penilaian>
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * Mendapatkan mengajar terkait penilaian.
     *
     * @return BelongsTo<Mengajar, Penilaian>
     */
    public function mengajar(): BelongsTo
    {
        return $this->belongsTo(Mengajar::class);
    }
}
