<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UTPPertanian extends Model
{
    use HasFactory;

    protected $table = 'utp_pertanian';

    // Buka izin pengisian data dari Laravel:
    protected $fillable = [
        'id_wilayah',
        'kecamatan',
        'desa',
        'sls',
        'kbli_3digit',
        'total_usaha',
    ];
}