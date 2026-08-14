<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->date('upload_date')->index(); // tanggal snapshot data (bukan tanggal upload file)
            $table->string('original_filename');
            $table->unsignedInteger('total_rows')->default(0);
            $table->timestamps();

            // satu tanggal hanya boleh punya satu snapshot data yang "aktif"
            $table->unique('upload_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
