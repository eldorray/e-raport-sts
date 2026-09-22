<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model untuk entitas Guru.
 *
 * Merepresentasikan data guru dalam sistem rapor.
 *
 * @property int $id
 * @property int $user_id
 * @property string $nama
 * @property string $nip
 * @property string|null $nik
 * @property string $jenis_kelamin
 * @property string|null $tempat_lahir
 * @property \Carbon\Carbon|null $tanggal_lahir
 * @property string|null $pendidikan
 * @property string|null $wali_kelas
 * @property int|null $jtm
 * @property string|null $initial_password
 * @property string|null $foto_path
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User|null $user
 * @property-read Kelas|null $kelasWali
 */
class Guru extends Model
{
    /** @use HasFactory<\Database\Factories\GuruFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nama',
        'nip',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'pendidikan',
        'wali_kelas',
        'jtm',
        'initial_password',
        'foto_path',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Mendapatkan user account yang terkait dengan guru.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan kelas yang diwali oleh guru.
     *
     * @return HasOne<Kelas, $this>
     */
    public function kelasWali(): HasOne
    {
        return $this->hasOne(Kelas::class);
    }

    /**
     * Mendapatkan jadwal mengajar guru.
     *
     * @return HasMany<Mengajar, $this>
     */
    public function mengajars(): HasMany
    {
        return $this->hasMany(Mengajar::class);
    }

    /**
     * Mendapatkan nilai yang diinput guru.
     *
     * @return HasMany<Penilaian, $this>
     */
    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }
}
