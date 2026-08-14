<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel lama dibuat dengan asumsi kolom "petugas_email" sebagai key,
        // tapi file referensi yang sebenarnya memakai "petugas_username".
        // Drop & buat ulang dengan struktur yang benar (data referensi
        // bersifat master dan selalu di-replace total tiap upload, jadi aman didrop).
        Schema::dropIfExists('petugas_references');

        Schema::create('petugas_references', function (Blueprint $table) {
            $table->id();
            $table->string('petugas_username')->unique();
            $table->string('nama_petugas')->nullable();
            $table->string('kode_kecamatan')->nullable();
            $table->string('nama_kecamatan')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petugas_references');
    }
};
