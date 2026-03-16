<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basts', function (Blueprint $table) {
            $table->string('handover_location')->nullable()->after('received_by');
            $table->string('receiver_location')->nullable()->after('handover_location');
        });

        // copy data lama dari location ke lokasi baru kalau masih ada
        if (Schema::hasColumn('basts', 'location')) {
            DB::statement("UPDATE basts SET handover_location = location WHERE handover_location IS NULL");
            DB::statement("UPDATE basts SET receiver_location = location WHERE receiver_location IS NULL");
        }

        // bast_number harus nullable supaya draft bisa disimpan tanpa nomor final
        DB::statement("ALTER TABLE basts MODIFY bast_number VARCHAR(191) NULL");
    }

    public function down(): void
    {
        Schema::table('basts', function (Blueprint $table) {
            $table->dropColumn(['handover_location', 'receiver_location']);
        });

        // hati-hati: rollback ke not null hanya aman jika semua row punya bast_number
        DB::statement("UPDATE basts SET bast_number = CONCAT('DRAFT-', id) WHERE bast_number IS NULL");
        DB::statement("ALTER TABLE basts MODIFY bast_number VARCHAR(191) NOT NULL");
    }
};