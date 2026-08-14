<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('petugas_role');
            $table->string('original_filename');
            $table->string('file_path');
            $table->unsignedInteger('total_rows');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_uploads');
    }
};