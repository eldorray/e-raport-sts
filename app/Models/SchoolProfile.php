<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nsm',
        'npsn',
        'email',
        'address',
        'district',
        'city',
        'province',
        'headmaster',
        'nip_headmaster',
        'logo',
    ];
}
