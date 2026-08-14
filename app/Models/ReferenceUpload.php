<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceUpload extends Model
{
    protected $fillable = [
        'petugas_role',
        'original_filename',
        'file_path',
        'total_rows',
    ];
}