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

    /**
     * Mendapatkan predikat berdasarkan nilai.
     * 
     * A (Sangat Baik): >= 85
     * B (Baik): 70 - 84
     * C (Cukup): 50 - 69
     * D (Kurang): < 50
     */
    public function getPredikatAttribute(): ?string
    {
        if ($this->nilai === null) {
            return null;
        }

        $nilai = (float) $this->nilai;

        if ($nilai >= 85) {
            return 'A';
        }
        if ($nilai >= 70) {
            return 'B';
        }
        if ($nilai >= 50) {
            return 'C';
        }
        return 'D';
    }

    /**
     * Mendapatkan keterangan predikat.
     */
    public function getPredikatKeteranganAttribute(): ?string
    {
        $predikat = $this->predikat;

        return match ($predikat) {
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            'D' => 'Kurang',
            default => null,
        };
    }
}
