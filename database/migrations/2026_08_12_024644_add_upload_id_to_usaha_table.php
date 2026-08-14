<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usaha', function (Blueprint $table) {
            $table->foreignId('upload_id')
                ->nullable()
                ->after('id')
                ->constrained('usaha_uploads')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('usaha', function (Blueprint $table) {
            $table->dropForeign(['upload_id']);
            $table->dropColumn('upload_id');
        });
    }
};