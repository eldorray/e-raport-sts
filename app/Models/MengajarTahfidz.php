<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk penugasan guru mengajar Tahfidz.
 *
 * @property int $id
 * @property int $tahun_ajaran_id
 * @property string|null $semester
 * @property int $kelas_id
 * @property int|null $guru_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read TahunAjaran $tahunAjaran
 * @property-read Kelas $kelas
 * @property-read Guru|null $guru
 */
class MengajarTahfidz extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun_ajaran_id',
        'semester',
        'kelas_id',
        'guru_id',
    ];

    /**
     * Mendapatkan tahun ajaran.
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Mendapatkan kelas.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Mendapatkan guru yang mengajar.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
