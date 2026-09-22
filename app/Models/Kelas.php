<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model untuk entitas Kelas (rombongan belajar).
 *
 * @property int $id
 * @property string $nama
 * @property string|null $tingkat
 * @property int|null $guru_id
 * @property int|null $tahun_ajaran_id
 * @property-read \App\Models\Guru|null $guru
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Siswa> $siswas
 */
class Kelas extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama',
        'tingkat',
        'jurusan',
        'jenis',
        'guru_id',
        'tahun_ajaran_id',
    ];

    /**
     * @return BelongsTo<Guru, $this>
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * @return HasMany<Siswa, $this>
     */
    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    /**
     * @return HasMany<Mengajar, $this>
     */
    public function mengajars(): HasMany
    {
        return $this->hasMany(Mengajar::class);
    }

    /**
     * @return HasMany<Penilaian, $this>
     */
    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }

    /**
     * @return HasMany<RaporMetadata, $this>
     */
    public function raporMetadatas(): HasMany
    {
        return $this->hasMany(RaporMetadata::class);
    }

    /**
     * @return BelongsTo<TahunAjaran, $this>
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
