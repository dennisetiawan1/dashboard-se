<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usaha extends Model
{
    protected $table = 'usaha';

    protected $fillable = [
        'upload_id',

        'id_wilayah',
        'kd_kab',
        'nama_sls',

        'jumlah_ub_prelist_awal',
        'jumlah_um_prelist_awal',
        'jumlah_umk_prelist_awal',

        'jumlah_usaha_ditemukan_bku',
        'jumlah_usaha_ditutup_bku',
        'jumlah_usaha_ganda_bku',
        'jumlah_usaha_tidak_ditemukan_bku',
        'jumlah_usaha_baru_bku',

        'jumlah_usaha_ditemukan_usaha_keluarga',
        'jumlah_usaha_tutup_usaha_keluarga',
        'jumlah_usaha_ganda_usaha_keluarga',
        'jumlah_usaha_tidak_ditemukan_usaha_keluarga',
        'jumlah_usaha_baru_usaha_keluarga',

        'jumlah_keluarga_ditemukan',
        'jumlah_keluarga_meninggal',
        'jumlah_keluarga_tidak_eligible',
        'jumlah_keluarga_tidak_ditemui',
        'jumlah_keluarga_tidak_ditemukan',
        'jumlah_keluarga_baru',

        'jumlah_prelist_usaha',
        'jumlah_usaha_realisasi',
        'jumlah_prelist_keluarga',
        'jumlah_keluarga_realisasi',

        'ppl',
        'pml',
        'last_update',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(UsahaUpload::class, 'upload_id');
    }
}