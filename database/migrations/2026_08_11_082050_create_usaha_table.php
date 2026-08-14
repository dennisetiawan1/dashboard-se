<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usaha', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_wilayah')->nullable();
            $table->string('kd_kab')->nullable();
            $table->string('nama_sls')->nullable();

            $table->integer('jumlah_ub_prelist_awal')->default(0);
            $table->integer('jumlah_um_prelist_awal')->default(0);
            $table->integer('jumlah_umk_prelist_awal')->default(0);

            $table->integer('jumlah_usaha_ditemukan_bku')->default(0);
            $table->integer('jumlah_usaha_ditutup_bku')->default(0);
            $table->integer('jumlah_usaha_ganda_bku')->default(0);
            $table->integer('jumlah_usaha_tidak_ditemukan_bku')->default(0);
            $table->integer('jumlah_usaha_baru_bku')->default(0);

            $table->integer('jumlah_usaha_ditemukan_usaha_keluarga')->default(0);
            $table->integer('jumlah_usaha_tutup_usaha_keluarga')->default(0);
            $table->integer('jumlah_usaha_ganda_usaha_keluarga')->default(0);
            $table->integer('jumlah_usaha_tidak_ditemukan_usaha_keluarga')->default(0);
            $table->integer('jumlah_usaha_baru_usaha_keluarga')->default(0);

            $table->integer('jumlah_keluarga_ditemukan')->default(0);
            $table->integer('jumlah_keluarga_meninggal')->default(0);
            $table->integer('jumlah_keluarga_tidak_eligible')->default(0);
            $table->integer('jumlah_keluarga_tidak_ditemui')->default(0);
            $table->integer('jumlah_keluarga_tidak_ditemukan')->default(0);
            $table->integer('jumlah_keluarga_baru')->default(0);

            $table->integer('jumlah_prelist_usaha')->default(0);
            $table->integer('jumlah_usaha_realisasi')->default(0);
            $table->integer('jumlah_prelist_keluarga')->default(0);
            $table->integer('jumlah_keluarga_realisasi')->default(0);

            $table->string('ppl')->nullable();
            $table->string('pml')->nullable();

            $table->string('last_update')->nullable();

            $table->timestamps();

            $table->index('kd_kab');
            $table->index('nama_sls');
            $table->index('ppl');
            $table->index('pml');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usaha');
    }
};