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
        Schema::create('kecamatan_wilker_stats', function (Blueprint $table) {
            $table->id();

            // Buat fisik kolom di database:
            $table->string('kecamatan')->nullable();
            $table->string('bku_wilkerstat')->nullable();
            $table->string('st_2023')->nullable();

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
