<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ekskul extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'guru_id',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(EkskulPenilaian::class);
    }
}
