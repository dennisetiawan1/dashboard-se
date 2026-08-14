<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploads', function (Blueprint $table) {

            $table->dropUnique('uploads_upload_date_unique');

            $table->unique(['upload_date', 'petugas_role']);
        });
    }

    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {

            $table->dropUnique(['upload_date','petugas_role']);

            $table->unique('upload_date');
        });
    }
};