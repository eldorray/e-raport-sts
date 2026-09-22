<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk profil sekolah.
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $nsm
 * @property string|null $npsn
 * @property string|null $email
 * @property string|null $address
 * @property string|null $district
 * @property string|null $city
 * @property string|null $province
 * @property string|null $headmaster
 * @property string|null $nip_headmaster
 * @property string|null $logo
 * @property string|null $logo_right
 */
class SchoolProfile extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolProfileFactory> */
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
        'logo_right',
    ];
}
