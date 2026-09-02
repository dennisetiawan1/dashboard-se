<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('desa_references', function (Blueprint $table) {
            $table->id();

            // Buat fisik kolom di database:
            $table->string('id_wilayah')->nullable();
            $table->string('email_pengawas')->nullable();
            $table->string('email_pencacah')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('nama_desa')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desa_references');
    }
};
