<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penilaian extends Model
{
    use HasFactory;

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

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function mengajar(): BelongsTo
    {
        return $this->belongsTo(Mengajar::class);
    }
}
