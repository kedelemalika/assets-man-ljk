<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basts', function (Blueprint $table) {
            $table->string('handover_position')->nullable()->after('handover_by');
            $table->string('received_position')->nullable()->after('received_by');
            $table->string('handover_city')->nullable()->after('handover_location');
            $table->string('receiver_city')->nullable()->after('receiver_location');
        });
    }

    public function down(): void
    {
        Schema::table('basts', function (Blueprint $table) {
            $table->dropColumn([
                'handover_position',
                'received_position',
                'handover_city',
                'receiver_city',
            ]);
        });
    }
};