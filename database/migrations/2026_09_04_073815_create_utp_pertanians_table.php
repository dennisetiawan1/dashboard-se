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
        Schema::create('kecamatan_wilker_stat', function (Blueprint $table) {
            $table->id();

            // Buat fisik kolom di database:
            $table->string('id_wilayah')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('desa')->nullable();
            $table->string('sls')->nullable();
            $table->integer('bku')->nullable();
            $table->integer('st_2023')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kecamatan_wilker_stats');
    }
};
