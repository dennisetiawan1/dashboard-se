<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained('uploads')->cascadeOnDelete();
            $table->date('upload_date')->index();

            $table->string('kabupaten_code')->nullable();
            $table->string('kabupaten_name')->nullable();

            $table->string('petugas_user_id')->nullable();
            $table->string('petugas_username')->nullable()->index();
            $table->string('petugas_email')->nullable();
            $table->string('petugas_role')->nullable()->index();
            $table->unsignedInteger('petugas_total_assignment')->default(0);

            $table->string('sls_code')->nullable()->index();
            $table->unsignedInteger('sls_total_assignment')->default(0);

            $table->unsignedInteger('status_open')->default(0);
            $table->unsignedInteger('status_draft')->default(0);
            $table->unsignedInteger('status_submitted_pencacah')->default(0);
            $table->unsignedInteger('status_approved_pengawas')->default(0);
            $table->unsignedInteger('status_rejected_pengawas')->default(0);
            $table->unsignedInteger('status_edited_pengawas')->default(0);
            $table->unsignedInteger('status_revoked_pengawas')->default(0);
            $table->unsignedInteger('status_submitted_respondent')->default(0);
            $table->unsignedInteger('status_completed_admin_kab')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_snapshots');
    }
};
