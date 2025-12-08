<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajarans';

    protected $fillable = [
        'kode',
        'nama_mapel',
        'jumlah_jam',
        'kelompok',
        'jurusan',
        'urutan',
    ];
}
