<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Model untuk entitas Siswa.
 *
 * Merepresentasikan data siswa dalam sistem rapor.
 *
 * @property int $id
 * @property int $tahun_ajaran_id
 * @property string $nis
 * @property string|null $nisn
 * @property int|null $kelas_id
 * @property string $nama
 * @property string $jenis_kelamin
 * @property string|null $tempat_lahir
 * @property \Carbon\Carbon|null $tanggal_lahir
 * @property string|null $agama
 * @property string|null $status_keluarga
 * @property int|null $anak_ke
 * @property string|null $telpon
 * @property string|null $alamat
 * @property string|null $sekolah_asal
 * @property \Carbon\Carbon|null $tanggal_diterima
 * @property string|null $kelas_diterima
 * @property string|null $nama_ayah
 * @property string|null $nama_ibu
 * @property string|null $pekerjaan_ayah
 * @property string|null $pekerjaan_ibu
 * @property string|null $alamat_orang_tua
 * @property string|null $nama_wali
 * @property string|null $pekerjaan_wali
 * @property string|null $alamat_wali
 * @property string|null $photo_path
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Kelas|null $kelas
 * @property-read TahunAjaran $tahunAjaran
 * @property-read string $photo_url
 * @property-read string $initials
 */
class Siswa extends Model
{
    use HasFactory;

    /** @var string Disk storage untuk foto */
    private const PHOTO_DISK = 'public';

    /** @var string Path default foto siswa */
    private const DEFAULT_PHOTO_PATH = 'images/default-siswa.svg';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_diterima' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Mendapatkan kelas yang ditempati siswa.
     *
     * @return BelongsTo<Kelas, Siswa>
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Mendapatkan tahun ajaran siswa.
     *
     * @return BelongsTo<TahunAjaran, Siswa>
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Mendapatkan URL foto siswa.
     *
     * Mengembalikan foto default jika tidak ada foto yang di-upload.
     *
     * @return string URL foto siswa
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path && Storage::disk(self::PHOTO_DISK)->exists($this->photo_path)) {
            return Storage::disk(self::PHOTO_DISK)->url($this->photo_path);
        }

        return asset(self::DEFAULT_PHOTO_PATH);
    }

    /**
     * Mendapatkan inisial nama siswa.
     *
     * @return string Inisial dari nama siswa
     */
    public function getInitialsAttribute(): string
    {
        return Str::of($this->nama ?? 'S')
            ->explode(' ')
            ->map(fn ($part) => Str::substr($part, 0, 1))
            ->implode('');
    }
}
