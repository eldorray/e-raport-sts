<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaporMetadata extends Model
{
    protected $table = 'rapor_metadatas';
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

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function wali(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'wali_guru_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
