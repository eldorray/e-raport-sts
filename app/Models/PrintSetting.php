<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk pengaturan cetak rapor.
 *
 * @property int $id
 * @property string|null $nama_yayasan
 * @property string|null $tempat_cetak
 * @property \Carbon\Carbon|null $tanggal_cetak
 * @property \Carbon\Carbon|null $tanggal_cetak_rapor
 * @property string|null $watermark
 */
class PrintSetting extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'nama_yayasan',
        'tempat_cetak',
        'tanggal_cetak',
        'tanggal_cetak_rapor',
        'watermark',
    ];

    protected $casts = [
        'tanggal_cetak' => 'date',
        'tanggal_cetak_rapor' => 'date',
    ];
}
