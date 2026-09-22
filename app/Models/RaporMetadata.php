<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk metadata rapor siswa per semester.
 *
 * @property int $id
 * @property int $tahun_ajaran_id
 * @property string $semester
 * @property int $siswa_id
 * @property int|null $kelas_id
 * @property int|null $wali_guru_id
 * @property int|null $sakit
 * @property int|null $izin
 * @property int|null $alpa
 * @property string|null $catatan_wali
 * @property string|null $tanggapan_ortu
 * @property array<int, array{jenis: string, keterangan: string|null}>|null $prestasi
 * @property \Carbon\Carbon|null $tanggal_rapor
 */
class RaporMetadata extends Model
{
    protected $table = 'rapor_metadatas';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'tahun_ajaran_id',
        'semester',
        'siswa_id',
        'kelas_id',
        'wali_guru_id',
        'sakit',
        'izin',
        'alpa',
        'catatan_wali',
        'tanggapan_ortu',
        'prestasi',
        'tanggal_rapor',
    ];

    protected $casts = [
        'prestasi' => 'array',
        'tanggal_rapor' => 'date',
    ];

    /**
     * @return BelongsTo<Siswa, $this>
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * @return BelongsTo<Kelas, $this>
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * @return BelongsTo<Guru, $this>
     */
    public function wali(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'wali_guru_id');
    }

    /**
     * @return BelongsTo<TahunAjaran, $this>
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
