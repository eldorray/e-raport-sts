<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajarans';

    protected $fillable = [
        'kode',
        'nama_mapel',
        'jumlah_jam',
        'kelompok',
        'jurusan',
        'urutan',
    ];

    /**
     * Jadwal mengajar yang memakai mata pelajaran ini.
     *
     * @return HasMany<Mengajar, $this>
     */
    public function mengajars(): HasMany
    {
        return $this->hasMany(Mengajar::class);
    }

    /**
     * Nilai yang tersimpan untuk mata pelajaran ini.
     *
     * @return HasMany<Penilaian, $this>
     */
    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }
}
