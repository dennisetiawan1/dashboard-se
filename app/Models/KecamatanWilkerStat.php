<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KecamatanWilkerStat extends Model
{
    use HasFactory;

    protected $table = 'kecamatan_wilker_stats';

    // Buka izin pengisian data dari Laravel:
    protected $fillable = [
        'kecamatan',
        'bku_wilkerstat',
        'st_2023',
        'utp_pertanian'
    ];
}