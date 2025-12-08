<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EkskulPenilaian extends Model
{
    use HasFactory;

    protected $fillable = [
        'ekskul_id',
        'guru_id',
        'siswa_id',
        'tahun_ajaran_id',
        'semester',
        'nilai',
        'catatan',
    ];

    public function ekskul(): BelongsTo
    {
        return $this->belongsTo(Ekskul::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
