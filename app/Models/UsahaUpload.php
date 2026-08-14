<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsahaUpload extends Model
{
    protected $table = 'usaha_uploads';

    protected $fillable = [
        'upload_date',
        'original_filename',
        'file_path',
        'total_rows',
    ];

    protected $casts = [
        'upload_date' => 'date',
    ];

    public function usaha(): HasMany
    {
        return $this->hasMany(Usaha::class, 'upload_id');
    }
}