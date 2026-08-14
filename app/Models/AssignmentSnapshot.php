<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSnapshot extends Model
{
    protected $fillable = [
        'upload_id',
        'upload_date',
        'kabupaten_code',
        'kabupaten_name',
        'petugas_user_id',
        'petugas_username',
        'petugas_email',
        'petugas_role',
        'petugas_total_assignment',
        'sls_code',
        'sls_total_assignment',
        'status_open',
        'status_draft',
        'status_submitted_pencacah',
        'status_approved_pengawas',
        'status_rejected_pengawas',
        'status_edited_pengawas',
        'status_revoked_pengawas',
        'status_submitted_respondent',
        'status_completed_admin_kab',
        'status_edited_admin_kab',
        'status_rejected_admin_kab',
        'status_revoked_admin_kab',
    ];

    protected $casts = [
        'upload_date' => 'date',
        'petugas_total_assignment' => 'integer',
        'sls_total_assignment' => 'integer',
        'status_open' => 'integer',
        'status_draft' => 'integer',
        'status_submitted_pencacah' => 'integer',
        'status_approved_pengawas' => 'integer',
        'status_rejected_pengawas' => 'integer',
        'status_edited_pengawas' => 'integer',
        'status_revoked_pengawas' => 'integer',
        'status_submitted_respondent' => 'integer',
        'status_completed_admin_kab' => 'integer',
        'status_edited_admin_kab' => 'integer',
        'status_rejected_admin_kab' => 'integer',
        'status_revoked_admin_kab' => 'integer',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    /**
     * Daftar kolom status utama yang dipakai di kartu ringkasan & grafik.
     */
    public static function mainStatusColumns(): array
    {
        return [
            'status_open' => 'Open',
            'status_draft' => 'Draft',
            'status_submitted_pencacah' => 'Submitted by Pencacah',
            'status_approved_pengawas' => 'Approved by Pengawas',
        ];
    }
}
