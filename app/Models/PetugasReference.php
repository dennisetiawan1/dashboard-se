<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetugasReference extends Model
{
    protected $fillable = [
        'petugas_username',
        'nama_petugas',
        'kode_kecamatan',
        'nama_kecamatan',
        'petugas_role',
    ];
}