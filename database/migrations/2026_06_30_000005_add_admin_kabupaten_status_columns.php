<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_snapshots', function (Blueprint $table) {
            $table->unsignedInteger('status_edited_admin_kab')->default(0)->after('status_completed_admin_kab');
            $table->unsignedInteger('status_rejected_admin_kab')->default(0)->after('status_edited_admin_kab');
        });
    }

    public function down(): void
    {
        Schema::table('assignment_snapshots', function (Blueprint $table) {
            $table->dropColumn(['status_edited_admin_kab', 'status_rejected_admin_kab']);
        });
    }
};