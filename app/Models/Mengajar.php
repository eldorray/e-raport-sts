<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Model untuk entitas Mengajar (penugasan guru mengajar mapel di kelas).
 *
 * @property int $id
 * @property int $tahun_ajaran_id
 * @property string $semester
 * @property int $kelas_id
 * @property int $mata_pelajaran_id
 * @property int|null $guru_id
 * @property float|null $bobot_sumatif
 * @property float|null $bobot_sts
 * @property int $penilaian_target
 * @property int $penilaian_filled
 * @property bool $penilaian_done
 * @property-read \App\Models\Kelas|null $kelas
 * @property-read \App\Models\MataPelajaran|null $mataPelajaran
 * @property-read \App\Models\Guru|null $guru
 * @property-read \App\Models\TahunAjaran|null $tahunAjaran
 */
class Mengajar extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'tahun_ajaran_id',
        'semester',
        'kelas_id',
        'mata_pelajaran_id',
        'guru_id',
        'jtm',
        'bobot_sumatif',
        'bobot_sts',
    ];

    /**
     * Boot the model and auto-generate UUID on creating.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Get the route key name for Laravel route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return BelongsTo<TahunAjaran, $this>
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * @return BelongsTo<Kelas, $this>
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * @return BelongsTo<MataPelajaran, $this>
     */
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    /**
     * @return BelongsTo<Guru, $this>
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * @return HasMany<Penilaian, $this>
     */
    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }
}
