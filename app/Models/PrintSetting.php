<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintSetting extends Model
{
    use HasFactory;

    protected $fillable = [
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
