<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\TahunAjaran;


class Siswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun_ajaran_id',
        'nis',
        'nisn',
        'kelas_id',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'status_keluarga',
        'anak_ke',
        'telpon',
        'alamat',
        'sekolah_asal',
        'tanggal_diterima',
        'kelas_diterima',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'alamat_orang_tua',
        'nama_wali',
        'pekerjaan_wali',
        'alamat_wali',
        'photo_path',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_diterima' => 'date',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return Storage::disk('public')->url($this->photo_path);
        }

        return asset('images/default-siswa.svg');
    }

    public function getInitialsAttribute(): string
    {
        return Str::of($this->nama ?? 'S')->explode(' ')->map(fn ($part) => Str::substr($part, 0, 1))->implode('');
    }
}
