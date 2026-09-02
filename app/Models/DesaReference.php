<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesaReference extends Model
{
    use HasFactory;

    protected $table = 'desa_references';

    // Buka izin pengisian data dari Laravel:
    protected $fillable = [
        'id_wilayah',
        'email_pengawas',
        'email_pencacah',
        'kecamatan',
        'nama_desa',
    ];
}