<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'nama',
        'tahun_mulai',
        'tahun_selesai',
        'semester',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Nilai rapor pada tahun ajaran ini.
     *
     * @return HasMany<Penilaian, $this>
     */
    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }

    /**
     * Metadata rapor (absensi, catatan wali) pada tahun ajaran ini.
     *
     * @return HasMany<RaporMetadata, $this>
     */
    public function raporMetadatas(): HasMany
    {
        return $this->hasMany(RaporMetadata::class);
    }

    /**
     * Penilaian tahfidz pada tahun ajaran ini.
     *
     * @return HasMany<TahfidzPenilaian, $this>
     */
    public function tahfidzPenilaians(): HasMany
    {
        return $this->hasMany(TahfidzPenilaian::class);
    }

    /**
     * Jadwal mengajar pada tahun ajaran ini.
     *
     * @return HasMany<Mengajar, $this>
     */
    public function mengajars(): HasMany
    {
        return $this->hasMany(Mengajar::class);
    }

    /**
     * Siswa yang terdaftar pada tahun ajaran ini.
     *
     * @return HasMany<Siswa, $this>
     */
    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    /**
     * Kelas pada tahun ajaran ini.
     *
     * @return HasMany<Kelas, $this>
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }
}
